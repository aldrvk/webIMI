<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Club;

class PembalapRegistrationController extends Controller
{
    /**
     * Menampilkan form registrasi pembalap baru
     */
    public function create()
    {
        $clubs = Club::orderBy('nama_klub')->get();
        
        return view('admin.pembalap.create', [
            'clubs' => $clubs
        ]);
    }
    
    /**
     * Menyimpan pembalap baru menggunakan Stored Procedure
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'club_id' => 'required|exists:clubs,id',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'no_ktp_sim' => 'nullable|string|max:255|unique:pembalap_profiles,no_ktp_sim',
            'golongan_darah' => 'nullable|in:A,B,AB,O,-',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string'
        ]);

        try {
            // Hash password
            $hashedPassword = Hash::make($validated['password']);
            
            // Panggil Stored Procedure 'Proc_RegisterPembalap'
            $result = DB::select(
                'CALL Proc_RegisterPembalap(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    null, // user_id (null untuk insert baru)
                    $validated['name'],
                    $validated['email'],
                    $hashedPassword,
                    $validated['club_id'],
                    $validated['tempat_lahir'] ?? null,
                    $validated['tanggal_lahir'] ?? null,
                    $validated['no_ktp_sim'] ?? null,
                    $validated['golongan_darah'] ?? null,
                    $validated['phone_number'] ?? null,
                    $validated['address'] ?? null
                ]
            );

            return redirect()
                ->route('admin.pembalap.index')
                ->with('status', 'Pembalap berhasil didaftarkan!');

        } catch (\Exception $e) {
            \Log::error('Gagal mendaftarkan pembalap (Proc_RegisterPembalap): ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal mendaftarkan pembalap. Silakan coba lagi.');
        }
    }
    
    /**
     * Update data pembalap menggunakan Stored Procedure
     */
    public function update(Request $request, $userId)
    {
        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId,
            'club_id' => 'required|exists:clubs,id',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'no_ktp_sim' => 'nullable|string|max:255|unique:pembalap_profiles,no_ktp_sim,' . $userId . ',user_id',
            'golongan_darah' => 'nullable|in:A,B,AB,O,-',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string'
        ]);

        try {
            // Panggil Stored Procedure 'Proc_RegisterPembalap' untuk update
            DB::select(
                'CALL Proc_RegisterPembalap(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $userId, // user_id (untuk update)
                    $validated['name'],
                    $validated['email'],
                    '', // password kosong jika tidak diubah
                    $validated['club_id'],
                    $validated['tempat_lahir'] ?? null,
                    $validated['tanggal_lahir'] ?? null,
                    $validated['no_ktp_sim'] ?? null,
                    $validated['golongan_darah'] ?? null,
                    $validated['phone_number'] ?? null,
                    $validated['address'] ?? null
                ]
            );

            return redirect()
                ->route('admin.pembalap.index')
                ->with('status', 'Data pembalap berhasil diperbarui!');

        } catch (\Exception $e) {
            \Log::error('Gagal memperbarui pembalap (Proc_RegisterPembalap): ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data pembalap. Silakan coba lagi.');
        }
    }
}
