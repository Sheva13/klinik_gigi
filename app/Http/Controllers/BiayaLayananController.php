<?php

namespace App\Http\Controllers;

use App\Models\MasterBiayaLayanan;
use App\Services\BiayaLayananService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BiayaLayananController extends Controller
{
    protected $biayaLayananService;

    public function __construct(BiayaLayananService $biayaLayananService)
    {
        $this->biayaLayananService = $biayaLayananService;
    }

    /**
     * Menampilkan semua biaya layanan
     */
    public function index(): JsonResponse
    {
        try {
            $biayaLayanan = $this->biayaLayananService->getAllBiayaLayanan();
            return response()->json([
                'success' => true,
                'data' => $biayaLayanan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data biaya layanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan biaya berdasarkan tipe layanan dan jenis pasien
     */
    public function getBiayaByLayananAndPasien(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'tipe_layanan' => 'required|string',
                'jenis_pasien' => 'required|string',
            ]);

            $biaya = $this->biayaLayananService->getBiayaReservasi(
                $request->tipe_layanan,
                $request->jenis_pasien
            );

            if ($biaya === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biaya layanan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'biaya_reservasi' => $biaya,
                    'tipe_layanan' => $request->tipe_layanan,
                    'jenis_pasien' => $request->jenis_pasien
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil biaya layanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan biaya layanan baru
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'tipe_layanan' => 'required|string|max:50',
                'jenis_pasien' => 'required|string|max:50',
                'biaya_reservasi' => 'required|numeric|min:0',
            ]);

            $biayaLayanan = $this->biayaLayananService->findOrCreateBiayaLayanan(
                $request->tipe_layanan,
                $request->jenis_pasien,
                $request->biaya_reservasi
            );

            return response()->json([
                'success' => true,
                'message' => 'Biaya layanan berhasil disimpan',
                'data' => $biayaLayanan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan biaya layanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}