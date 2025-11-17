<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClubDues;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IuranApprovalController extends Controller
{
    /**
     * Menampilkan daftar pengajuan iuran yang statusnya 'Pending'.
     */
    public function index()
    {
        // 1. Ambil data iuran yang 'Pending'
        $pendingDues = ClubDues::where('status', 'Pending')
                               ->with('club') // Muat relasi klub
                               ->orderBy('created_at', 'asc') // Tampilkan yang terlama dulu
                               ->paginate(15); 

        // 2. Kirim data ke view (yang akan kita buat selanjutnya)
        return view('admin.iuran.index', [
            'dues' => $pendingDues
        ]);
    }

    /**
     * Menampilkan detail satu pengajuan iuran (untuk melihat 'nota').
     */
    public function show(ClubDues $clubDues) // Menggunakan Route Model Binding
    {
        // 1. Muat relasi klub untuk menampilkan nama
        $clubDues->load('club'); 

        // 2. Kirim data ke view detail (yang akan kita buat selanjutnya)
        return view('admin.iuran.show', [
            'payment' => $clubDues
        ]);
    }

    /**
     * Menyetujui (Approve) pengajuan iuran.
     */
    public function approve(ClubDues $clubDues)
    {
        // 1. Pastikan hanya 'Pending' yang bisa di-approve
        if ($clubDues->status !== 'Pending') {
            return redirect()->route('admin.iuran.show', $clubDues->id)->with('error', 'Pembayaran ini tidak lagi dalam status Pending.');
        }

        // 2. Update status dan catat siapa yang memproses
        $clubDues->update([
            'status' => 'Approved',
            'processed_by_user_id' => Auth::id(), // Catat Pengurus IMI yang login
            'rejection_reason' => null,
        ]);

        // 3. Redirect kembali ke daftar
        return redirect()->route('admin.iuran.index')->with('status', 'Pembayaran iuran klub berhasil disetujui.');
    }

    /**
     * Menolak (Reject) pengajuan iuran.
     */
    public function reject(Request $request, ClubDues $clubDues)
    {
        // 1. Pastikan hanya 'Pending' yang bisa di-reject
        if ($clubDues->status !== 'Pending') {
            return redirect()->route('admin.iuran.show', $clubDues->id)->with('error', 'Pembayaran ini tidak lagi dalam status Pending.');
        }

        // 2. Validasi alasan (wajib diisi)
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        // 3. Update status dan catat alasan
        $clubDues->update([
            'status' => 'Rejected',
            'processed_by_user_id' => Auth::id(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // 4. Redirect kembali ke daftar
        return redirect()->route('admin.iuran.index')->with('status', 'Pembayaran iuran klub telah ditolak.');
    }
}