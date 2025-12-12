<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use App\Models\KisApplication;
use App\Models\ClubDues;
use App\Models\Club;
use App\Models\PembalapProfile;
use App\Models\Event; 
use App\Models\KisCategory; 
use App\Models\KisLicense;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Redirect ke dashboard yang sesuai berdasarkan role
        switch ($user->role) {
            case 'pimpinan_imi':
                // Redirect ke dashboard pimpinan dengan fitur filter tahun
                return redirect()->route('dashboard.pimpinan', ['year' => $request->input('year', 'overall')]);
            
            case 'pengurus_imi':
            case 'super_admin':
                return $this->adminDashboard();
            
            case 'pembalap':
                return $this->pembalapDashboard();
            
            case 'penyelenggara_event':
                return $this->penyelenggaraDashboard();
            
            default:
                abort(403, 'Unauthorized access.');
        }
    }

    public function adminDashboard()
    {
        $user = Auth::user();
        $data = ['user' => $user];

        switch ($user->role) {
            case 'super_admin':
                return redirect()->route('superadmin.users.index');

            case 'pengurus_imi':
                $data['pendingKisCount'] = KisApplication::where('status', 'Pending')->count();
                $data['pendingIuranCount'] = ClubDues::where('status', 'Pending')->count();
                $data['totalKlub'] = Club::count();
                $data['totalPembalap'] = PembalapProfile::count();
                $data['latestPendingKis'] = KisApplication::where('status', 'Pending')->with('pembalap')->latest()->take(5)->get();
                $data['latestPendingIuran'] = ClubDues::where('status', 'Pending')->with('club')->latest()->take(5)->get();
                $data['upcomingEvents'] = Event::where('is_published', true)->where('event_date', '>=', now()->toDateString())->with('proposingClub')->orderBy('event_date', 'asc')->take(5)->get();
                
                return view('dashboard-admin', $data);
        }
    }

    public function pembalapDashboard()
    {
        $user = Auth::user();
        $data = ['user' => $user];
        $data['hasProfile'] = $user->profile()->exists();
        $data['hasActiveKis'] = false;
        $data['hasPendingKis'] = false;
        $data['latestRejectedApplication'] = null;
        if ($data['hasProfile']) {
            $data['hasActiveKis'] = $user->kisLicense()->exists() && $user->kisLicense->expiry_date >= now()->toDateString();
            $data['hasPendingKis'] = $user->kisApplications()->where('status', 'Pending')->exists();
            if (!$data['hasActiveKis'] && !$data['hasPendingKis']) {
                 $data['latestRejectedApplication'] = $user->kisApplications()->where('status', 'Rejected')->latest()->first();
            }
        }
        if($data['hasActiveKis']) {
            $data['upcomingEvents'] = Event::where('is_published', true)->where('event_date', '>=', now()->toDateString())->with('proposingClub')->orderBy('event_date', 'asc')->take(5)->get();
            
            // Ambil riwayat balapan terakhir untuk ditampilkan di dashboard
            $data['recentRaces'] = $user->eventRegistrations()
                ->whereNotNull('result_position')
                ->with('event')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Statistik pembalap
            $data['totalRaces'] = $user->eventRegistrations()->whereNotNull('result_position')->count();
            $data['totalWins'] = $user->eventRegistrations()->where('result_position', 1)->count();
            $data['totalPodiums'] = $user->eventRegistrations()->whereIn('result_position', [1, 2, 3])->count();
            $data['totalPoints'] = $user->eventRegistrations()->sum('points_earned');
        } else {
             $data['upcomingEvents'] = collect();
             $data['recentRaces'] = collect();
             $data['totalRaces'] = 0;
             $data['totalWins'] = 0;
             $data['totalPodiums'] = 0;
             $data['totalPoints'] = 0;
        }
        
        return view('dashboard-pembalap', $data);
    }

    public function penyelenggaraDashboard()
    {
        return redirect()->route('penyelenggara.dashboard');
    }
}