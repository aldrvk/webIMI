<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\ClubDues;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PembalapExport;
use App\Exports\EventExport;
use App\Exports\IuranExport;

class ExportController extends Controller
{
    public function pembalapPdf(Request $request)
    {
        $year = $request->input('year', now()->year);
        $pembalap = User::where('role', 'pembalap')
            ->with(['profile.club', 'profile.kisLicense'])
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('exports.pembalap-pdf', [
            'pembalap' => $pembalap,
            'year' => $year
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-pembalap-' . $year . '.pdf');
    }

    public function pembalapExcel(Request $request)
    {
        $year = $request->input('year', now()->year);
        return Excel::download(new PembalapExport($year), 'laporan-pembalap-' . $year . '.xlsx');
    }

    public function eventPdf(Request $request)
    {
        $year = $request->input('year', now()->year);
        $events = Event::whereYear('event_date', $year)
            ->with('proposingClub')
            ->orderBy('event_date')
            ->get();

        $pdf = Pdf::loadView('exports.event-pdf', [
            'events' => $events,
            'year' => $year
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-event-' . $year . '.pdf');
    }

    public function eventExcel(Request $request)
    {
        $year = $request->input('year', now()->year);
        return Excel::download(new EventExport($year), 'laporan-event-' . $year . '.xlsx');
    }

    public function iuranPdf(Request $request)
    {
        try {
            $year = $request->input('year', now()->year);
            
            // Log untuk debugging
            \Log::info('Generating Iuran PDF for year: ' . $year);
            
            // Ambil data iuran dengan eager loading dan error handling
            $iuran = ClubDues::where('payment_year', $year)
                ->with('club')
                ->orderBy('payment_date', 'desc')
                ->get();
            
            \Log::info('Total iuran found: ' . $iuran->count());
            
            // Pastikan view ada
            if (!view()->exists('exports.iuran-pdf')) {
                throw new \Exception('Template PDF iuran tidak ditemukan');
            }

            // Generate PDF dengan timeout lebih lama
            $pdf = Pdf::loadView('exports.iuran-pdf', [
                'iuran' => $iuran,
                'year' => $year
            ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

            return $pdf->download('laporan-iuran-' . $year . '.pdf');
            
        } catch (\Exception $e) {
            \Log::error('Error generating Iuran PDF: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Gagal generate PDF Iuran: ' . $e->getMessage());
        }
    }

    public function iuranExcel(Request $request)
    {
        try {
            $year = $request->input('year', now()->year);
            return Excel::download(new IuranExport($year), 'laporan-iuran-' . $year . '.xlsx');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate Excel: ' . $e->getMessage());
        }
    }
}
