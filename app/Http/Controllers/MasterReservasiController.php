<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterPoli;
use App\Models\MasterDokter;
use App\Models\MasterJadwal;
use App\Services\ReservasiService;
use Exception;
use Illuminate\Support\Facades\Log;

class MasterReservasiController extends Controller
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
            return response()->json(['success' => true, 'message' => 'Daftar poli berhasil diambil', 'data' => $poli]);
        } catch (Exception $e) {
            Log::error('Get Daftar Poli Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data poli'], 500);
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
            return response()->json(['success' => true, 'message' => 'Daftar dokter berhasil diambil', 'data' => $dokter]);
        } catch (Exception $e) {
            Log::error('Get Dokter Error: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data dokter'], 500);
        }
    }

    public function getJadwalDenganKuota(Request $request)
    {
        try {
            $hasil = $this->reservasiService->getJadwalDenganKuota($request);
            return response()->json(['success' => true, 'message' => 'Data jadwal berhasil diambil', 'data' => $hasil]);
        } catch (Exception $e) {
            Log::error('Get Jadwal Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data jadwal'], 500);
        }
    }

    // Mendapatkan daftar tanggal yang memiliki jadwal dokter
    public function getTanggalDenganJadwal(Request $request)
    {
        // Validasi input jika diperlukan
        $request->validate([
            'kode_poli' => 'nullable|string',
            'kode_dokter' => 'nullable|string',
        ]);

        try {
            $hasil = $this->reservasiService->getTanggalDenganJadwal($request);
            return response()->json(['success' => true, 'message' => 'Daftar tanggal dengan jadwal berhasil diambil', 'data' => $hasil]);
        } catch (Exception $e) {
            Log::error('Get Tanggal Jadwal Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data tanggal jadwal'], 500);
        }
    }

    // Mendapatkan daftar dokter yang tersedia pada tanggal tertentu
    public function getDokterDenganJadwal(Request $request)
    {
        try {
            $hasil = $this->reservasiService->getDokterDenganJadwal($request);
            return response()->json(['success' => true, 'message' => 'Daftar dokter dengan jadwal berhasil diambil', 'data' => $hasil]);
        } catch (Exception $e) {
            Log::error('Get Dokter Dengan Jadwal Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data dokter dengan jadwal'], 500);
        }
    }
}