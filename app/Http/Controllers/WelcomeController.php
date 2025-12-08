<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\PembalapProfile;
use Illuminate\Support\Facades\DB;

class WelcomeController extends Controller
{
    public function index()
    {
        // Ambil upcoming events yang sudah dipublikasi
        $upcomingEvents = Event::where('is_published', true)
            ->where('event_date', '>=', now()->toDateString())
            ->with('proposingClub')
            ->orderBy('event_date', 'asc')
            ->take(6)
            ->get();

        // Ambil pembalap dengan KIS aktif untuk section profil
        $pembalaps = PembalapProfile::with(['user.kisLicense', 'club', 'user'])
            ->whereHas('user.kisLicense', function($query) {
                $query->where('expiry_date', '>=', now()->toDateString());
            })
            ->orderBy('user_id', 'desc')
            ->take(6)
            ->get();

        return view('welcome', compact('upcomingEvents', 'pembalaps'));
    }
}

