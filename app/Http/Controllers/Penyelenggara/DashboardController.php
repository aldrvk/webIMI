<?php

// Pastikan namespace-nya benar sesuai nama folder Anda
namespace App\Http\Controllers\Penyelenggara; 

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\Club;

class DashboardController extends Controller // Pastikan nama class-nya DashboardController
{
    /**
     * Menampilkan dashboard untuk Penyelenggara Event.
     * Terhubung ke Rute GET /penyelenggara/dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Dapatkan club_id dari user penyelenggara yang login
        // (Ini adalah kolom yang kita tambahkan di migrasi terakhir)
        $clubId = $user->club_id; 

        // 1. Handle jika akun Penyelenggara tidak ter-link ke klub
        if (!$clubId) {
            // Kita akan buat view error ini selanjutnya
            return view('penyelenggara.dashboard-error', [
                'message' => 'Error! Akun Penyelenggara Anda tidak terhubung dengan Klub manapun. Harap hubungi Super Admin.'
            ]);
        }

        // 2. Ambil data klub
        $club = Club::find($clubId);

        // 3. Ambil Event Selesai (yang perlu diisi hasilnya)
        $pastEvents = Event::where('proposing_club_id', $clubId)
                            ->where('is_published', true)
                            ->where('event_date', '<', now()->toDateString())
                            ->orderBy('event_date', 'desc')
                            ->get();

        // 4. Ambil Event Mendatang (yang diajukan klub ini & sudah dipublikasi)
        $upcomingEvents = Event::where('proposing_club_id', $clubId)
                                ->where('is_published', true)
                                ->where('event_date', '>=', now()->toDateString())
                                ->orderBy('event_date', 'asc')
                                ->get();


        // 5. Kirim data ke view dashboard penyelenggara
        return view('penyelenggara.dashboard', [
            'club' => $club,
            'pastEvents' => $pastEvents,
            'upcomingEvents' => $upcomingEvents
        ]);
    }
}