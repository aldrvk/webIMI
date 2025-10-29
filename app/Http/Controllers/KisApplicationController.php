<?php

namespace App\Http\Controllers;

use App\Models\KisApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KisApplicationController extends Controller
{
    /**
     * Menampilkan formulir pengajuan KIS.
     * Terhubung ke Rute GET /kis/apply
     */
    public function create()
    {
        // Cek apakah pembalap sudah punya pengajuan KIS yang 'Pending' atau 'Approved'
        // Ini mencegah pengajuan ganda jika tidak diperlukan
        $existingApplication = KisApplication::where('pembalap_user_id', Auth::id())
                                ->whereIn('status', ['Pending', 'Approved']) // Cek status Pending atau Approved
                                ->first(); // Ambil yang pertama ditemukan

        // Jika SUDAH ADA pengajuan aktif, redirect ke dashboard dengan pesan
        if ($existingApplication) {
            return redirect()->route('dashboard')->with('info', 'Anda sudah memiliki pengajuan KIS yang sedang diproses atau sudah disetujui.');
        }

        // Jika belum ada, tampilkan formulir
        return view('kis.apply');
    }

    /**
     * Menyimpan pengajuan KIS baru.
     * Terhubung ke Rute POST /kis/apply
     */
    public function store(Request $request)
    {
         // Cek lagi (keamanan sisi server) apakah sudah ada pengajuan aktif
        $existingApplication = KisApplication::where('pembalap_user_id', Auth::id())
                                ->whereIn('status', ['Pending', 'Approved'])
                                ->first();

        if ($existingApplication) {
            return redirect()->route('dashboard')->with('error', 'Anda sudah memiliki pengajuan KIS yang aktif.');
        }

        // 1. Validasi Input (Pastikan file ada, jenisnya benar, ukurannya pas)
        $validatedData = $request->validate([
            'file_surat_sehat' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // Maks 2MB
            'file_bukti_bayar' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // Maks 2MB
        ]);

        // 2. Upload Files
        // 'public' disk akan menyimpan file di storage/app/public/kis_documents/...
        // Pastikan Anda sudah menjalankan `php artisan storage:link`
        $path_surat_sehat = $request->file('file_surat_sehat')->store('kis_documents/surat_sehat', 'public');
        $path_bukti_bayar = $request->file('file_bukti_bayar')->store('kis_documents/bukti_bayar', 'public');

        // 3. Simpan ke Database menggunakan Eloquent Model
        KisApplication::create([
            'pembalap_user_id' => Auth::id(), // Dapatkan ID pembalap yang sedang login
            'status' => 'Pending', // Status awal
            'file_surat_sehat_url' => $path_surat_sehat,
            'file_bukti_bayar_url' => $path_bukti_bayar,
            // Kolom lain (processed_by_user_id, etc.) akan diisi nanti oleh Pengurus
        ]);

        // 4. Redirect kembali ke Dashboard dengan pesan sukses
        return redirect()->route('dashboard')->with('status', 'Pengajuan KIS Anda berhasil dikirim dan sedang menunggu persetujuan.');
    }
}
