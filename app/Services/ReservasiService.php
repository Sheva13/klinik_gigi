<?php

namespace App\Services;

use App\Models\Reservasi;
use App\Models\MasterBiayaLayanan;
use App\Models\HomeCareReservasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReservasiService
{
    protected $biayaLayananService;

    public function __construct(\App\Services\BiayaLayananService $biayaLayananService)
    {
        $this->biayaLayananService = $biayaLayananService;
    }

    /**
     * Membuat reservasi baru dengan biaya dari tabel master
     *
     * @param array $data
     * @return Reservasi
     */
    public function createReservasi(array $data): Reservasi
    {
        DB::beginTransaction();

        try {
            // Ambil biaya dari tabel master berdasarkan tipe layanan dan jenis pasien
            $biayaReservasi = $this->biayaLayananService->getBiayaReservasi(
                $data['tipe_layanan'] ?? 'klinik',
                $data['jenis_pasien'] ?? 'Umum'
            );

            if ($biayaReservasi === null) {
                throw new Exception('Biaya layanan tidak ditemukan untuk kombinasi tipe layanan dan jenis pasien yang dipilih');
            }

            // Tambahkan biaya reservasi ke data
            $data['biaya_reservasi'] = $biayaReservasi;

            // Buat reservasi baru
            $reservasi = Reservasi::create($data);

            DB::commit();

            return $reservasi;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Mendapatkan biaya reservasi berdasarkan tipe layanan dan jenis pasien
     * untuk kebutuhan frontend sebelum membuat reservasi
     *
     * @param string $tipeLayanan
     * @param string $jenisPasien
     * @return float|null
     */
    public function getBiayaReservasiForPreview(string $tipeLayanan, string $jenisPasien): ?float
    {
        return $this->biayaLayananService->getBiayaReservasi($tipeLayanan, $jenisPasien);
    }

    /**
     * Generate nomor pemeriksaan unik
     */
    public function generateNoPemeriksaan()
    {
        $prefix = 'REG-';
        $timestamp = time();
        $random = rand(1000, 9999);
        return $prefix . $timestamp . '-' . $random;
    }

    /**
     * Simpan data reservasi ke database
     */
    public function simpanReservasi(array $data)
    {
        return Reservasi::create($data);
    }

    /**
     * Cek ketersediaan jadwal
     */
    public function cekKetersediaanJadwal($jadwalId, $tanggal)
    {
        // Validasi tambahan jika diperlukan
        // Misalnya: cek apakah jadwal tersedia di tanggal tersebut
        // atau cek kuota jadwal harian
    }
}