<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

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

    // Get specific setting by key
    public function getSetting(Request $request)
    {
        $key = $request->input('key');

        if (!$key) {
            return response()->json([
                'success' => false,
                'message' => 'Key is required.'
            ], 400);
        }

        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'key' => $setting->key,
            'value' => $setting->value,
            'description' => $setting->description
        ]);
    }

    // Get multiple settings by keys
    public function getSettings(Request $request)
    {
        $keys = $request->input('keys', []);

        if (empty($keys)) {
            return response()->json([
                'success' => false,
                'message' => 'Keys array is required.'
            ], 400);
        }

        $settings = Setting::whereIn('key', $keys)->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return response()->json([
            'success' => true,
            'settings' => $result
        ]);
    }
}
