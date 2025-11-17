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
            $query->where('nama_pembalap', 'like', '%' . $searchTerm . '%');
        }

        // 4. Logika Filter Kategori
        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori_id', $request->kategori);
        }

        // 5. Ambil hasil, urutkan berdasarkan poin, dan paginasi
        $leaderboard = $query->orderBy('total_points', 'desc')
                             ->paginate(20) // Tampilkan 20 per halaman
                             ->withQueryString(); // Agar filter tetap ada saat pindah halaman

            // Ubah 'event_date' (string) menjadi objek Carbon (Tanggal) secara manual
            $events->getCollection()->transform(function ($event) {
                $event->event_date = \Carbon\Carbon::parse($event->event_date);
                return $event;
            });
            
        // 4. Kirim data ke view 
        return view('leaderboard.index', [
            'leaderboard' => $leaderboard,
            'categories' => $categories,
            'search' => $request->search ?? '',
            'selectedKategori' => $request->kategori ?? ''
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