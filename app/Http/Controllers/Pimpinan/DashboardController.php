<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\Club;
use App\Models\ClubDues;
use App\Models\KisApplication;
use App\Models\KisCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = $request->get('year', 'overall');
        $year = ($selectedYear === 'overall') ? now()->year : $selectedYear;
        
        // Dapatkan semua tahun yang tersedia untuk dropdown
        $availableYears = DB::table('kis_applications')
            ->selectRaw('YEAR(created_at) as year')
            ->union(DB::table('events')->selectRaw('YEAR(event_date) as year'))
            ->union(DB::table('club_dues')->selectRaw('payment_year as year'))
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter();

        // KPI: Pembalap Aktif (responsif terhadap filter)
        if ($selectedYear === 'overall') {
            // Overall: semua pembalap dengan KIS aktif (belum expired)
            $kpi_pembalap_aktif = DB::table('users')
                ->where('role', 'pembalap')
                ->where('is_active', 1)
                ->whereExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('kis_licenses')
                      ->whereColumn('kis_licenses.pembalap_user_id', 'users.id')
                      ->where('kis_licenses.expiry_date', '>=', now());
                })
                ->count();
        } else {
            // Per tahun: pembalap yang punya KIS approved di tahun tersebut
            $kpi_pembalap_aktif = DB::table('users')
                ->where('role', 'pembalap')
                ->where('is_active', 1)
                ->whereExists(function($q) use ($selectedYear) {
                    $q->select(DB::raw(1))
                      ->from('kis_applications')
                      ->whereColumn('kis_applications.pembalap_user_id', 'users.id')
                      ->where('kis_applications.status', 'Approved')
                      ->whereYear('kis_applications.approved_at', $selectedYear);
                })
                ->count();
        }

        // KPI: Total Klub
        $kpi_klub_total = Club::count();

        // KPI: Event Selesai (responsif terhadap filter)
        if ($selectedYear === 'overall') {
            $kpi_event_selesai = Event::where('event_date', '<', now())->count();
        } else {
            $kpi_event_selesai = Event::whereYear('event_date', $selectedYear)
                ->where('event_date', '<', now())
                ->count();
        }

        // KPI: KIS Pending
        $kpi_kis_pending = KisApplication::where('status', 'Pending')->count();

        // Revenue YTD (responsif terhadap filter)
        if ($selectedYear === 'overall') {
            // KIS revenue: dari kis_applications yang approved * biaya_kis
            $revenue_kis = DB::table('kis_applications as ka')
                ->join('kis_categories as kc', 'ka.kis_category_id', '=', 'kc.id')
                ->where('ka.status', 'Approved')
                ->sum('kc.biaya_kis');
            
            $revenue_iuran = ClubDues::where('status', 'Approved')
                ->sum('amount_paid');
            
            $revenue_event = DB::table('event_registrations')
                ->where('status', 'Confirmed')
                ->sum('amount_paid');
        } else {
            $revenue_kis = DB::table('kis_applications as ka')
                ->join('kis_categories as kc', 'ka.kis_category_id', '=', 'kc.id')
                ->where('ka.status', 'Approved')
                ->whereYear('ka.approved_at', $selectedYear)
                ->sum('kc.biaya_kis');
            
            $revenue_iuran = ClubDues::where('status', 'Approved')
                ->where('payment_year', $selectedYear)
                ->sum('amount_paid');
            
            $revenue_event = DB::table('event_registrations as er')
                ->join('events as e', 'er.event_id', '=', 'e.id')
                ->where('er.status', 'Confirmed')
                ->whereYear('e.event_date', $selectedYear)
                ->sum('er.amount_paid');
        }
        $total_revenue_ytd = $revenue_kis + $revenue_iuran + $revenue_event;

        // PERBAIKAN FINAL: KIS BELUM DIPERBAHARUI
        // LOGIKA BARU: Cari pembalap dengan KIS EXPIRED yang belum perpanjang
        
        if ($selectedYear === 'overall') {
            // Overall: Semua pembalap dengan KIS expired (sampai hari ini)
            $kis_belum_diperbaharui = DB::table('kis_licenses')
                ->where('expiry_date', '<', now()->format('Y-m-d'))
                ->distinct()
                ->count('pembalap_user_id');
                
            \Log::info('KIS Expired Overall', [
                'count' => $kis_belum_diperbaharui,
                'today' => now()->format('Y-m-d')
            ]);
        } else {
            // Per tahun: Pembalap yang KIS-nya EXPIRED pada tahun tersebut dan belum perpanjang
            // Contoh: Tahun 2025 → cari KIS expired tahun 2024 yang belum perpanjang di 2025
            
            $startOfYear = $selectedYear . '-01-01';
            $endOfYear = $selectedYear . '-12-31';
            $today = now()->format('Y-m-d');
            
            // Ambil pembalap dengan KIS expired SEBELUM tahun ini
            $pembalapExpiredSebelumnya = DB::table('kis_licenses')
                ->where('expiry_date', '<', $startOfYear)
                ->pluck('pembalap_user_id');
            
            // Cari yang BELUM perpanjang di tahun ini
            $kis_belum_diperbaharui = DB::table('users')
                ->whereIn('id', $pembalapExpiredSebelumnya)
                ->whereNotExists(function($q) use ($selectedYear) {
                    $q->select(DB::raw(1))
                      ->from('kis_applications')
                      ->whereColumn('kis_applications.pembalap_user_id', 'users.id')
                      ->where('kis_applications.status', 'Approved')
                      ->whereYear('kis_applications.approved_at', $selectedYear);
                })
                ->count();
                
            \Log::info('KIS Expired Year ' . $selectedYear, [
                'expired_sebelumnya' => $pembalapExpiredSebelumnya->count(),
                'belum_perpanjang' => $kis_belum_diperbaharui,
                'start_year' => $startOfYear
            ]);
        }

        // KLUB BELUM BAYAR - sudah benar
        if ($selectedYear === 'overall') {
            $klub_belum_bayar_iuran = DB::table('clubs')
                ->whereNotExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('club_dues')
                      ->whereColumn('club_dues.club_id', 'clubs.id')
                      ->where('club_dues.status', 'Approved');
                })
                ->count();
        } else {
            $klub_belum_bayar_iuran = DB::table('clubs')
                ->whereNotExists(function($q) use ($selectedYear) {
                    $q->select(DB::raw(1))
                      ->from('club_dues')
                      ->whereColumn('club_dues.club_id', 'clubs.id')
                      ->where('club_dues.status', 'Approved')
                      ->where('club_dues.payment_year', $selectedYear);
                })
                ->count();
        }

        // EVENT REGISTRASI RENDAH - sudah benar
        if ($selectedYear === 'overall') {
            $subquery = DB::table('events as e')
                ->leftJoin('event_registrations as er', function($join) {
                    $join->on('e.id', '=', 'er.event_id')
                         ->where('er.status', 'Confirmed');
                })
                ->select('e.id')
                ->groupBy('e.id')
                ->havingRaw('COUNT(er.id) < 10');
            
            $event_low_registration = DB::table(DB::raw("({$subquery->toSql()}) as subquery"))
                ->mergeBindings($subquery)
                ->count();
        } else {
            $subquery = DB::table('events as e')
                ->leftJoin('event_registrations as er', function($join) {
                    $join->on('e.id', '=', 'er.event_id')
                         ->where('er.status', 'Confirmed');
                })
                ->whereYear('e.event_date', $selectedYear)
                ->where('e.event_date', '>=', now())
                ->select('e.id')
                ->groupBy('e.id')
                ->havingRaw('COUNT(er.id) < 10');
            
            $event_low_registration = DB::table(DB::raw("({$subquery->toSql()}) as subquery"))
                ->mergeBindings($subquery)
                ->count();
        }

        // Top Clubs (responsif terhadap filter)
        $top_clubs = $this->getTopClubs($selectedYear);

        // Top Events by Revenue (responsif terhadap filter)
        $top_events_revenue = $this->getTopEventsByRevenue($selectedYear);

        // Line Chart Data - 12 bulan terakhir KIS
        $lineChartData = $this->getLineChartData($selectedYear);

        // Pie Chart Data - Distribusi pembalap per kategori
        $pieChartData = $this->getPieChartData($selectedYear);

        // Overall Leaderboard
        $overallLeaderboard = $this->getOverallLeaderboard($selectedYear);

        // Categories untuk filter
        $categories = KisCategory::all();

        return view('dashboard-pimpinan', compact(
            'kpi_pembalap_aktif',
            'kpi_klub_total',
            'kpi_event_selesai',
            'kpi_kis_pending',
            'revenue_kis',
            'revenue_iuran',
            'revenue_event',
            'total_revenue_ytd',
            'kis_belum_diperbaharui',
            'klub_belum_bayar_iuran',
            'event_low_registration',
            'top_clubs',
            'top_events_revenue',
            'lineChartData',
            'pieChartData',
            'overallLeaderboard',
            'categories',
            'year',
            'selectedYear',
            'availableYears'
        ));
    }

    private function getTopClubs($selectedYear)
    {
        $clubs = DB::table('clubs as c')
            ->select([
                'c.id',
                'c.nama_klub',
                DB::raw('(SELECT COUNT(DISTINCT pp.user_id) 
                         FROM pembalap_profiles pp 
                         JOIN kis_licenses kl ON pp.user_id = kl.pembalap_user_id 
                         WHERE pp.club_id = c.id 
                         AND kl.expiry_date >= CURDATE()) as total_anggota_aktif'),
                DB::raw('(SELECT COUNT(*) FROM events e WHERE e.proposing_club_id = c.id' . 
                        ($selectedYear !== 'overall' ? ' AND YEAR(e.event_date) = ' . $selectedYear : '') . 
                        ') as total_event_tahun_ini'),
                DB::raw('(SELECT CASE WHEN COUNT(*) > 0 THEN "Approved" ELSE "Belum Lunas" END 
                         FROM club_dues cd 
                         WHERE cd.club_id = c.id 
                         AND cd.status = "Approved"' . 
                        ($selectedYear !== 'overall' ? ' AND cd.payment_year = ' . $selectedYear : '') .  
                        ') as status_iuran')
            ])
            ->get()
            ->map(function($club) {
                $club->score_klub = ($club->total_anggota_aktif * 10) + 
                                   ($club->total_event_tahun_ini * 50) + 
                                   (($club->status_iuran === 'Approved') ? 100 : 0);
                return $club;
            })
            ->sortByDesc('score_klub')
            ->take(3);
        
        return $clubs;
    }

    private function getTopEventsByRevenue($selectedYear)
    {
        $query = DB::table('events as e')
            ->leftJoin('event_registrations as er', function($join) {
                $join->on('e.id', '=', 'er.event_id')
                     ->where('er.status', 'Confirmed');
            })
            ->select([
                'e.id',
                'e.event_name',
                'e.event_date',
                DB::raw('CASE 
                    WHEN e.event_date < CURDATE() THEN "Selesai"
                    WHEN e.event_date = CURDATE() THEN "Sedang Berjalan"
                    ELSE "Akan Datang"
                END as status_event'),
                DB::raw('COUNT(er.id) as total_registrants'),
                DB::raw('COALESCE(SUM(er.amount_paid), 0) as total_revenue')
            ])
            ->groupBy('e.id', 'e.event_name', 'e.event_date');
            
        if ($selectedYear !== 'overall') {
            $query->whereYear('e.event_date', $selectedYear);
        }
        
        return $query->orderByDesc('total_revenue')
            ->take(5)
            ->get();
    }

    private function getLineChartData($selectedYear)
    {
        $query = DB::table('kis_applications')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bulan, COUNT(*) as total')
            ->where('status', 'Approved')
            ->groupBy('bulan')
            ->orderBy('bulan', 'desc');
            
        if ($selectedYear !== 'overall') {
            $query->whereYear('created_at', $selectedYear);
        } else {
            $query->where('created_at', '>=', now()->subMonths(12));
        }
        
        return $query->take(12)->get();
    }

    private function getPieChartData($selectedYear)
    {
        $query = DB::table('kis_applications as ka')
            ->join('kis_categories as kc', 'ka.kis_category_id', '=', 'kc.id')
            ->where('ka.status', 'Approved')
            ->selectRaw('kc.nama_kategori, COUNT(*) as total')
            ->groupBy('kc.id', 'kc.nama_kategori');
            
        if ($selectedYear !== 'overall') {
            $query->whereYear('ka.approved_at', $selectedYear);
        }
        
        return $query->get();
    }

    private function getOverallLeaderboard($selectedYear)
    {
        $query = DB::table('event_registrations as er')
            ->join('users as u', 'er.pembalap_user_id', '=', 'u.id')
            ->leftJoin('kis_categories as kc', 'er.kis_category_id', '=', 'kc.id')
            ->where('u.role', 'pembalap')
            ->selectRaw('u.name as nama_pembalap, 
                        COALESCE(kc.nama_kategori, "Umum") as kategori, 
                        SUM(er.points_earned) as total_poin')
            ->groupBy('u.id', 'u.name', 'kc.nama_kategori');
            
        if ($selectedYear !== 'overall') {
            $query->join('events as e', 'er.event_id', '=', 'e.id')
                  ->whereYear('e.event_date', $selectedYear);
        }
        
        return $query->orderByDesc('total_poin')
            ->take(10)
            ->get();
    }
}
