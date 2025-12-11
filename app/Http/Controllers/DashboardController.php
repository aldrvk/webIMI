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
    /**
     * Menampilkan dashboard yang sesuai berdasarkan peran user.
     */
    public function index()
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
            
            case 'pimpinan_imi':
                
                // 1. Ambil KPI (Key Performance Indicators)
                $kpis = DB::table('View_Dashboard_KPIs')->first();
                $data['kpi_pembalap_aktif'] = $kpis->total_pembalap_aktif;
                $data['kpi_klub_total'] = $kpis->total_klub;
                $data['kpi_event_selesai'] = $kpis->total_event_selesai;
                $data['kpi_kis_pending'] = $kpis->total_kis_pending;
                
                // 2. NEW: Revenue Breakdown YTD
                $revenue = DB::table('View_Revenue_Breakdown_YTD')->first();
                $data['revenue_iuran'] = $revenue->revenue_iuran;
                $data['revenue_kis'] = $revenue->revenue_kis;
                $data['revenue_event'] = $revenue->revenue_event;
                $data['total_revenue_ytd'] = $revenue->total_revenue_ytd;
                
                // 3. NEW: Operational Alerts
                $alerts = DB::table('View_Operational_Alerts')->first();
                $data['kis_belum_diperbaharui'] = $alerts->kis_belum_diperbaharui;
                $data['klub_belum_bayar_iuran'] = $alerts->klub_belum_bayar_iuran;
                $data['event_low_registration'] = $alerts->event_low_registration;
                
                // 4. NEW: Top 3 Clubs Performance
                $data['top_clubs'] = DB::table('View_Top_Clubs_Performance')->get();
                
                // 5. NEW: Event Revenue Ranking (Top 5)
                $data['top_events_revenue'] = DB::table('View_Event_Revenue_Ranking')->limit(5)->get();
                
                // 6. Data untuk Tabel Pie Chart: Kirim koleksi mentah
                $data['pieChartData'] = KisLicense::join('kis_categories', 'kis_licenses.kis_category_id', '=', 'kis_categories.id')
                            ->where('expiry_date', '>=', now()->toDateString()) 
                            ->select('kis_categories.nama_kategori', DB::raw('count(kis_licenses.id) as total'))
                            ->groupBy('kis_categories.nama_kategori')
                            ->get();

                // 7. Data untuk Tabel Line Chart: Kirim koleksi mentah
                $data['lineChartData'] = KisApplication::where('status', 'Approved')
                            ->where('approved_at', '>=', now()->subYear())
                            ->select(DB::raw('DATE_FORMAT(approved_at, "%Y-%m") as bulan'), DB::raw('count(id) as total'))
                            ->groupBy('bulan')
                            ->orderBy('bulan', 'asc')
                            ->get();
                
                // 8. Data untuk Tabel Klasemen 
                $data['overallLeaderboard'] = DB::table('View_Leaderboard')
                                                ->orderBy('total_poin', 'desc')
                                                ->take(10)
                                                ->get();
                
                // 9. Ambil data Kategori untuk dropdown
                $data['categories'] = KisCategory::orderBy('tipe')->orderBy('nama_kategori')->get();

                return view('dashboard-pimpinan', $data);
            
            case 'penyelenggara_event':
                return redirect()->route('penyelenggara.dashboard');

            case 'pembalap':
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

            default:
                return redirect('/')->with('error', 'Role tidak dikenali.');
        }
    }
}