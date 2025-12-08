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
use Carbon\Carbon; 

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
        
        $biayaKis = 0; 
        $infoBank = "Bank BCA\nNo. Rek: 123-456-7890\nA/n IMI Sumut";

        return view('kis.apply', [
            'clubs' => $clubs,
            'categories' => $categories,
            'biayaKis' => $biayaKis,
            'infoBank' => $infoBank
        ]);
    }

    /**
     * Menyimpan pengajuan KIS baru.
     */
    public function store(Request $request)
    {
        // 1. Hitung Umur di Server (Logika Pusat)
        $tanggalLahir = $request->input('tanggal_lahir');
        $isUnder17 = false;
        
        if ($tanggalLahir) {
            $age = Carbon::parse($tanggalLahir)->age;
            $isUnder17 = $age < 17;
        }

        // 2. Validasi Input
        $validatedData = $request->validate([
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'no_ktp_sim' => [
                'required',
                'string',
                'max:30', // NIK (KTP atau KK) - validasi tambahan di closure
                function ($attribute, $value, $fail) {
                    // Jika pengguna memasukkan hanya digit, anggap itu NIK dan wajib 16 digit
                    if (preg_match('/^\d+$/', $value) && strlen($value) !== 16) {
                        $fail('Jika memasukkan NIK (angka saja), maka harus berjumlah 16 digit.');
                    }
                }
            ], // NIK (KTP atau KK)
            'golongan_darah' => ['required', Rule::in(['A', 'B', 'AB', 'O', '-'])],
            'phone_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            'kis_category_id' => ['required', 'integer', 'exists:kis_categories,id'],
            
            'file_surat_sehat' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_bukti_bayar' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'file_pas_foto' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            
            // Validasi Kondisional
            'file_ktp' => [
                $isUnder17 ? 'nullable' : 'required', 
                'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'
            ],
            'file_surat_izin_ortu' => [
                $isUnder17 ? 'required' : 'nullable', 
                'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'
            ],
            'file_kk' => [
                $isUnder17 ? 'required' : 'nullable', 
                'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'
            ],
            
            'persetujuan' => ['required', 'accepted'],
        ]);

        $userId = Auth::id();
        $all_paths = [];

        try {
            // Upload File Umum
            $path_surat_sehat = $request->file('file_surat_sehat')->store('kis_documents/surat_sehat', 'public');
            $all_paths[] = $path_surat_sehat;
            
            $path_bukti_bayar = $request->file('file_bukti_bayar')->store('kis_documents/bukti_bayar', 'public');
            $all_paths[] = $path_bukti_bayar;
            
            $path_pas_foto = $request->file('file_pas_foto')->store('kis_documents/pas_foto', 'public');
            $all_paths[] = $path_pas_foto;

            // Variabel untuk parameter SQL
            $path_ktp = null;
            $path_kk = null;
            $path_surat_izin = null;

            // LOGIKA UPLOAD KONDISIONAL
            if (!$isUnder17) {
                // Dewasa: Wajib KTP
                $path_ktp = $request->file('file_ktp')->store('kis_documents/ktp', 'public');
                $all_paths[] = $path_ktp;
            } else {
                // Anak: Wajib KK & Surat Izin
                $path_kk = $request->file('file_kk')->store('kis_documents/kk', 'public');
                $all_paths[] = $path_kk;
                
                $path_surat_izin = $request->file('file_surat_izin_ortu')->store('kis_documents/surat_izin', 'public');
                $all_paths[] = $path_surat_izin;
            }

            // 3. Panggil Stored Procedure
            DB::statement(
                'CALL Proc_ApplyForKIS(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
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
                    $path_pas_foto, 
                    
                    $path_ktp,         
                    $path_kk,          
                    $path_surat_izin   
                ]
            );

            return redirect()->route('dashboard')->with('status', 'Profil berhasil dilengkapi dan pengajuan KIS telah dikirim!');

        } catch (\Exception $e) {
            Storage::disk('public')->delete($all_paths);
            \Log::error('Gagal mengajukan KIS: ' . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'no_ktp_sim')) {
                 return redirect()->back()->withInput()->with('error', 'No. Identitas yang Anda masukkan sudah terdaftar di sistem.');
            }

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan pada server. Gagal menyimpan data.');
        }
    }
}