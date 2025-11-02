<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event; 
use App\Models\Club;  
use Illuminate\Support\Facades\Auth; 

class EventController extends Controller
{
    public function index()
    {
        // Logika ORDER BY 'status' LAMA DIHAPUS
        // Kita urutkan berdasarkan yang 'is_published' = true, lalu yang terbaru
        $events = Event::with('proposingClub') // Muat relasi klub
                       ->orderBy('is_published', 'desc') // Tampilkan yang terbit dulu
                       ->orderBy('created_at', 'desc') // Tampilkan yang terbaru
                       ->paginate(15); 

        return view('admin.events.index', [
            'events' => $events
        ]);
    }

    /**
     * Menampilkan formulir untuk membuat event baru.
     * (Ini sudah benar, tidak ada perubahan)
     */
    public function create()
    {
        $clubs = Club::orderBy('nama_klub', 'asc')->get();
        return view('admin.events.create', [
            'clubs' => $clubs
        ]);
    }

    /**
     * Menyimpan event baru ke database (DIPERBARUI)
     * (Sesuai komitmen final kita - Alur Publisher)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            'event_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'event_date' => 'required|date', 
            'proposing_club_id' => 'required|integer|exists:clubs,id', 
            'description' => 'nullable|string',
        ]);

        // 2. Buat Event Baru (Langsung Publikasi)
        $event = new Event();
        $event->event_name = $validatedData['event_name'];
        $event->location = $validatedData['location'];
        $event->event_date = $validatedData['event_date'];
        $event->proposing_club_id = $validatedData['proposing_club_id'];
        $event->description = $validatedData['description'];
        $event->created_by_user_id = Auth::id(); // Menggunakan user yang login
        $event->is_published = true; // Langsung terbit

        $event->save(); // Memicu TRIGGER 'log_event_insert' yang baru

        // 3. Redirect ke halaman daftar event
        return redirect()->route('admin.events.index')->with('status', 'Event baru berhasil dipublikasikan.');
    }
}