<?php

namespace App\Http\Controllers;

use App\Models\Event;

class PublicEventController extends Controller
{
    /**
     * Menampilkan daftar semua event yang dipublikasi (untuk publik).
     */
    public function index()
    {
        $upcomingEvents = Event::where('is_published', true)
            ->where('event_date', '>=', now()->toDateString())
            ->with('proposingClub')
            ->orderBy('event_date', 'asc')
            ->paginate(12);

        $pastEvents = Event::where('is_published', true)
            ->where('event_date', '<', now()->toDateString())
            ->with('proposingClub')
            ->orderBy('event_date', 'desc')
            ->paginate(12);

        return view('events.public-index', compact('upcomingEvents', 'pastEvents'));
    }

    /**
     * Menampilkan halaman detail event untuk publik (tanpa auth).
     */
    public function show($id)
    {
        $event = Event::where('id', $id)
            ->where('is_published', true)
            ->firstOrFail();

        // Load relasi yang diperlukan
        $event->load('proposingClub', 'kisCategories');

        return view('events.public-show', [
            'event' => $event
        ]);
    }
}

