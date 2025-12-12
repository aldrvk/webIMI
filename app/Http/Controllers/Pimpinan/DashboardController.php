<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\Club;
use App\Models\ClubDues;
use App\Models\KisApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        
        // Statistik Umum
        $stats = [
            'total_pembalap' => User::where('role', 'pembalap')->count(),
            'total_pembalap_aktif' => User::where('role', 'pembalap')
                ->where('status', 'active')
                ->count(),
            'total_klub' => Club::count(),
            'total_event' => Event::whereYear('event_date', $year)->count(),
            'total_iuran_pending' => ClubDues::where('status', 'Pending')->count(),
            'total_kis_pending' => KisApplication::where('status', 'Pending')->count(),
        ];

        // Event per bulan
        $eventPerBulan = Event::whereYear('event_date', $year)
            ->selectRaw('MONTH(event_date) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // Klub dengan iuran
        $klubIuran = Club::withCount(['clubDues' => function($query) use ($year) {
            $query->where('payment_year', $year)
                  ->where('status', 'Approved');
        }])->get();

        return view('dashboard-pimpinan', compact(
            'stats',
            'eventPerBulan',
            'klubIuran',
            'year'
        ));
    }
}
