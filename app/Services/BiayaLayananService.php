<?php

namespace App\Services;

use App\Models\MasterBiayaLayanan;
use Exception;

class BiayaLayananService
{
    /**
     * Mendapatkan biaya reservasi berdasarkan tipe layanan dan jenis pasien
     *
     * @param string $tipeLayanan
     * @param string $jenisPasien
     * @return float|null
     */
    public function getBiayaReservasi(string $tipeLayanan, string $jenisPasien): ?float
    {
        $biayaLayanan = MasterBiayaLayanan::getBiayaByLayananAndPasien($tipeLayanan, $jenisPasien);

        if (!$biayaLayanan) {
            return null;
        }

        return $biayaLayanan->biaya_reservasi;
    }

    /**
     * Mendapatkan semua biaya layanan
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllBiayaLayanan()
    {
        return MasterBiayaLayanan::all();
    }

    /**
     * Mencari atau membuat biaya layanan
     *
     * @param string $tipeLayanan
     * @param string $jenisPasien
     * @param float $biaya
     * @return MasterBiayaLayanan
     */
    public function findOrCreateBiayaLayanan(string $tipeLayanan, string $jenisPasien, float $biaya): MasterBiayaLayanan
    {
        return MasterBiayaLayanan::updateOrCreate(
            [
                'tipe_layanan' => $tipeLayanan,
                'jenis_pasien' => $jenisPasien,
            ],
            [
                'biaya_reservasi' => $biaya,
            ]
        );
    }

    /**
     * Mendapatkan biaya berdasarkan tipe layanan dan jenis pasien
     *
     * @param string $tipeLayanan
     * @param string $jenisPasien
     * @return \App\Models\MasterBiayaLayanan|null
     */
    public function getBiayaByLayananAndPasien(string $tipeLayanan, string $jenisPasien)
    {
        return MasterBiayaLayanan::where('tipe_layanan', $tipeLayanan)
            ->where('jenis_pasien', $jenisPasien)
            ->first();
    }
}