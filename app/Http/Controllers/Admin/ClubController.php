<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    /**
     * Menampilkan daftar semua klub.
     */
    public function index()
    {
        // 1. Ambil data klub, dan cek status iuran untuk TAHUN INI (misal 2025)
        $currentYear = now()->year;
        $clubs = Club::withExists([
            'duesHistory' => function ($query) use ($currentYear) {
                $query->where('payment_year', $currentYear)
                    ->where('status', 'Approved');
            }
        ])
            ->orderBy('nama_klub', 'asc')
            ->paginate(20);

        // 2. Kirim data ke view
        return view('admin.clubs.index', [
            'clubs' => $clubs,
            'currentYear' => $currentYear
        ]);
    }

    /**
     * Menampilkan formulir untuk membuat klub baru.
     */
    public function create()
    {
        return view('admin.clubs.create');
    }

    /**
     * Menyimpan klub baru ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            'nama_klub' => 'required|string|max:255|unique:clubs,nama_klub',
            'alamat' => 'nullable|string',
            'nama_ketua' => 'nullable|string|max:255',
            'hp' => 'nullable|string|max:20',
            'email_klub' => 'nullable|email|max:255|unique:clubs,email_klub',
            // 'status_iuran' DIHAPUS
        ]);

        // 2. Buat dan Simpan Klub Baru
        Club::create($validatedData);

        // 3. Redirect kembali
        return redirect()->route('admin.clubs.index')->with('status', 'Klub baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail satu klub.
     */
    public function show(Club $club)
    {
        // 1. Muat relasi riwayat iuran (duesHistory)
        //    DAN muat relasi 'processor' (user pengurus) dari riwayat tersebut
        $club->load('duesHistory.processor'); 
        
        // 2. Kirim data klub (termasuk riwayat iurannya) ke view
        return view('admin.clubs.show', [
            'club' => $club
        ]);
    }

    /**
     * Menampilkan formulir untuk mengedit klub.
     */
    public function edit(Club $club)
    {
        return view('admin.clubs.edit', [
            'club' => $club
        ]);
    }

    /**
     * Memperbarui klub di database.
     */
    public function update(Request $request, Club $club)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            'nama_klub' => 'required|string|max:255|unique:clubs,nama_klub,' . $club->id,
            'alamat' => 'nullable|string',
            'nama_ketua' => 'nullable|string|max:255',
            'hp' => 'nullable|string|max:20',
            'email_klub' => 'nullable|email|max:255|unique:clubs,email_klub,' . $club->id,
        ]);

        // 2. Update data klub
        $club->update($validatedData);

        // 3. Redirect kembali
        return redirect()->route('admin.clubs.index')->with('status', 'Data klub berhasil diperbarui.');
    }

    /**
     * Menghapus klub dari database.
     */
    public function destroy(Club $club)
    {
        try {
            $club->delete();
            return redirect()->route('admin.clubs.index')->with('status', 'Klub berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->route('admin.clubs.index')->with('error', 'Klub tidak bisa dihapus karena masih digunakan oleh pembalap, event, atau riwayat iuran.');
            }
            return redirect()->route('admin.clubs.index')->with('error', 'Gagal menghapus klub: ' . $e->getMessage());
        }
    }
}