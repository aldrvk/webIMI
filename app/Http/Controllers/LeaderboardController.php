<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /**
     * Menampilkan halaman Papan Peringkat (Daftar Event Selesai).
     * Terhubung ke Rute GET /leaderboard
     */
    public function index(Request $request)
    {
        // 1. Mulai query ke Event
        $query = Event::where('is_published', true)
                      // Hanya tampilkan event yang sudah lewat (Selesai)
                      ->where('event_date', '<', now()->toDateString()) 
                      ->with('proposingClub');

        // 2. Logika Search (Telusuri)
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where('event_name', 'like', '%' . $searchTerm . '%');
        }

        // 3. Ambil hasil 
        $events = $query->orderBy('event_date', 'desc')->paginate(10)->withQueryString();

        // 4. Kirim data ke view
        return view('leaderboard.index', [
            'events' => $events,
            'search' => $request->search ?? ''
        ]);
    }
}