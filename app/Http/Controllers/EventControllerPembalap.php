<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 

class EventControllerPembalap extends Controller
{
    /**
     * Menampilkan kalender event
     */
    public function index(Request $request)
    {
        $currentMonthQuery = $request->query('month', now()->format('Y-m'));
        $currentDate = Carbon::createFromFormat('Y-m', $currentMonthQuery)->startOfMonth();
        $monthName = $currentDate->translatedFormat('F Y');
        $currentMonth = $currentDate->month;
        $startOfGrid = $currentDate->copy()->startOfWeek(Carbon::MONDAY);

        $events = Event::where('is_published', true)
            ->whereYear('event_date', $currentDate->year)
            ->whereMonth('event_date', $currentDate->month)
            ->orderBy('event_date', 'asc')
            ->get();
        
        $eventsByDay = $events->groupBy(function($event) {
            return Carbon::parse($event->event_date)->day;
        });

        $prevMonthQuery = route('events.index', ['month' => $currentDate->copy()->subMonth()->format('Y-m')]);
        $nextMonthQuery = route('events.index', ['month' => $currentDate->copy()->addMonth()->format('Y-m')]);

        return view('events.index', compact(
            'monthName', 
            'currentMonth', 
            'startOfGrid', 
            'eventsByDay', 
            'prevMonthQuery', 
            'nextMonthQuery'
        ));
    }

    /**
     * Menampilkan halaman detail untuk satu event.
     * (Logika "Sadar Status" yang sudah diperbarui)
     */
    public function show(Event $event)
    {
        // 1. Ambil data event
        $event->load('proposingClub', 'kisCategories');

        // 2. Cek apakah pendaftaran DITUTUP (event sudah lewat ATAU deadline lewat)
        $isRegistrationClosed = $event->event_date->isPast() || ($event->registration_deadline && $event->registration_deadline->isPast());

        // 3. Ambil status pendaftaran pembalap SAAT INI (jika ada)
        //    (Perbaikan: Menggunakan Auth::id() yang sudah di-import)
        $userRegistration = $event->registrations()
                                  ->where('pembalap_user_id', Auth::id())
                                  ->first();

        // 4. Tampilkan view baru dan kirim semua datanya
        return view('events.show', [
            'event' => $event,
            'isRegistrationClosed' => $isRegistrationClosed,
            'userRegistration' => $userRegistration // (Bisa null jika belum daftar)
        ]);
    }

    /**
    * Menampilkan hasil lomba (Leaderboard per Event).
    */
   public function results(Event $event)
   {
       // 1. Validasi 
       if (!$event->event_date->isPast()) {
           return redirect()->route('events.show', $event->id)
                            ->with('info', 'Hasil lomba belum tersedia karena event belum selesai.');
       }

       // 2. Ambil data dari SQL View
       $results = DB::table('View_Detailed_Event_Results')
                      ->where('event_id', $event->id)
                      ->orderBy('category_name', 'asc')
                      ->orderBy('result_position', 'asc')
                      ->get();

       // 3. Kelompokkan berdasarkan Nama Kategori
       $groupedResults = $results->groupBy('category_name');

       // 4. Kirim ke view 
       return view('events.results', [
           'event' => $event,
           'groupedResults' => $groupedResults
       ]);
   }
}