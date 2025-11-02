<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PembalapProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PembalapController extends Controller
{
    /**
     * Menampilkan daftar semua pembalap (dengan fitur search).
     */
    public function index(Request $request)
    {
        $query = PembalapProfile::with(['user.kisLicense', 'club']);

        // LOGIKA SEARCH (Telusuri)
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            
            $query->whereHas('user', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%');
            });
        }

        // Ambil hasil
        $profiles = $query->latest('user_id')->paginate(20)->withQueryString(); 

        return view('admin.pembalap.index', [
            'profiles' => $profiles,
            'search' => $request->search ?? '' 
        ]);
    }

    /**
     * Menampilkan detail satu pembalap.
     */
    public function show(PembalapProfile $profile)
    {
        $profile->load([
            'user', 
            'club', 
            'user.kisApplications.category', 
            'user.kisLicense' 
        ]);

        return view('admin.pembalap.show', [
            'profile' => $profile
        ]);
    }

    /**
     * Menon-Aktifkan akun user (memerlukan alasan).
     * Terhubung ke Rute PATCH admin.pembalap.deactivate
     */
    public function deactivate(Request $request, User $user)
    {
        // 1. Validasi Alasan
        $validated = $request->validate([
            'deactivation_reason' => 'required|string|max:255',
        ]);

        // 2. Update status dan alasan
        $user->is_active = false;
        $user->deactivation_reason = $validated['deactivation_reason'];
        $user->save();

        // 3. Redirect kembali ke halaman detail profil
        return redirect()->route('admin.pembalap.show', $user->profile->id)->with('status', 'Akun berhasil dinon-aktifkan.');
    }

    /**
     * Mengaktifkan kembali akun user.
     * Terhubung ke Rute PATCH admin.pembalap.activate
     */
    public function activate(User $user)
    {
        // 1. Update status
        $user->is_active = true;
        $user->deactivation_reason = null; // Hapus alasan lama
        $user->save();

        // 2. Redirect kembali
        return redirect()->route('admin.pembalap.show', $user->profile->id)->with('status', 'Akun berhasil diaktifkan kembali.');
    }
}