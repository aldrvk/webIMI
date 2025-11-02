<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\KisApplication;
use App\Models\KisCategory;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Storage;

class KisApplicationController extends Controller
{
   /**
     * Menampilkan formulir pengajuan KIS (yang sekarang juga formulir profil).
     */
    public function create()
    {
        $user = Auth::user();

        // Cek pengajuan yang aktif/pending
        $hasPendingOrActive = $user->kisApplications()
                                  ->whereIn('status', ['Pending', 'Approved'])
                                  ->exists();

        if ($hasPendingOrActive) {
            return redirect()->route('dashboard')->with('info', 'Anda sudah memiliki pengajuan KIS yang sedang diproses atau sudah disetujui.');
        }

        // 1. Ambil daftar klub untuk dropdown
        $clubs = Club::orderBy('nama_klub', 'asc')->get();
        
        // 2. AMBIL DAFTAR KATEGORI KIS (BARU)
        $categories = KisCategory::orderBy('tipe', 'asc')->orderBy('kode_kategori', 'asc')->get();

        // 3. Tampilkan view dan kirim KEDUA data
        return view('kis.apply', [
            'clubs' => $clubs,
            'categories' => $categories
        ]);
    }

    /**
     * Menyimpan pengajuan KIS baru DAN membuat profil pembalap.

     */
    /**
     * Menyimpan pengajuan KIS baru DAN membuat profil pembalap.
     * (DIPERBARUI: Menambahkan 'kis_category_id')
     */
    public function store(Request $request)
    {
        // 1. Validasi Input (LENGKAP)
        $validatedData = $request->validate([
            // Validasi Data Profil
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'no_ktp_sim' => ['required', 'string', 'max:30'],
            'golongan_darah' => ['required', Rule::in(['A', 'B', 'AB', 'O', '-'])],
            'phone_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            
            // --- VALIDASI BARU ---
            'kis_category_id' => ['required', 'integer', 'exists:kis_categories,id'],
            // --- AKHIR VALIDASI BARU ---
            
            // Validasi File KIS
            'file_surat_sehat' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_bukti_bayar' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $userId = Auth::id();
        $path_surat_sehat = $request->file('file_surat_sehat')->store('kis_documents/surat_sehat', 'public');
        $path_bukti_bayar = $request->file('file_bukti_bayar')->store('kis_documents/bukti_bayar', 'public');

        try {
            // 4. Panggil Stored Procedure 'Proc_ApplyForKIS'
            // (Sekarang dengan 11 parameter, sesuai definisi SP)
            DB::statement(
                'CALL Proc_ApplyForKIS(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                [
                    $userId,
                    $validatedData['club_id'],
                    $validatedData['tempat_lahir'],
                    $validatedData['tanggal_lahir'],
                    $validatedData['no_ktp_sim'],
                    $validatedData['golongan_darah'],
                    $validatedData['phone_number'],
                    $validatedData['address'],
                    
                    // --- PARAMETER BARU ---
                    $validatedData['kis_category_id'], 
                    // --- AKHIR PARAMETER BARU ---
                    
                    $path_surat_sehat,
                    $path_bukti_bayar
                ]
            );

            return redirect()->route('dashboard')->with('status', 'Profil berhasil dilengkapi dan pengajuan KIS telah dikirim!');

        } catch (\Exception $e) {
            Storage::disk('public')->delete([$path_surat_sehat, $path_bukti_bayar]);
            \Log::error('Gagal mengajukan KIS (Proc_ApplyForKIS): ' . $e->getMessage());
            
            // Cek error spesifik
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'no_ktp_sim')) {
                 return redirect()->back()->withInput()->with('error', 'No. KTP/SIM yang Anda masukkan sudah terdaftar di sistem.');
            }

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan pada server. Gagal menyimpan data.');
        }
    }
}
