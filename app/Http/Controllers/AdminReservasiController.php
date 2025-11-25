<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Reservasi;
use App\Models\MasterJadwal;
use App\Models\MasterDokter;
use Carbon\Carbon;
use Exception;

class AdminReservasiController extends Controller
{
    protected function successResponse($message, $data = null, $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message, $errors = null, $code = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    /**
     * LIST RESERVASI ADMIN
     */
    public function index(Request $request)
    {
        try {
            $query = Reservasi::with(['dokter.masterPoli', 'jadwal', 'rekamMedis as pasien']);

            if ($q = $request->query('q')) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('no_pemeriksaan', 'like', "%{$q}%")
                        ->orWhereHas('rekamMedis', function ($q3) use ($q) {
                            $q3->where('nama_lengkap', 'like', "%{$q}%")
                                ->orWhere('rekam_medis', 'like', "%{$q}%");
                        });
                });
            }

            if ($dokter = $request->query('dokter')) {
                $query->where('dokter_id', $dokter);
            }

            if ($poli = $request->query('poli')) {
                $query->whereHas('dokter', fn($q) => $q->where('kode_poli', $poli));
            }

            if ($status = $request->query('status_reservasi')) {
                $query->where('status_reservasi', $status);
            }

            if ($statusBayar = $request->query('status_pembayaran')) {
                $query->where('status_pembayaran', $statusBayar);
            }

            if ($jenis = $request->query('jenis_pasien')) {
                $query->where('jenis_pasien', $jenis);
            }

            if ($from = $request->query('from')) {
                $query->whereDate('tanggal_pesan', '>=', $from);
            }

            if ($to = $request->query('to')) {
                $query->whereDate('tanggal_pesan', '<=', $to);
            }

            $perPage = (int) $request->query('per_page', 15);
            $sortBy = $request->query('sort_by', 'tanggal_pesan');
            $sortDir = $request->query('sort_dir', 'desc');

            $list = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

            return $this->successResponse('Daftar reservasi berhasil diambil', $list);
        } catch (Exception $e) {
            Log::error('Admin Reservasi Index Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil daftar reservasi', null, 500);
        }
    }

    /**
     * DETAIL RESERVASI
     */
    public function show($idOrNo)
    {
        try {
            $reservasi = Reservasi::with(['dokter.masterPoli', 'jadwal.poli', 'rekamMedis'])
                ->where('id', $idOrNo)
                ->orWhere('no_pemeriksaan', $idOrNo)
                ->first();

            if (!$reservasi) {
                return $this->errorResponse('Reservasi tidak ditemukan', null, 404);
            }

            return $this->successResponse('Detail reservasi', $reservasi);
        } catch (Exception $e) {
            Log::error('Admin Reservasi Show Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal membuka detail reservasi', null, 500);
        }
    }

    /**
     * Update status reservasi
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,cancel,complete,force_approve',
            'reason' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $reservasi = Reservasi::find($id);
            if (!$reservasi) {
                return $this->errorResponse('Reservasi tidak ditemukan', null, 404);
            }

            $action = $request->input('action');

            if ($action === 'approve') {
                $jadwal = MasterJadwal::find($reservasi->jadwal_id);

                $terpakai = Reservasi::where('jadwal_id', $jadwal->id)
                    ->where('tanggal_pesan', $reservasi->tanggal_pesan)
                    ->whereIn('status_reservasi', [
                        'waiting', 'approved', 'menunggu_kunjungan'
                    ])
                    ->count();

                if ($terpakai >= ($jadwal->quota ?? 0)) {
                    DB::rollBack();
                    return $this->errorResponse('Kuota penuh, gunakan force_approve jika tetap ingin melanjutkan', null, 409);
                }

                $reservasi->update([
                    'status_reservasi' => 'approved',
                    'status_pembayaran' => $reservasi->status_pembayaran,
                    'status' => 'Aktif'
                ]);
            }

            if ($action === 'force_approve') {
                $reservasi->update([
                    'status_reservasi' => 'approved',
                    'status' => 'Override Admin',
                ]);
            }

            if ($action === 'cancel') {
                $reservasi->update([
                    'status_reservasi' => 'cancelled',
                    'status_pembayaran' => 'dibatalkan',
                    'status' => 'Dibatalkan Admin'
                ]);
            }

            if ($action === 'complete') {
                $reservasi->update([
                    'status_reservasi' => 'completed',
                    'status' => 'Selesai'
                ]);
            }

            DB::commit();
            return $this->successResponse('Status reservasi diperbarui', $reservasi->fresh());

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Admin UpdateStatus Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memperbarui status reservasi', null, 500);
        }
    }
}
