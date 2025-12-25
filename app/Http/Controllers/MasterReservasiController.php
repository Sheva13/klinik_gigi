<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterPoli;
use App\Models\MasterDokter;
use App\Models\MasterJadwal;
use App\Services\ReservasiService;
use Exception;

class MasterReservasiController extends ReservasiController
{
    protected $reservasiService;

    public function __construct(ReservasiService $reservasiService)
    {
        $this->reservasiService = $reservasiService;
    }
    
    public function getDaftarPoli()
    {
        try {
            $poli = MasterPoli::select('kode_poli', 'nama_poli')->get();
            return $this->successResponse('Daftar poli berhasil diambil', $poli);
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Get Daftar Poli Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil data poli', null, 500);
        }
    }

    public function getDokterByPoli(Request $request)
    {
        $request->validate(['kode_poli' => 'nullable|string']);
        try {
            $query = MasterDokter::select('kode_dokter', 'nama', 'gelar', 'kode_poli');

            if (!empty($request->kode_poli) && strtolower($request->kode_poli) !== 'semua') {
                $query->where('kode_poli', $request->kode_poli);
            }

            $dokter = $query->get();
            return $this->successResponse('Daftar dokter berhasil diambil', $dokter);
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Get Dokter Error: '.$e->getMessage());
            return $this->errorResponse('Gagal mengambil data dokter', null, 500);
        }
    }

    public function getJadwalDenganKuota(Request $request)
    {
        try {
            $hasil = $this->reservasiService->getJadwalDenganKuota($request);
            return $this->successResponse('Data jadwal berhasil diambil', $hasil);
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Get Jadwal Error: ' . $e->getMessage());
            return $this->errorResponse('Gagal mengambil data jadwal', null, 500);
        }
    }
}