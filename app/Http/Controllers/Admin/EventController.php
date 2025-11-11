<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event; 
use App\Models\Club;  
use App\Models\KisCategory;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    // ... (fungsi index() tetap sama) ...
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

    // ... (fungsi create() tetap sama) ...
    public function create()
    {
        $clubs = Club::orderBy('nama_klub', 'asc')->get();
        $categories = KisCategory::orderBy('tipe', 'asc')->orderBy('nama_kategori', 'asc')->get();

        return view('admin.events.create', [
            'clubs' => $clubs,
            'categories' => $categories
        ]);
    }

    /**
     * Menyimpan event baru (DIPERBARUI DENGAN DEADLINE)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input (DIPERBARUI)
        $validatedData = $request->validate([
            // Data Utama
            'event_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'event_date' => 'required|date|after:now', // Event harus di masa depan
            
            // <-- 1. LOGIKA VALIDASI DEADLINE BARU -->
            'registration_deadline' => 'required|date|after:now|before:event_date', // Deadline harus setelah sekarang, TAPI sebelum tanggal event
            
            // Data Tambahan
            'proposing_club_id' => 'required|integer|exists:clubs,id', 
            'description' => 'nullable|string',
            'biaya_pendaftaran' => 'nullable|numeric|min:0', 
            'kontak_panitia' => 'nullable|string|max:255',
            'url_regulasi' => 'nullable|url|max:255', 
            'image_banner_url' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            
            // Relasi
            'kis_categories_ids' => 'nullable|array',
            'kis_categories_ids.*' => 'integer|exists:kis_categories,id',
        ]);

        // 2. Tambahkan data non-form
        $validatedData['created_by_user_id'] = Auth::id();
        $validatedData['is_published'] = true;

        // 3. Logika File Upload (tetap sama)
        if ($request->hasFile('image_banner_url')) {
            $path = $request->file('image_banner_url')->store('event-posters', 'public');
            $validatedData['image_banner_url'] = $path;
        }

        // 4. Simpan Event (Langkah 1: Simpan data utama)
        $categoryIds = $validatedData['kis_categories_ids'] ?? []; 
        unset($validatedData['kis_categories_ids']); 
        
        // $validatedData sekarang berisi 'registration_deadline'
        $event = Event::create($validatedData);

        // 5. Simpan Relasi (Langkah 2: Sync pivot table)
        $event->kisCategories()->sync($categoryIds); 

        return redirect()->route('admin.events.index')->with('status', 'Event baru berhasil dipublikasikan.');
    }

    // ... (fungsi edit() tetap sama) ...
    public function edit(Event $event)
    {
        $clubs = Club::orderBy('nama_klub')->get();
        $categories = KisCategory::orderBy('tipe', 'asc')->orderBy('nama_kategori', 'asc')->get();
        $selectedCategories = $event->kisCategories->pluck('id')->toArray();

        return view('admin.events.edit', [
            'event' => $event,
            'clubs' => $clubs,
            'categories' => $categories, 
            'selectedCategories' => $selectedCategories 
        ]);
    }

    /**
     * Menyimpan perubahan event (DIPERBARUI DENGAN DEADLINE)
     */
    public function update(Request $request, Event $event)
    {
        // 1. Validasi data (DIPERBARUI)
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            
            // <-- 2. LOGIKA VALIDASI DEADLINE BARU -->
            'registration_deadline' => 'required|date|before:event_date', // Deadline harus sebelum tanggal event

            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'proposing_club_id' => 'required|exists:clubs,id',
            'is_published' => 'nullable|boolean',
            'biaya_pendaftaran' => 'nullable|numeric|min:0',
            'kontak_panitia' => 'nullable|string|max:255',
            'url_regulasi' => 'nullable|url|max:255',
            'image_banner_url' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'kis_categories_ids' => 'nullable|array',
            'kis_categories_ids.*' => 'integer|exists:kis_categories,id'
        ]);

        // 2. Siapkan data (checkbox publish)
        $validated['is_published'] = $request->has('is_published');

        // 3. Logika File Update (tetap sama)
        if ($request->hasFile('image_banner_url')) {
            if ($event->image_banner_url) {
                Storage::disk('public')->delete($event->image_banner_url);
            }
            $path = $request->file('image_banner_url')->store('event-posters', 'public');
            $validated['image_banner_url'] = $path;
        }

        // 4. Update Event (Langkah 1: Update data utama)
        $categoryIds = $validated['kis_categories_ids'] ?? []; 
        unset($validated['kis_categories_ids']);

        // $validatedData sekarang berisi 'registration_deadline'
        $event->update($validated);

        // 5. Sync Relasi (Langkah 2: Re-Sync pivot table)
        $event->kisCategories()->sync($categoryIds);

        return redirect()->route('admin.events.index')->with('status', 'Data event berhasil diperbarui.');
    }
}