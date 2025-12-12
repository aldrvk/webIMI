<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    // ============================================
    // EXPORT PEMBALAP - KONSISTEN DENGAN KPI
    // ============================================
    public function pembalapPdf(Request $request)
    {
        $year = $request->input('year', 'overall');

        // Query SAMA PERSIS dengan KPI Dashboard
        $baseQuery = DB::table('pembalap_profiles as pp')
            ->join('users as u', 'pp.user_id', '=', 'u.id')
            ->join('clubs as c', 'pp.club_id', '=', 'c.id')
            ->leftJoin('kis_licenses as kl', 'u.id', '=', 'kl.pembalap_user_id')
            ->leftJoin('kis_categories as kc', 'kl.kis_category_id', '=', 'kc.id')
            ->select(
                'u.name as nama',
                'u.email',
                'c.nama_klub',
                'pp.phone_number',
                'kl.kis_number',
                'kc.nama_kategori',
                'kl.issued_date',
                'kl.expiry_date',
                DB::raw("CASE 
                    WHEN kl.expiry_date >= CURDATE() THEN 'Aktif'
                    WHEN kl.expiry_date < CURDATE() THEN 'Expired'
                    ELSE 'Tidak Ada KIS'
                END as status_kis")
            );

        // LOGIKA FILTER SAMA DENGAN KPI
        if ($year !== 'overall') {
            $yearInt = (int) $year;
            // Hanya pembalap yang KIS-nya issued di tahun ini DAN masih aktif per 1 Jan tahun tersebut
            $baseQuery->whereYear('kl.issued_date', '=', $yearInt)
                ->where('kl.expiry_date', '>=', "$yearInt-01-01");
        } else {
            // Overall: Semua pembalap dengan KIS aktif sekarang
            $baseQuery->where('kl.expiry_date', '>=', now());
        }

        $pembalap = $baseQuery->orderBy('u.name')->get();

        $fileName = $year === 'overall'
            ? 'data-pembalap-imi-overall.pdf'
            : 'data-pembalap-imi-' . $year . '.pdf';

        $pdf = Pdf::loadView('exports.pembalap-pdf', [
            'pembalap' => $pembalap,
            'year' => $year
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    public function pembalapExcel(Request $request)
    {
        $year = $request->input('year', 'overall');

        $baseQuery = DB::table('pembalap_profiles as pp')
            ->join('users as u', 'pp.user_id', '=', 'u.id')
            ->join('clubs as c', 'pp.club_id', '=', 'c.id')
            ->leftJoin('kis_licenses as kl', 'u.id', '=', 'kl.pembalap_user_id')
            ->leftJoin('kis_categories as kc', 'kl.kis_category_id', '=', 'kc.id')
            ->select(
                'u.name as nama',
                'u.email',
                'c.nama_klub',
                'pp.phone_number',
                'kl.kis_number',
                'kc.nama_kategori',
                'kl.issued_date',
                'kl.expiry_date',
                DB::raw("CASE 
                    WHEN kl.expiry_date >= CURDATE() THEN 'Aktif'
                    WHEN kl.expiry_date < CURDATE() THEN 'Expired'
                    ELSE 'Tidak Ada KIS'
                END as status_kis")
            );

        if ($year !== 'overall') {
            $yearInt = (int) $year;
            $baseQuery->whereYear('kl.issued_date', '=', $yearInt)
                ->where('kl.expiry_date', '>=', "$yearInt-01-01");
        } else {
            $baseQuery->where('kl.expiry_date', '>=', now());
        }

        $pembalap = $baseQuery->orderBy('u.name')->get();

        $fileName = $year === 'overall'
            ? 'data-pembalap-imi-overall.xlsx'
            : 'data-pembalap-imi-' . $year . '.xlsx';

        return Excel::download(new \App\Exports\PembalapExport($pembalap, $year), $fileName);
    }

    // ============================================
    // EXPORT EVENT - KONSISTEN
    // ============================================
    public function eventPdf(Request $request)
    {
        $year = $request->input('year', 'overall');

        $query = DB::table('events as e')
            ->leftJoin('clubs as c', 'e.proposing_club_id', '=', 'c.id')
            ->select(
                'e.event_name',
                'e.event_date',
                'e.location',
                'e.biaya_pendaftaran',
                'c.nama_klub as penyelenggara',
                DB::raw('(SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = "Confirmed") as total_peserta'),
                DB::raw('(e.biaya_pendaftaran * (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = "Confirmed")) as total_revenue')
            )
            ->where('e.is_published', true);

        if ($year !== 'overall') {
            $query->whereYear('e.event_date', '=', (int) $year);
        }

        $events = $query->orderBy('e.event_date', 'desc')->get();

        $fileName = $year === 'overall'
            ? 'data-event-imi-overall.pdf'
            : 'data-event-imi-' . $year . '.pdf';

        $pdf = Pdf::loadView('exports.event-pdf', [
            'events' => $events,
            'year' => $year
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    public function eventExcel(Request $request)
    {
        $year = $request->input('year', 'overall');

        $query = DB::table('events as e')
            ->leftJoin('clubs as c', 'e.proposing_club_id', '=', 'c.id')
            ->select(
                'e.event_name',
                'e.event_date',
                'e.location',
                'e.biaya_pendaftaran',
                'c.nama_klub as penyelenggara',
                DB::raw('(SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = "Confirmed") as total_peserta'),
                // ✅ QUERY BARU
                DB::raw('(e.biaya_pendaftaran * (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = "Confirmed")) as total_revenue')
            )
            ->where('e.is_published', true);

        if ($year !== 'overall') {
            $query->whereYear('e.event_date', '=', (int) $year);
        }

        $events = $query->orderBy('e.event_date', 'desc')->get();

        $fileName = $year === 'overall'
            ? 'data-event-imi-overall.xlsx'
            : 'data-event-imi-' . $year . '.xlsx';

        return Excel::download(new \App\Exports\EventExport($events, $year), $fileName);
    }

    // ============================================
    // EXPORT IURAN - KONSISTEN
    // ============================================
    public function iuranPdf(Request $request)
    {
        $year = $request->get('year', 'overall');

        // Build query with proper column aliasing
        $iuranData = DB::table('club_dues as cd')
            ->join('clubs as c', 'cd.club_id', '=', 'c.id')
            ->select(
                'c.nama_klub',
                'c.nama_klub as club_name',
                'cd.payment_year',
                'cd.payment_date',
                'cd.amount_paid',
                'cd.status'
            )
            ->where('cd.status', 'Approved');

        // Apply year filter if not 'overall'
        if ($year !== 'overall') {
            $iuranData->where('cd.payment_year', $year);
        }

        $iuranData = $iuranData->orderBy('cd.payment_date', 'desc')->get();

        $pdf = Pdf::loadView('exports.iuran-pdf', [
            'iuran' => $iuranData,
            'year' => $year
        ]);

        return $pdf->download('laporan-iuran-' . ($year === 'overall' ? 'semua-tahun' : $year) . '.pdf');
    }

    public function iuranExcel(Request $request)
    {
        $year = $request->input('year', 'overall');

        $iuranData = DB::table('club_dues as cd')
            ->join('clubs as c', 'cd.club_id', '=', 'c.id')
            ->select(
                'c.nama_klub',
                'c.nama_klub as club_name',
                'cd.payment_year',
                'cd.payment_date',
                'cd.amount_paid',
                'cd.status'
            )
            ->where('cd.status', 'Approved');

        if ($year !== 'overall') {
            $iuranData->where('cd.payment_year', '=', (int) $year);
        }

        $iuranData = $iuranData->orderBy('cd.payment_date', 'desc')->get();

        $fileName = $year === 'overall'
            ? 'data-iuran-imi-overall.xlsx'
            : 'data-iuran-imi-' . $year . '.xlsx';

        return Excel::download(new \App\Exports\IuranExport($iuranData, $year), $fileName);
    }
}