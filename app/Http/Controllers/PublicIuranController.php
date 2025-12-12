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
        $clubs = Club::orderBy('nama_klub')->get();

        // 2. Tampilkan view form publik
        return view('iuran.create', compact('clubs'));
    }

    /**
     * Menyimpan pengajuan iuran baru ke database (status 'Pending').
     */
    public function store(Request $request)
    {
        // 1. Validasi Input (WAJIB)
        $request->validate([
            'club_id' => 'required|exists:clubs,id',
            'payment_year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0',
            'payment_proof_url' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'persetujuan' => 'required|accepted',
        ], [
            'persetujuan.accepted' => 'Anda harus menyetujui pernyataan di atas.',
        ]);

        // 2. Upload File "Nota"
        $path = $request->file('payment_proof_url')->store('iuran-proofs', 'public');

        // 3. Simpan ke Database
        ClubDues::create([
            'club_id' => $request->club_id,
            'payment_year' => $request->payment_year,
            'payment_date' => $request->payment_date,
            'amount_paid' => $request->amount_paid,
            'payment_proof_url' => $path,
            'status' => 'Pending',
        ]);

        // 4. Redirect
        return redirect()->route('iuran.create')->with('status', 'Bukti pembayaran berhasil dikirim! Menunggu verifikasi admin.');
    }
}