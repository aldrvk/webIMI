<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\KisCategory; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

class LeaderboardController extends Controller
{
    /**
     * Menampilkan halaman Papan Peringkat (dari View_Leaderboard).
     * Terhubung ke Rute GET /leaderboard
     */
    public function index(Request $request)
    {
        // 1. Ambil daftar Kategori untuk dropdown filter
        $categories = KisCategory::orderBy('nama_kategori', 'asc')->get();

        // 2. Mulai query ke SQL VIEW Anda
        $query = DB::table('View_Leaderboard');

        // 3. Logika Filter Search (berdasarkan Nama Pembalap)
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

        // 6. Kirim data ke view
        return view('leaderboard.index', [
            'leaderboard' => $leaderboard,
            'categories' => $categories,
            'search' => $request->search ?? '',
            'selectedKategori' => $request->kategori ?? ''
        ]);
    }
}