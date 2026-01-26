<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RekamMedis;
use Exception;

class UserReservasiController extends ReservasiController
{
    // 1. GET USER DATA
    public function getUserData(Request $request)
    {
        $user = Auth::user();
        if (!$user) return $this->errorResponse('User belum login', null, 401);

        $rekamMedis = $user->rekamMedis;
        if (!$rekamMedis) return $this->errorResponse('Data rekam medis tidak ditemukan', null, 404);

        return $this->successResponse('Data user berhasil diambil', [
            'nama_lengkap'    => $rekamMedis->nama ?? $user->nama_pengguna,
            'no_rekam_medis'  => $rekamMedis->rekam_medis ?? '-',
            'user_id'         => $user->user_id,
            'email'           => $user->email,
            'phone'           => $user->phone ?? $rekamMedis->telepon ?? null,
        ]);
    }

    // 6. RIWAYAT RESERVASI
    public function riwayatReservasi($rekam_medis_id) {
        try {
            $data = \App\Models\Reservasi::where('pasien_id', $rekam_medis_id)
                ->with(['dokter', 'jadwal.poli'])
                ->orderBy('tanggal_pesan', 'desc')
                ->orderBy('waktu_pesan', 'desc')
                ->get();
            return $this->successResponse('Riwayat ditemukan', $data);
        } catch (Exception $e) {
            return $this->errorResponse('Gagal memuat riwayat', null, 500);
        }
    }
}