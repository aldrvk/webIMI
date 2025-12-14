<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PimpinanController extends Controller
{
    public function dashboard(Request $request)
    {
        // Ambil tahun dari request, default = tahun sekarang
        $selectedYear = $request->input('year', 'overall');
        $currentYear = now()->year;

        // Daftar tahun yang tersedia (berdasarkan data KIS)
        $availableYears = DB::table('kis_licenses')
            ->selectRaw('DISTINCT YEAR(issued_date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        
        if ($selectedYear === 'overall') {
            // ============================================
            // KPI Overall - Menggunakan View_Dashboard_KPIs
            // ============================================
            $kpis = DB::table('View_Dashboard_KPIs')->first();
            
            $kpi_pembalap_aktif = $kpis->total_pembalap_aktif ?? 0;
            $kpi_klub_total = $kpis->total_klub ?? 0;
            $kpi_event_selesai = $kpis->total_event_selesai ?? 0;
            $kpi_kis_pending = $kpis->total_kis_pending ?? 0;

            // Revenue Overall
            $revenue_iuran = DB::table('club_dues')
                ->where('status', 'Approved')
                ->sum('amount_paid');

            $revenue_kis = DB::table('kis_applications as ka')
                ->join('kis_categories as kc', 'ka.kis_category_id', '=', 'kc.id')
                ->where('ka.status', 'Approved')
                ->sum('kc.biaya_kis');

                $revenue_event = DB::table('event_registrations as er')
                ->join('events as e', 'er.event_id', '=', 'e.id')
                ->where('er.status', 'Confirmed')
                ->selectRaw('SUM(e.biaya_pendaftaran) as total')
                ->value('total') ?? 0;

        } else {
            // KPI Per Tahun
            $year = (int) $selectedYear;
            
            $kpi_pembalap_aktif = DB::table('kis_licenses')
                ->whereYear('issued_date', $year)
                ->where('expiry_date', '>=', "$year-01-01")
                ->count();
            
            $kpi_klub_total = DB::table('clubs')
                ->whereYear('created_at', '<=', $year)
                ->count();
            
            $kpi_event_selesai = DB::table('events')
                ->whereYear('event_date', $year)
                ->where('event_date', '<', now())
                ->where('is_published', true)
                ->count();
            
            $kpi_kis_pending = DB::table('kis_applications')
                ->whereYear('created_at', $year)
                ->where('status', 'Pending')
                ->count();

            // Revenue Per Tahun
            $revenue_iuran = DB::table('club_dues')
                ->where('payment_year', $year)
                ->where('status', 'Approved')
                ->sum('amount_paid');

            $revenue_kis = DB::table('kis_applications as ka')
                ->join('kis_categories as kc', 'ka.kis_category_id', '=', 'kc.id')
                ->where('ka.status', 'Approved')
                ->whereYear('ka.approved_at', $year)
                ->sum('kc.biaya_kis');

                $revenue_event = DB::table('event_registrations as er')
                ->join('events as e', 'er.event_id', '=', 'e.id')
                ->where('er.status', 'Confirmed')
                ->whereYear('e.event_date', $year)
                ->selectRaw('SUM(e.biaya_pendaftaran) as total')
                ->value('total') ?? 0;
        }

        $total_revenue_ytd = $revenue_iuran + $revenue_kis + $revenue_event;

        // ============================================
        // OPERATIONAL ALERTS (Responsif Terhadap Filter)
        // ============================================

        // 1. KIS BELUM DIPERBAHARUI
        if ($selectedYear === 'overall') {
            // Overall: Semua pembalap dengan KIS expired (sampai hari ini)
            $kis_belum_diperbaharui = DB::table('kis_licenses')
                ->where('expiry_date', '<', now()->format('Y-m-d'))
                ->distinct()
                ->count('pembalap_user_id');
        } else {
            // Per tahun: Pembalap yang KIS-nya EXPIRED sebelum tahun ini dan belum perpanjang
            $startOfYear = $selectedYear . '-01-01';
            
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
        }

        // 2. KLUB BELUM BAYAR IURAN
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

        // 3. EVENT REGISTRASI RENDAH (< 10 peserta)
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

        // ============================================
        // TOP CLUBS
        // - Overall: Menggunakan View_Top_Clubs_Performance
        // - Per Tahun: Query manual (View tidak support year filter)
        // ============================================
        if ($selectedYear === 'overall') {
            // Menggunakan View untuk konsistensi
            $top_clubs = DB::table('View_Top_Clubs_Performance')
                ->select([
                    'club_id as id',
                    'nama_klub',
                    'total_events_organized as total_event_tahun_ini',
                    'total_participants as total_anggota_aktif',
                    'club_status as status_iuran',
                    'total_dues_paid'
                ])
                ->orderByDesc('total_events_organized')
                ->limit(3)
                ->get()
                ->map(function($club) {
                    $club->status_iuran = $club->status_iuran === 'Active' ? 'Lunas' : 'Belum Lunas';
                    // Hitung score_klub: anggota*10 + event*50 + (lunas?100:0)
                    $club->score_klub = ($club->total_anggota_aktif * 10) 
                                      + ($club->total_event_tahun_ini * 50) 
                                      + ($club->status_iuran === 'Lunas' ? 100 : 0);
                    return $club;
                });
        } else {
            // Query manual untuk filter per tahun (View tidak support)
            $yearCondition = 'AND YEAR(kl.issued_date) = ' . (int)$selectedYear;
            $eventYearCondition = 'AND YEAR(events.event_date) = ' . (int)$selectedYear;
            $iuranYear = (int)$selectedYear;

            $top_clubs = DB::table('clubs')
                ->select('clubs.*')
                ->selectRaw("
                    (SELECT COUNT(DISTINCT pp.user_id) 
                     FROM pembalap_profiles pp
                     JOIN kis_licenses kl ON kl.pembalap_user_id = pp.user_id
                     WHERE pp.club_id = clubs.id 
                     AND kl.expiry_date >= NOW()
                     $yearCondition
                    ) as total_anggota_aktif
                ")
                ->selectRaw("
                    (SELECT COUNT(*) 
                     FROM events 
                     WHERE events.proposing_club_id = clubs.id
                     $eventYearCondition
                    ) as total_event_tahun_ini
                ")
                ->selectRaw("
                    (SELECT cd.status 
                     FROM club_dues cd 
                     WHERE cd.club_id = clubs.id 
                     AND cd.payment_year = $iuranYear
                     ORDER BY cd.payment_year DESC 
                     LIMIT 1
                    ) as status_iuran
                ")
                ->selectRaw("
                    (
                        (SELECT COUNT(DISTINCT pp.user_id) 
                         FROM pembalap_profiles pp
                         JOIN kis_licenses kl ON kl.pembalap_user_id = pp.user_id
                         WHERE pp.club_id = clubs.id 
                         AND kl.expiry_date >= NOW()
                         $yearCondition
                        ) * 10
                    ) + 
                    (
                        (SELECT COUNT(*) 
                         FROM events 
                         WHERE events.proposing_club_id = clubs.id
                         $eventYearCondition
                        ) * 50
                    ) + 
                    (
                        CASE 
                            WHEN (SELECT cd.status 
                                  FROM club_dues cd 
                                  WHERE cd.club_id = clubs.id 
                                  AND cd.payment_year = $iuranYear
                                  ORDER BY cd.payment_year DESC 
                                  LIMIT 1) = 'Approved' 
                            THEN 100 
                            ELSE 0 
                        END
                    ) as score_klub
                ")
                ->orderByDesc('score_klub')
                ->limit(3)
                ->get()
                ->map(function($club) {
                    $club->status_iuran = $club->status_iuran === 'Approved' ? 'Lunas' : 'Belum Lunas';
                    return $club;
                });
        }

        // ============================================
        // TOP EVENTS BY REVENUE
        // - Overall: Menggunakan View_Event_Revenue_Ranking
        // - Per Tahun: Query manual dengan Func_Get_Event_Status
        // ============================================
        if ($selectedYear === 'overall') {
            // Menggunakan View untuk konsistensi
            $top_events_revenue = DB::table('View_Event_Revenue_Ranking')
                ->select([
                    'event_id as id',
                    'event_name',
                    'event_date',
                    'proposing_club as proposing_club_name',
                    'confirmed_count as total_registrants',
                    'estimated_revenue as total_revenue'
                ])
                ->selectRaw("
                    Func_Get_Event_Status(event_date, event_date, 1) as status_event
                ")
                ->orderByDesc('estimated_revenue')
                ->limit(5)
                ->get();
        } else {
            // Query manual untuk filter per tahun
            $top_events_revenue = DB::table('events as e')
                ->select('e.*')
                ->selectRaw('
                    (SELECT COUNT(*) 
                     FROM event_registrations er 
                     WHERE er.event_id = e.id 
                     AND er.status = "Confirmed"
                    ) as total_registrants
                ')
                ->selectRaw('
                    (e.biaya_pendaftaran * 
                     (SELECT COUNT(*) 
                      FROM event_registrations er 
                      WHERE er.event_id = e.id 
                      AND er.status = "Confirmed")
                    ) as total_revenue
                ')
                ->selectRaw('Func_Get_Event_Status(e.event_date, e.registration_deadline, e.is_published) as status_event')
                ->where('e.is_published', true)
                ->whereYear('e.event_date', (int)$selectedYear)
                ->orderBy('total_revenue', 'desc')
                ->limit(5)
                ->get();
        }

        // ============================================
        // LINE CHART & PIE CHART DATA
        // ============================================
        if ($selectedYear === 'overall') {
            $lineChartData = DB::table('kis_applications')
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bulan, COUNT(*) as total')
                ->where('status', 'Approved')
                ->groupBy('bulan')
                ->orderBy('bulan', 'desc')
                ->limit(12)
                ->get()
                ->reverse()
                ->values();
        } else {
            $year = (int) $selectedYear;
            $lineChartData = DB::table('kis_applications')
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bulan, COUNT(*) as total')
                ->where('status', 'Approved')
                ->whereYear('created_at', $year)
                ->groupBy('bulan')
                ->orderBy('bulan', 'asc')
                ->get();
        }

        $pieChartQuery = DB::table('kis_licenses as kl')
            ->join('kis_categories as kc', 'kl.kis_category_id', '=', 'kc.id')
            ->select('kc.nama_kategori')
            ->selectRaw('COUNT(DISTINCT kl.pembalap_user_id) as total')
            ->where('kl.expiry_date', '>=', now());

        if ($selectedYear !== 'overall') {
            $pieChartQuery->whereYear('kl.issued_date', (int)$selectedYear);
        }

        $pieChartData = $pieChartQuery
            ->groupBy('kc.id', 'kc.nama_kategori')
            ->orderBy('total', 'desc')
            ->get();

        $categories = DB::table('kis_categories')->get();
        
        // Filter kategori untuk leaderboard
        $selectedCategoryId = $request->input('category_id', null);
        $selectedCategory = null;
        if ($selectedCategoryId) {
            $selectedCategory = DB::table('kis_categories')->where('id', $selectedCategoryId)->first();
        }
        
        // Query leaderboard dengan filter kategori
        $leaderboardQuery = DB::connection('mysql')->table('event_registrations as er')
            ->join('users as u', 'er.pembalap_user_id', '=', 'u.id')
            ->leftJoin('kis_categories as kc', 'er.kis_category_id', '=', 'kc.id')
            ->where('u.role', 'pembalap')
            ->whereNotNull('er.points_earned')
            ->where('er.points_earned', '>', 0)
            ->selectRaw('u.name as nama_pembalap, 
                        COALESCE(kc.nama_kategori, "Umum") as kategori, 
                        SUM(er.points_earned) as total_poin')
            ->groupBy('u.id', 'u.name', 'kc.nama_kategori');
        
        // Filter kategori jika dipilih
        if ($selectedCategoryId) {
            $leaderboardQuery->where('er.kis_category_id', $selectedCategoryId);
        }
        
        // Filter tahun jika bukan overall
        if ($selectedYear !== 'overall') {
            $leaderboardQuery->join('events as e', 'er.event_id', '=', 'e.id')
                             ->whereYear('e.event_date', (int)$selectedYear);
        }
        
        $overallLeaderboard = $leaderboardQuery
            ->havingRaw('SUM(er.points_earned) > 0')
            ->orderByDesc('total_poin')
            ->take(10)
            ->get();

        return view('dashboard-pimpinan', compact(
            'kpi_pembalap_aktif',
            'kpi_klub_total',
            'kpi_event_selesai',
            'kpi_kis_pending',
            'total_revenue_ytd',
            'revenue_iuran',
            'revenue_kis',
            'revenue_event',
            'kis_belum_diperbaharui',
            'klub_belum_bayar_iuran',
            'event_low_registration',
            'top_clubs',
            'top_events_revenue',
            'lineChartData',
            'pieChartData',
            'categories',
            'overallLeaderboard',
            'selectedCategoryId',
            'selectedCategory',
            'selectedYear',
            'availableYears'
        ));
    }
}
