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
            // KPI Overall (All Time)
            $kpi_pembalap_aktif = DB::table('kis_licenses')
                ->where('expiry_date', '>=', now())
                ->count();
            
            $kpi_klub_total = DB::table('clubs')->count();
            
            $kpi_event_selesai = DB::table('events')
                ->where('event_date', '<', now())
                ->where('is_published', true)
                ->count();
            
            $kpi_kis_pending = DB::table('kis_applications')
                ->where('status', 'Pending')
                ->count();

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
        // TOP CLUBS (FIXED - Tanpa Alias di SELECT)
        // ============================================
        $yearCondition = $selectedYear === 'overall' ? '' : 'AND YEAR(kl.issued_date) = ' . (int)$selectedYear;
        $eventYearCondition = $selectedYear === 'overall' ? '' : 'AND YEAR(events.event_date) = ' . (int)$selectedYear;
        $iuranYear = $selectedYear === 'overall' ? $currentYear : (int)$selectedYear;

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
            ->orderByRaw("
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
                ) DESC
            ")
            ->limit(3)
            ->get()
            ->map(function($club) {
                $club->status_iuran = $club->status_iuran === 'Approved' ? 'Lunas' : 'Belum Lunas';
                return $club;
            });

        // ============================================
        // TOP EVENTS BY REVENUE (Berdasarkan Filter)
        // ============================================
        $topEventsQuery = DB::table('events as e')
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
            ->selectRaw('
                CASE 
                    WHEN e.event_date < NOW() THEN "Selesai"
                    WHEN e.event_date = CURDATE() THEN "Sedang Berjalan"
                    ELSE "Akan Datang"
                END as status_event
            ')
            ->where('e.is_published', true);

        if ($selectedYear !== 'overall') {
            $topEventsQuery->whereYear('e.event_date', (int)$selectedYear);
        }

        $top_events_revenue = $topEventsQuery
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

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
        $overallLeaderboard = collect();

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
            'selectedYear',
            'availableYears'
        ));
    }
}
