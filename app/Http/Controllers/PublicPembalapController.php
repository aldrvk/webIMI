<?php

namespace App\Http\Controllers;

use App\Models\PembalapProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PublicPembalapController extends Controller
{
    /**
     * Menampilkan daftar semua pembalap dengan KIS aktif (untuk halaman welcome).
     */
    public function index()
    {
        $pembalaps = PembalapProfile::with(['user.kisLicense', 'club', 'user'])
            ->whereHas('user.kisLicense', function($query) {
                $query->where('expiry_date', '>=', now()->toDateString());
            })
            ->orderBy('user_id', 'desc')
            ->paginate(12);

        return view('pembalap.index', compact('pembalaps'));
    }

    /**
     * Menampilkan CV dan histori satu pembalap.
     */
    public function show($id)
    {
        $profile = PembalapProfile::with([
            'user.kisLicense',
            'user.kisApplications.category',
            'club'
        ])->findOrFail($id);

        // Ambil total poin pembalap
        $totalPoints = DB::selectOne(
            "SELECT Func_GetPembalapTotalPoints(?) as total_points",
            [$profile->user_id]
        )->total_points ?? 0;

        // Ambil histori event (hasil lomba)
        $eventHistory = DB::table('View_Detailed_Event_Results')
            ->where('pembalap_user_id', $profile->user_id)
            ->join('events', 'View_Detailed_Event_Results.event_id', '=', 'events.id')
            ->select(
                'events.id as event_id',
                'events.event_name',
                'events.event_date',
                'events.location',
                'View_Detailed_Event_Results.category_name',
                'View_Detailed_Event_Results.result_position',
                'View_Detailed_Event_Results.points_earned'
            )
            ->orderBy('events.event_date', 'desc')
            ->get();

        // Hitung statistik
        $stats = [
            'total_events' => $eventHistory->count(),
            'total_points' => $totalPoints,
            'wins' => $eventHistory->where('result_position', 1)->count(),
            'podiums' => $eventHistory->whereIn('result_position', [1, 2, 3])->count(),
        ];

        return view('pembalap.show', compact('profile', 'eventHistory', 'stats'));
    }
}

