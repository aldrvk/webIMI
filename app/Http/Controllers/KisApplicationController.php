<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\KisApplication;
use App\Models\KisCategory;
use Illuminate\Support\Facades\DB; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class KisApplicationController extends Controller
{
   /**
     * Menampilkan formulir pengajuan KIS.
     */
    public function create()
    {
        $user = Auth::user();

        $hasPendingOrActive = $user->kisApplications()
                                  ->whereIn('status', ['Pending', 'Approved'])
                                  ->exists();

        if ($hasPendingOrActive) {
            return redirect()->route('dashboard')->with('info', 'Anda sudah memiliki pengajuan KIS yang sedang diproses atau sudah disetujui.');
        }

        $clubs = Club::orderBy('nama_klub', 'asc')->get();
        $categories = KisCategory::orderBy('tipe', 'asc')->orderBy('kode_kategori', 'asc')->get();

        return view('kis.apply', [
            'clubs' => $clubs,
            'categories' => $categories
        ]);
    }

    /**
     * Menyimpan pengajuan KIS baru DAN membuat profil pembalap.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input (DIPERBARUI)
        $validatedData = $request->validate([
            // Data Profil
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'no_ktp_sim' => ['required', 'string', 'max:30', 'unique:pembalap_profiles,no_ktp_sim'],
            'golongan_darah' => ['required', Rule::in(['A', 'B', 'AB', 'O', '-'])],
            'phone_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            
            // Data KIS
            'kis_category_id' => ['required', 'integer', 'exists:kis_categories,id'],
            
            // Validasi File 
            'file_surat_sehat' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_bukti_bayar' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_ktp' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // <-- BARU
            'file_pas_foto' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // <-- BARU
            
            // Validasi Checkbox
            'persetujuan' => ['required', 'accepted'], // <-- BARU (PENTING!)
        ]);

        $userId = Auth::id();

        // 2. Upload 4 File
        $path_surat_sehat = $request->file('file_surat_sehat')->store('kis_documents/surat_sehat', 'public');
        $path_bukti_bayar = $request->file('file_bukti_bayar')->store('kis_documents/bukti_bayar', 'public');
        $path_ktp = $request->file('file_ktp')->store('kis_documents/ktp', 'public'); 
        $path_pas_foto = $request->file('file_pas_foto')->store('kis_documents/pas_foto', 'public'); 

        // Kumpulkan path untuk rollback jika gagal
        $all_paths = [$path_surat_sehat, $path_bukti_bayar, $path_ktp, $path_pas_foto];

        try {
            // 3. Panggil Stored Procedure
            DB::statement(
                'CALL Proc_ApplyForKIS(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
                [
                    $userId,
                    $validatedData['club_id'],
                    $validatedData['tempat_lahir'],
                    $validatedData['tanggal_lahir'],
                    $validatedData['no_ktp_sim'],
                    $validatedData['golongan_darah'],
                    $validatedData['phone_number'],
                    $validatedData['address'],
                    $validatedData['kis_category_id'], 
                    
                    $path_surat_sehat,
                    $path_bukti_bayar,
                    
                    $path_ktp, 
                    $path_pas_foto
                ]
            );

            return redirect()->route('dashboard')->with('status', 'Profil berhasil dilengkapi dan pengajuan KIS telah dikirim!');

        } catch (\Exception $e) {
            // 4. Hapus 4 file jika Stored Procedure gagal
            Storage::disk('public')->delete($all_paths);
            
            \Log::error('Gagal mengajukan KIS (Proc_ApplyForKIS): ' . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'no_ktp_sim')) {
                 return redirect()->back()->withInput()->with('error', 'No. KTP/SIM yang Anda masukkan sudah terdaftar di sistem.');
            }

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan pada server. Gagal menyimpan data.');
        }
    }
}