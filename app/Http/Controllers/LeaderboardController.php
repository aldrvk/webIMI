<?php

namespace App\Http\Controllers;

use App\Models\KisCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

class LeaderboardController extends Controller
{
    /**
     * Menampilkan halaman "Hasil Event"
     */
    public function index(Request $request)
    {
        // 1. Mulai query ke SQL View Anda
        $query = DB::table('View_Finished_Events');

        // 2. Logika Search
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where('event_name', 'like', '%' . $searchTerm . '%');
        }

        // 3. Ambil hasil 
        $events = $query->orderBy('event_date', 'desc')->paginate(10)->withQueryString();

            // Ubah 'event_date' (string) menjadi objek Carbon (Tanggal) secara manual
            $events->getCollection()->transform(function ($event) {
                $event->event_date = \Carbon\Carbon::parse($event->event_date);
                return $event;
            });
            
        // 4. Kirim data ke view 
        return view('leaderboard.index', [
            'events' => $events,
            'search' => $request->search ?? ''
        ]);
    }

    /**
     * Menampilkan Papan Peringkat FINAL untuk satu Kategori.
     */
    public function show(KisCategory $category)
    {
        // 1. Panggil Stored Procedure 'Proc_GetLeaderboard'
        $results = DB::select('CALL Proc_GetLeaderboard(?)', [$category->id]);

        // 2. Kirim data hasil dan data kategori ke view
        return view('leaderboard.show', [
            'category' => $category,
            'results' => $results
        ]);
    }
}