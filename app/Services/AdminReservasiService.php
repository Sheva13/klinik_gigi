<?php

namespace App\Services;

use App\Models\Reservasi;
use App\Models\RekamMedis;
use App\Models\MasterJadwal;
use App\Models\DataPasien;
use App\Models\TransaksiBayar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class AdminReservasiService
{
    // Logika Simpan Reservasi Baru
    public function handleCreateManual($data)
    {
        // 1. Logic Pasien (Cari atau Buat Baru)
        $rekam_medis_id = $data['pasien_id_exist'] ?? null;
        $rmString = '';

        if (!$rekam_medis_id) {
            $last_rm = RekamMedis::latest('id')->first();
            $new_rm_num = $last_rm ? (int) substr($last_rm->rekam_medis, 2) + 1 : 1;
            $rekam_medis_no = 'RM' . str_pad($new_rm_num, 3, '0', STR_PAD_LEFT);

            $pasien = RekamMedis::create([
                'rekam_medis'   => $rekam_medis_no,
                'nama'          => $data['nama_lengkap'],
                'tgl_lahir'     => $data['ttl'] ?? null,
                'alamat'        => $data['alamat'] ?? null,
                'no_hp'         => $data['no_hp'] ?? null,
                'jenis_pasien'  => $data['jenis_pasien'],
            ]);
            $rmString = $pasien->rekam_medis;
        } else {
            $pasien = RekamMedis::find($rekam_medis_id);
            $rmString = $pasien ? $pasien->rekam_medis : '';
        }

        // 2. Logic Jadwal
        $tanggal_waktu_pesan = Carbon::parse($data['tanggal_janji'] . ' ' . $data['waktu_janji']);
        $day_of_week = $tanggal_waktu_pesan->dayOfWeekIso;
        
        $master = MasterJadwal::where('kode_dokter', $data['dokter'])
            ->where('hari', $day_of_week)
            ->where('jam_mulai', $data['waktu_janji'])
            ->first();
        
        $jadwal_id = $master ? $master->id : null;
        if (!$jadwal_id) throw new Exception("Jadwal Dokter atau jam praktek tidak ditemukan.");

        // 3. Simpan Reservasi
        $reservasi = Reservasi::create([
            'pasien_id'         => $rmString,
            'dokter_id'         => $data['dokter'],
            'jadwal_id'         => $jadwal_id,
            'tanggal_pesan'     => $data['tanggal_janji'],
            'waktu_pesan'       => $tanggal_waktu_pesan->format('H:i:s'),
            'jam_mulai'         => $data['waktu_janji'],
            'jam_selesai'       => Carbon::parse($data['waktu_janji'])->addMinutes(30)->format('H:i:s'),
            'keluhan'           => $data['keluhan'] ?? null,
            'status_pembayaran' => $data['status_bayar'] ?? 'menunggu_verifikasi',
            'metode_pembayaran' => $data['metode_bayar'] ?? 'Manual',
            'pembayaran_total'  => $data['total_biaya'] ?? 0,
            'status_reservasi'  => 'menunggu',
            'no_pemeriksaan'    => $this->generateNoPemeriksaan(),
            'no_antrian'        => null,
            'jenis_pasien'      => $data['jenis_pasien'],
        ]);

        // 4. Cek Auto Masuk Antrian (Jika Lunas)
        if (in_array($reservasi->status_pembayaran, ['lunas', 'terverifikasi'])) {
            $reservasi->status_pembayaran = 'terverifikasi';
            $reservasi->save();
            $this->processQueueLogic($reservasi, $rmString, $jadwal_id);
        }

        return $reservasi;
    }

    // Logika Update Reservasi
    public function handleUpdate($id, $data)
    {
        $reservasi = Reservasi::with('rekamMedis')->findOrFail($id);

        // Update basic fields
        if (isset($data['dokter_id'])) $reservasi->dokter_id = $data['dokter_id'];
        if (isset($data['tanggal_pesan'])) $reservasi->tanggal_pesan = $data['tanggal_pesan'];
        if (isset($data['keluhan'])) $reservasi->keluhan = $data['keluhan'];

        if (isset($data['jam_mulai'])) {
            $reservasi->jam_mulai = $data['jam_mulai'];
            $reservasi->jam_selesai = Carbon::parse($data['jam_mulai'])->addMinutes(30)->format('H:i:s');
        }

        if (isset($data['status_reservasi'])) {
            $reservasi->status_reservasi = $data['status_reservasi'];
        }

        // Update keuangan
        if (isset($data['metode_pembayaran'])) {
            $reservasi->metode_pembayaran = $data['metode_pembayaran'];
        }

        // Logic Pembayaran & Antrian
        if (isset($data['status_pembayaran']) && $reservasi->status_pembayaran != $data['status_pembayaran']) {
            $reservasi->status_pembayaran = $data['status_pembayaran'];

            if (in_array($data['status_pembayaran'], ['terverifikasi', 'lunas'])) {
                if($reservasi->status_reservasi == 'menunggu_pembayaran') {
                     $reservasi->status_reservasi = 'menunggu';
                }

                $rmString = $reservasi->rekamMedis ? $reservasi->rekamMedis->rekam_medis : $reservasi->pasien_id;
                $this->processQueueLogic($reservasi, $rmString, $reservasi->jadwal_id);
            }
        }

        $reservasi->save();
        return $reservasi;
    }

    // Logika Masuk Antrian (Logic kompleks dipisah disini)
    public function processQueueLogic($reservasi, $rmString, $jadwalId)
    {
        // Try to obtain a per-patient named lock to serialize inserts for the same rekam_medis.
        $lockName = 'data_pasien_rm_' . $rmString;
        $lockTimeout = 10; // seconds
        $gotLock = false;

        try {
            $res = DB::select('SELECT GET_LOCK(?, ?) AS got', [$lockName, $lockTimeout]);
            if (is_array($res) && count($res) > 0) {
                $row = (array) $res[0];
                $val = array_values($row)[0];
                $gotLock = ((int) $val === 1);
            }

            if (!$gotLock) {
                Log::warning("Could not obtain DB named lock {$lockName} within {$lockTimeout}s. Proceeding without it (risk of concurrency).");
            }

            // Wrap critical queuing section in a transaction + select FOR UPDATE on reservation to avoid
            // race conditions from concurrent webhooks trying to insert the same DataPasien row.
            DB::transaction(function () use ($reservasi, $rmString, $jadwalId, &$idPeriksa, $gotLock, $lockName) {
                // Obtain a FOR UPDATE lock on the reservasi row so concurrent executions
                // for the same reservation are serialized by the DB.
                $lockedReservasi = Reservasi::where('id', $reservasi->id)->lockForUpdate()->first();

                // 1. GENERATE NO ANTRIAN
                if (!$lockedReservasi->no_antrian || $lockedReservasi->no_antrian == '-') {
                    $maxAntrian = DataPasien::where('id_jadwal', $jadwalId)->whereDate('created_at', Carbon::today())->max('no_antri');
                    $urutanBaru = $maxAntrian ? ($maxAntrian + 1) : 1;

                    $prefix = match($lockedReservasi->jenis_pasien) { 'BPJS' => 'B', 'Asuransi' => 'A', default => 'U' };
                    $lockedReservasi->no_antrian = $prefix . '-' . str_pad($urutanBaru, 3, '0', STR_PAD_LEFT);
                    $lockedReservasi->save();
                } else {
                    $parts = explode('-', $lockedReservasi->no_antrian);
                    $urutanBaru = (count($parts) > 1) ? (int) end($parts) : 1;
                }

                // 2. INSERT DATA PASIEN (Antrian Hari Ini)
                $cekAntrian = DataPasien::where('rekam_medis', $rmString)->where('id_jadwal', $jadwalId)->whereDate('created_at', Carbon::today())->first();
                $idPeriksa = null;

                if (!$cekAntrian) {
                    // Use insertOrIgnore to avoid duplicate-key exceptions during concurrent inserts.
                    // If insert is ignored, re-query by `rekam_medis` (unique) to find the existing row.
                    $insertData = [
                        'id_jadwal' => $jadwalId,
                        'rekam_medis' => $rmString,
                        'no_antri' => $urutanBaru,
                        'status' => 1,
                        'pasien_baru' => 0,
                        'rujukan' => 0,
                        'biaya_admin' => 0,
                        'keluhan' => $lockedReservasi->keluhan,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    try {
                        $inserted = DB::table('data_pasien')->insertOrIgnore($insertData);
                        Log::info("DataPasien insertOrIgnore result for {$rmString} jadwal {$jadwalId}: " . var_export($inserted, true));

                        if ($inserted) {
                            // Insert succeeded; fetch the inserted row
                            $dp = DataPasien::where('rekam_medis', $rmString)
                                ->where('id_jadwal', $jadwalId)
                                ->whereDate('created_at', Carbon::today())
                                ->first();

                            if (!$dp) {
                                // In rare cases unique constraint may redirect row to another jadwal,
                                // so fallback to query by rekam_medis only
                                $dp = DataPasien::where('rekam_medis', $rmString)->whereDate('created_at', Carbon::today())->first();
                            }

                            if ($dp) {
                                $idPeriksa = $dp->id;
                            }
                        } else {
                            // Insert ignored (another process likely created it). Retry re-query a few times
                            $dp = null;
                            $attempts = 0;
                            while ($attempts < 5 && !$dp) {
                                $dp = DataPasien::where('rekam_medis', $rmString)->whereDate('created_at', Carbon::today())->first();
                                if ($dp) break;
                                usleep(100000); // wait 100 ms
                                $attempts++;
                            }

                            if ($dp) {
                                $idPeriksa = $dp->id;
                                Log::warning("Insert ignored for {$rmString}; found existing DataPasien id {$idPeriksa}");
                            } else {
                                // As a last resort, attempt a direct insert (this may still fail)
                                try {
                                    DB::table('data_pasien')->insert($insertData);
                                    $dp = DataPasien::where('rekam_medis', $rmString)->whereDate('created_at', Carbon::today())->first();
                                    $idPeriksa = $dp->id ?? null;
                                } catch (\Illuminate\Database\QueryException $e) {
                                    // If duplicate still occurs, query by rekam_medis across all dates
                                    if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                                        Log::warning("Concurrent DataPasien insert detected for {$rmString} / jadwal {$jadwalId}, re-querying by rekam_medis across all dates.");
                                        $dp = DataPasien::where('rekam_medis', $rmString)->first();
                                        if ($dp) {
                                            $idPeriksa = $dp->id;
                                        } else {
                                            // Log full details to aid debugging and rethrow
                                            Log::error("Duplicate entry but row not found for rekam_medis={$rmString}, jadwal={$jadwalId}");
                                            Log::error('SQL Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
                                            throw $e; // Unexpected - rethrow
                                        }
                                    } else {
                                        throw $e;
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // Bubble up the exception after logging so the caller can handle it
                        Log::error('❌ DataPasien insert failed: ' . $e->getMessage());
                        Log::error('Full Trace: ' . $e->getTraceAsString());
                        throw $e;
                    }
                } else {
                    $idPeriksa = $cekAntrian->id;
                }

                // 3. INSERT TRANSAKSI BAYAR
                $cekTrx = TransaksiBayar::where('id_periksa', $idPeriksa)->first();
                if (!$cekTrx && $idPeriksa) {
                    TransaksiBayar::create([
                        'id_periksa' => $idPeriksa,
                        'ambil_obat' => 0, 'total_tindakan' => 0, 'total_obat' => 0, 'total_penunjang' => 0,
                        'total_tambahan' => 0, 'total_bayar' => $lockedReservasi->pembayaran_total,
                        'waktu' => Carbon::now(), 'diskon' => 0, 'biaya_admin' => 0, 'pasien_baru' => 0,
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('❌ processQueueLogic failed: ' . $e->getMessage());
            Log::error('❌ processQueueLogic trace: ' . $e->getTraceAsString());
            throw $e;
        } finally {
            // Release named lock if acquired
            try {
                if (!empty($lockName)) {
                    DB::select('SELECT RELEASE_LOCK(?)', [$lockName]);
                    Log::info("Released DB named lock {$lockName}");
                }
            } catch (\Exception $releaseEx) {
                Log::warning('Failed to release DB named lock: ' . $releaseEx->getMessage());
            }
        }

        // 3. INSERT TRANSAKSI BAYAR
        $cekTrx = TransaksiBayar::where('id_periksa', $idPeriksa)->first();
        if (!$cekTrx && $idPeriksa) {
            TransaksiBayar::create([
                'id_periksa' => $idPeriksa,
                'ambil_obat' => 0, 'total_tindakan' => 0, 'total_obat' => 0, 'total_penunjang' => 0,
                'total_tambahan' => 0, 'total_bayar' => $reservasi->pembayaran_total,
                'waktu' => Carbon::now(), 'diskon' => 0, 'biaya_admin' => 0, 'pasien_baru' => 0,
            ]);
        }
    }

    private function generateNoPemeriksaan()
    {
        $tanggal = Carbon::now()->format('Ymd');
        $prefix = "RSV-{$tanggal}";
        $lastReservasi = Reservasi::where('no_pemeriksaan', 'LIKE', $prefix . '%')
            ->whereDate('created_at', Carbon::today())
            ->orderBy('no_pemeriksaan', 'desc')->first();

        $urutan = $lastReservasi ? ((int) substr($lastReservasi->no_pemeriksaan, -3) + 1) : 1;
        return $prefix . str_pad($urutan, 3, '0', STR_PAD_LEFT);
    }
}