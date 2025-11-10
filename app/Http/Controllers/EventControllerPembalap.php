<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Event; // Import model Event

class EventControllerPembalap extends Controller
{
    /**
     * Menampilkan halaman Kalender Event yang interaktif.
     * Terhubung ke Rute GET /events (events.index)
     */
    public function index(Request $request)
    {
        // 1. Tentukan bulan & tahun yang sedang dilihat
        //    (Ambil dari URL query ?month=2025-12, atau default ke bulan ini)
        try {
            // Karbon akan mem-parse string seperti "2025-11"
            $currentDate = Carbon::parse($request->query('month', now()->toDateString()));
        } catch (\Exception $e) {
            $currentDate = Carbon::now();
        }
        
        $monthName = $currentDate->translatedFormat('F Y'); // Contoh: "November 2025"
        $year = $currentDate->year;
        $month = $currentDate->month;

        // 2. Ambil data event yang sudah dipublikasikan HANYA untuk bulan ini
        //    Kita 'keyBy' (kelompokkan) berdasarkan hari agar mudah dicari di view
        $events = Event::where('is_published', true)
                        ->whereYear('event_date', $year)
                        ->whereMonth('event_date', $month)
                        ->with('proposingClub')
                        ->orderBy('event_date', 'asc')
                        ->get()
                        // Mengelompokkan event berdasarkan hari. Cth: $eventsByDay->get(3) akan berisi event di tgl 3
                        ->groupBy(fn($event) => Carbon::parse($event->event_date)->day); 

        // 3. Logika Kalender: Dapatkan tanggal pertama di grid (bisa jadi bulan lalu)
        $firstDayOfMonth = $currentDate->copy()->firstOfMonth();
        $startOfGrid = $firstDayOfMonth->copy()->startOfWeek(Carbon::MONDAY); // Grid dimulai hari Senin
        
        // 4. Buat link Navigasi Bulan
        $prevMonthQuery = route('events.index', ['month' => $currentDate->copy()->subMonth()->format('Y-m')]);
        $nextMonthQuery = route('events.index', ['month' => $currentDate->copy()->addMonth()->format('Y-m')]);

        // 5. Kirim semua data terstruktur ini ke view
        return view('events.index', [
            'monthName' => $monthName,
            'startOfGrid' => $startOfGrid, // Tanggal (Carbon) untuk sel pertama
            'currentMonth' => $month, // Nomor bulan (cth: 11)
            'eventsByDay' => $events, // Data event yang sudah dikelompokkan
            'prevMonthQuery' => $prevMonthQuery,
            'nextMonthQuery' => $nextMonthQuery,
        ]);
    }

    /**
     * Menampilkan detail satu event.
     * (Kita biarkan kosong untuk saat ini)
     */
    public function show(Event $event)
    {
        // Pastikan event sudah dipublikasi
        if (!$event->is_published) {
            abort(404);
        }
        
        // return view('events.show', ['event' => $event]);
        return redirect()->route('events.index'); // Sementara
    }
}