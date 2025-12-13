<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Menampilkan Event Revenue Ranking
     * Menggunakan View_Event_Revenue_Ranking
     */
    public function eventRevenueRanking()
    {
        $revenueRanking = DB::table('View_Event_Revenue_Ranking')
            ->paginate(10);

        return view('pimpinan.analytics.event-revenue', compact('revenueRanking'));
    }

    /**
     * Menampilkan Operational Alerts
     * Menggunakan View_Operational_Alerts
     */
    public function operationalAlerts()
    {
        $alerts = DB::table('View_Operational_Alerts')
            ->get();

        return view('pimpinan.analytics.alerts', compact('alerts'));
    }

    /**
     * Menampilkan Revenue Breakdown Year-to-Date
     * Menggunakan View_Revenue_Breakdown_YTD
     */
    public function revenueBreakdownYTD()
    {
        $revenueData = DB::table('View_Revenue_Breakdown_YTD')
            ->get();

        return view('pimpinan.analytics.revenue-breakdown', compact('revenueData'));
    }

    /**
     * Menampilkan Top Clubs Performance
     * Menggunakan View_Top_Clubs_Performance
     */
    public function topClubsPerformance()
    {
        $topClubs = DB::table('View_Top_Clubs_Performance')
            ->limit(10)
            ->get();

        return view('pimpinan.analytics.top-clubs', compact('topClubs'));
    }

    /**
     * API endpoint untuk dashboard widgets
     * Menggunakan semua 8 views
     */
    public function dashboardWidgets()
    {
        return response()->json([
            'kpis' => DB::table('View_Dashboard_KPIs')->first(),
            'alerts' => DB::table('View_Operational_Alerts')->get(),
            'revenue_ytd' => DB::table('View_Revenue_Breakdown_YTD')->get(),
            'top_clubs' => DB::table('View_Top_Clubs_Performance')->limit(5)->get(),
            'event_revenue' => DB::table('View_Event_Revenue_Ranking')->limit(5)->get(),
            'leaderboard' => DB::table('View_Leaderboard')->limit(10)->get(),
            'finished_events' => DB::table('View_Finished_Events')->limit(5)->get(),
            'detailed_results' => DB::table('View_Detailed_Event_Results')->limit(10)->get(),
        ]);
    }
}
