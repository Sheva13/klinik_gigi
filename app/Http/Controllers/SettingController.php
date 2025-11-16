<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    // Menampilkan username user yang sedang login
    public function getUsername()
    {
        // Ambil user yang login
        $user = Auth::user();

        // Cek apakah user ada (sudah login)
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User belum login.'
            ], 401);
        }

        // Return username (name)
        return response()->json([
            'success' => true,
            'username' => $user->nama_pengguna
        ]);
    }
}
