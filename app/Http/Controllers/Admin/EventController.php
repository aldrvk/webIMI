<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event; 
use App\Models\Club;  
use App\Models\KisCategory; 
use Illuminate\Support\Facades\Auth; 

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('proposingClub')
                       ->orderBy('is_published', 'desc') 
                       ->orderBy('created_at', 'desc') 
                       ->paginate(15); 

        return view('admin.events.index', [
            'events' => $events
        ]);
    }

    /**
     * Menampilkan formulir untuk membuat event baru.
     */
    public function create()
    {
        $clubs = Club::orderBy('nama_klub', 'asc')->get();
        
        // <-- 2. AMBIL SEMUA KATEGORI KIS (untuk Checkboxes) -->
        $categories = KisCategory::orderBy('tipe', 'asc')->orderBy('nama_kategori', 'asc')->get();

        return view('admin.events.create', [
            'clubs' => $clubs,
            'categories' => $categories // <-- 3. KIRIM KATEGORI KE VIEW -->
        ]);
    }

    /**
     * Menyimpan event baru ke database (DIPERBARUI untuk Many-to-Many)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input (DIPERBARUI)
        $validatedData = $request->validate([
            // Validasi data utama event
            'event_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'event_date' => 'required|date', 
            'proposing_club_id' => 'required|integer|exists:clubs,id', 
            'description' => 'nullable|string',
            'biaya_pendaftaran' => 'nullable|numeric|min:0', 
            'kontak_panitia' => 'nullable|string|max:255',
            'url_regulasi' => 'nullable|url|max:255', 
            
            // <-- 4. VALIDASI BARU UNTUK CHECKBOXES (ARRAY) -->
            'kis_categories_ids' => 'nullable|array', // Harus berupa array (jika ada)
            'kis_categories_ids.*' => 'integer|exists:kis_categories,id' // Setiap item dalam array harus ada di tabel kis_categories
        ]);

        // 2. Tambahkan data yang tidak diisi dari form
        $validatedData['created_by_user_id'] = Auth::id();
        $validatedData['is_published'] = true; // Langsung terbit

        // 3. Simpan Event (Langkah 1: Simpan data utama event)
        // Kita pisahkan data relasi (checkboxes) dari data utama
        $categoryIds = $validatedData['kis_categories_ids'] ?? []; // Ambil array ID (atau array kosong jika tidak ada)
        unset($validatedData['kis_categories_ids']); // Hapus dari data utama agar tidak error saat create
        
        // Buat event HANYA dengan data utamanya
        $event = Event::create($validatedData);

        // 4. Simpan Relasi (Langkah 2: Sync ke pivot table)
        // 'sync' akan menyimpan array ID ke tabel 'event_kis_category'
        $event->kisCategories()->sync($categoryIds); 

        // 5. Redirect ke halaman daftar event
        return redirect()->route('admin.events.index')->with('status', 'Event baru berhasil dipublikasikan.');
    }

    /**
     * Menampilkan form untuk mengedit event.
     */
    public function edit(Event $event)
    {
        $clubs = Club::orderBy('nama_klub')->get();
        
        // <-- 5. AMBIL SEMUA KATEGORI (untuk daftar checkbox) -->
        $categories = KisCategory::orderBy('tipe', 'asc')->orderBy('nama_kategori', 'asc')->get();
        
        // <-- 6. AMBIL ID KATEGORI YANG SUDAH TERPILIH/TERCENTANG (untuk event ini) -->
        // pluck('id') -> mengambil HANYA kolom 'id'
        // toArray() -> mengubahnya dari Collection menjadi array [1, 5, 8]
        $selectedCategories = $event->kisCategories->pluck('id')->toArray();

        return view('admin.events.edit', [
            'event' => $event,
            'clubs' => $clubs,
            'categories' => $categories, // <-- 7. KIRIM SEMUA KATEGORI -->
            'selectedCategories' => $selectedCategories // <-- 8. KIRIM KATEGORI TERPILIH -->
        ]);
    }

    /**
     * Menyimpan perubahan event ke database (DIPERBARUI)
     */
    public function update(Request $request, Event $event)
    {
        // 1. Validasi data (DIPERBARUI)
        $validated = $request->validate([
            // Validasi data utama event
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'proposing_club_id' => 'required|exists:clubs,id',
            'is_published' => 'nullable|boolean',
            'biaya_pendaftaran' => 'nullable|numeric|min:0',
            'kontak_panitia' => 'nullable|string|max:255',
            'url_regulasi' => 'nullable|url|max:255',

            // <-- 9. VALIDASI BARU UNTUK CHECKBOXES (ARRAY) -->
            'kis_categories_ids' => 'nullable|array',
            'kis_categories_ids.*' => 'integer|exists:kis_categories,id'
        ]);

        // 2. Siapkan data (termasuk checkbox publish)
        $validated['is_published'] = $request->has('is_published');

        // 3. Update Event (Langkah 1: Pisahkan & Update data utama)
        $categoryIds = $validated['kis_categories_ids'] ?? []; // Ambil array ID baru
        unset($validated['kis_categories_ids']); // Hapus dari data utama

        $event->update($validated); // Update data utama event

        // 4. Sync Relasi (Langkah 2: Re-Sync ke pivot table)
        // 'sync' akan menghapus centang lama dan menerapkan centang baru
        $event->kisCategories()->sync($categoryIds);

        // 5. Redirect kembali ke daftar event
        return redirect()->route('admin.events.index')->with('status', 'Data event berhasil diperbarui.');
    }
}