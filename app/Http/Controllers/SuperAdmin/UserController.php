<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua user dan search di sistem.
     */ 
    public function index(Request $request) 
    {
        $search = $request->input('search'); 

        $users = User::with('club')

                    ->when($search, function ($query, $term) {
                        $query->where('name', 'like', '%' . $term . '%')
                              ->orWhere('email', 'like', '%' . $term . '%');
                    })
                    ->orderBy('name')
                    ->paginate(15)
                    ->withQueryString(); 

        return view('superadmin.users.index', [
            'users' => $users,
            'search' => $search
        ]);
    }


    /**
     * Menampilkan formulir untuk membuat user baru.
     */
    public function create()
    {
        $roles = [
            'super_admin' => 'Super Admin',
            'pengurus_imi' => 'Pengurus IMI',
            'pimpinan_imi' => 'Pimpinan IMI',
            'penyelenggara_event' => 'Penyelenggara Event',
        ];

        $clubs = Club::orderBy('nama_klub')->get(); 

        return view('superadmin.users.create', [
            'roles' => $roles,
            'clubs' => $clubs
        ]);
    }

    /**
     * Menyimpan user baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => [
                'required', 
                'string', 
                Rule::in(['super_admin', 'pengurus_imi', 'pimpinan_imi', 'penyelenggara_event'])
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'club_id' => [
                'nullable',
                'required_if:role,penyelenggara_event', 
                'exists:clubs,id'
            ],
        ]);
        if ($request->role !== 'penyelenggara_event') {
            $validatedData['club_id'] = null;
        }
        User::create($validatedData);

        return redirect()->route('superadmin.users.index')->with('status', 'User baru berhasil dibuat!');
    }


    /**
     * * Menampilkan formulir untuk mengedit user.
     */
    public function edit(User $user) 
    {
        // Tentukan role yang bisa di-edit
        $roles = [
            'super_admin' => 'Super Admin',
            'pengurus_imi' => 'Pengurus IMI',
            'pimpinan_imi' => 'Pimpinan IMI',
            'penyelenggara_event' => 'Penyelenggara Event',
        ];

        $clubs = Club::orderBy('nama_klub')->get();

        return view('superadmin.users.edit', [
            'user' => $user,
            'roles' => $roles,
            'clubs' => $clubs 
        ]);
    }

    /**
     * * Menyimpan perubahan user ke database.
     */
    public function update(Request $request, User $user)
    {
        // Validasi data berbeda dari 'store')
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ];
        
        // Hanya validate role jika bukan pembalap
        if ($user->role !== 'pembalap') {
            $rules['role'] = 'required|in:super_admin,pengurus_imi,pimpinan_imi,penyelenggara_event';
            $rules['club_id'] = 'nullable|required_if:role,penyelenggara_event|exists:clubs,id';
        }
        
        $validated = $request->validate($rules);

        // Update data utama
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        // Hanya update role jika bukan pembalap
        if ($user->role !== 'pembalap' && isset($validated['role'])) {
            $user->role = $validated['role'];
            
            // Set club_id
            if ($validated['role'] === 'penyelenggara_event') {
                $user->club_id = $validated['club_id'];
            } else {
                $user->club_id = null;
            }
        }
        
        // Update password jika diisi
        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();

        // Redirect kembali ke halaman index
        return redirect()->route('superadmin.users.index')->with('status', 'Data user berhasil diperbarui!');
    }

    /**
     * Menghapus user dari database.
     */
    public function destroy(User $user)
    {
        // Cek apakah user mencoba menghapus diri sendiri
        if ($user->id === Auth::id()) {
            return redirect()->route('superadmin.users.index')
                             ->with('error', ' Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Cek apakah user memiliki "pekerjaan" terkait (data foreign key)
        if ($user->createdEvents()->exists()) {
            return redirect()->route('superadmin.users.index')
                             ->with('error', ' Gagal: User ini telah membuat event. Hapus/re-assign event tersebut terlebih dahulu.');
        }
        
        if ($user->processedKisApplications()->exists()) {
            return redirect()->route('superadmin.users.index')
                             ->with('error', ' Gagal: User ini telah memproses aplikasi KIS. Hapus/re-assign data KIS tersebut terlebih dahulu.');
        }

        if ($user->processedDues()->exists()) {
             return redirect()->route('superadmin.users.index')
                             ->with('error', ' Gagal: User ini telah memproses iuran klub. Hapus/re-assign data iuran tersebut terlebih dahulu.');
        }

        // Pengecekan untuk Pembalap
        if ($user->role === 'pembalap') {
            if ($user->profile()->exists() || $user->kisApplications()->exists() || $user->kisLicense()->exists() || $user->eventRegistrations()->exists()) {
                 return redirect()->route('superadmin.users.index')
                             ->with('error', ' Gagal: Pembalap ini memiliki data profil/KIS/registrasi event. Hapus data terkaitnya terlebih dahulu.');
            }
        }


        // 3. Hapus user
        $user->delete();

        // 4. Redirect kembali dengan pesan sukses
        return redirect()->route('superadmin.users.index')
                         ->with('status', ' User (' . $user->name . ') berhasil dihapus.');
    }
}