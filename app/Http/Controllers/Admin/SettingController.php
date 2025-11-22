<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Menampilkan halaman form pengaturan.
     */
    public function index()
    {
        // Ambil semua setting dan ubah jadi key-value array agar mudah diakses di view
        $settings = Setting::all()->pluck('value', 'key');

        return view('admin.settings.index', [
            'settings' => $settings
        ]);
    }

    /**
     * Menyimpan perubahan pengaturan.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'kis_registration_fee' => 'required|numeric|min:0',
            'kis_bank_account' => 'required|string|max:1000',
        ]);

        // Simpan setiap setting
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('status', 'Pengaturan sistem berhasil diperbarui.');
    }
}