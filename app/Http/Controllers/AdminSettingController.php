<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class AdminSettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Validasi input
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->settings as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
            DB::commit();

            return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }
}
