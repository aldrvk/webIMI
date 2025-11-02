<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClubDuesController extends Controller
{
    /**
     * Menampilkan formulir untuk mencatat pembayaran iuran baru.
     * Terhubung ke Rute GET /admin/clubs/{club}/dues/create
     */
    public function create(Club $club)
    {
        // Kirim data klub ke view
        return view('admin.clubs.dues.create', [
            'club' => $club
        ]);
    }

   /**
     * Menyimpan catatan iuran baru ke database (via Stored Procedure).
     * (DIPERBARUI: Penanganan Error Profesional)
     */
    public function store(Request $request, Club $club)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            'payment_year' => 'required|integer|digits:4|min:' . (now()->year - 1),
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0',
            'payment_proof_url' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', 
            'notes' => 'nullable|string',
        ]);

        // 2. Upload File "Nota" (jika ada)
        $path_nota = null;
        if ($request->hasFile('payment_proof_url')) {
            $path_nota = $request->file('payment_proof_url')->store('iuran_bukti', 'public');
        }

        try {
            // 3. Panggil Stored Procedure 'Proc_Admin_RecordDues'
            DB::statement(
                'CALL Proc_Admin_RecordDues(?, ?, ?, ?, ?, ?, ?)',
                [
                    $club->id,
                    $validatedData['payment_year'],
                    $validatedData['payment_date'],
                    $validatedData['amount_paid'],
                    $path_nota,
                    $validatedData['notes'],
                    Auth::id() // ID Pengurus IMI yang login
                ]
            );

            // 4. Jika 'CALL' berhasil, kembali ke halaman detail klub
            return redirect()->route('admin.clubs.show', $club->id)->with('status', 'Pembayaran iuran berhasil dicatat.');

        } catch (\Exception $e) {
            
            // 5a. Hapus file yang sudah ter-upload (jika ada)
            if ($path_nota) {
                Storage::disk('public')->delete($path_nota);
            }
            
            // 5b. Catat error detail untuk developer (di file log)
            \Log::error('Gagal mencatat iuran (Proc_Admin_RecordDues): ' . $e->getMessage());

            // 5c. Tampilkan pesan ramah ke pengguna (TIDAK ADA KODE SQL)
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan pada server. Gagal menyimpan data. Silakan coba lagi nanti.');
        }
    }
}