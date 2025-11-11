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
     * Menampilkan daftar semua user di sistem.
     * (Fungsi ini sudah ada)
     */
    public function index()
    {
        $users = User::with('club')
                     ->orderBy('name')
                     ->paginate(15); 

        return view('superadmin.users.index', [
            'users' => $users
        ]);
    }

    /**
     * Menampilkan formulir untuk membuat user baru.
     * (Fungsi ini sudah ada)
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
     * (Fungsi ini sudah ada)
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
     * Terhubung ke Rute GET /superadmin/users/{user}/edit
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
     * Terhubung ke Rute PATCH /superadmin/users/{user}
     */
    public function update(Request $request, User $user)
    {
        // Validasi data berbeda dari 'store')
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                Rule::unique('users')->ignore($user->id)
            ],
            'role' => [
                'required', 
                'string', 
                Rule::in(['super_admin', 'pengurus_imi', 'pimpinan_imi', 'penyelenggara_event'])
            ],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], 
            'club_id' => [
                'nullable',
                'required_if:role,penyelenggara_event',
                'exists:clubs,id'
            ],
        ]);
        if ($request->role !== 'penyelenggara_event') {
            $validatedData['club_id'] = null;
        }

        // Pisahkan data password
        $password = $validatedData['password'];
        unset($validatedData['password']);

        // Update data utama
        $user->update($validatedData);

        // Hanya update password JIKA diisi
        if ($password) {
            $user->password = $password;
            $user->save();
        }

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