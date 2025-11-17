<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Club;    
use App\Models\ClubDues;  
use Illuminate\Support\Facades\Storage; 

class PublicIuranController extends Controller
{
    /**
     * Menampilkan formulir publik untuk submit iuran.
     */
    public function create()
    {
        // 1. Ambil semua klub untuk ditampilkan di dropdown
        $clubs = Club::orderBy('nama_klub', 'asc')->get();

        // 2. Tampilkan view form publik
        return view('iuran.create', [
            'clubs' => $clubs
        ]);
    }

    /**
     * Menyimpan pengajuan iuran baru ke database (status 'Pending').
     */
    public function store(Request $request)
    {
        // 1. Validasi Input (WAJIB)
        $validatedData = $request->validate([
            'club_id' => 'required|integer|exists:clubs,id',
            'payment_year' => 'required|integer|digits:4|min:' . (now()->year - 1), 
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0',
            'payment_proof_url' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // "Nota"
            'persetujuan' => 'required|accepted', 
        ]);

        // 2. Upload File "Nota"
        $path_nota = $request->file('payment_proof_url')->store('iuran_bukti', 'public');

        // 3. Simpan ke Database
        try {
            ClubDues::create([
                'club_id' => $validatedData['club_id'],
                'payment_year' => $validatedData['payment_year'],
                'payment_date' => $validatedData['payment_date'],
                'amount_paid' => $validatedData['amount_paid'],
                'payment_proof_url' => $path_nota,
                'status' => 'Pending', 
            ]);

            // 4. Redirect
            return redirect()->route('login')->with('status', 'Bukti iuran Anda berhasil diunggah dan sedang menunggu persetujuan.');

        } catch (\Exception $e) {
            // 5. Jika gagal, hapus file
            Storage::disk('public')->delete($path_nota);
            
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan. Gagal menyimpan data: ' . $e->getMessage());
        }
    }
}