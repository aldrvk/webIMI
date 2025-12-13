<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\KisCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Step 1: Tampilkan list event yang sudah selesai
     */
    public function index(Request $request)
    {
        $query = Event::with('proposingClub')
            ->where('event_date', '<', now());
        
        // Handle search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('event_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('location', 'like', '%' . $searchTerm . '%');
            });
        }
        
        $events = $query->orderBy('event_date', 'desc')
            ->paginate(10)
            ->appends($request->only('search')); // Preserve search in pagination

        return view('leaderboard.index', [
            'events' => $events
        ]);
    }

    /**
     * Step 2: Tampilkan detail event + list kategori yang dipertandingkan
     * Jika hanya 1 kategori, redirect langsung ke leaderboard kategori tersebut
     */
    public function showEvent(Event $event)
    {
        // Cek apakah event sudah selesai
        if ($event->event_date >= now()) {
            return redirect()
                ->route('leaderboard.index')
                ->with('error', 'Event ini belum selesai.');
        }

        // Load relasi
        $event->load('proposingClub');
        
        // Ambil kategori dari registrations yang ada (bukan dari pivot table)
        // Karena event mungkin tidak punya relasi di event_kis_category, tapi registrations punya kis_category_id
        $categoryIds = EventRegistration::where('event_id', $event->id)
            ->whereNotNull('kis_category_id')
            ->distinct()
            ->pluck('kis_category_id');
        
        $categories = KisCategory::whereIn('id', $categoryIds)->get();
        
        // Jika hanya 1 kategori, redirect langsung ke leaderboard kategori
        if ($categories->count() === 1) {
            return redirect()->route('leaderboard.show', [
                'event' => $event->id,
                'category' => $categories->first()->id
            ]);
        }
        
        // Jika tidak ada kategori sama sekali
        if ($categories->count() === 0) {
            return redirect()
                ->route('leaderboard.index')
                ->with('error', 'Event ini belum memiliki peserta yang terdaftar.');
        }

        // Ambil overall leaderboard (gabungan semua kategori)
        $overallResults = EventRegistration::with(['pembalap', 'kisCategory'])
            ->where('event_id', $event->id)
            ->whereNotNull('result_position')
            ->orderBy('result_position', 'asc')
            ->limit(10)
            ->get();

        // Hitung total peserta per kategori
        $categoryStats = [];
        foreach ($categories as $category) {
            $categoryStats[$category->id] = [
                'total_peserta' => EventRegistration::where('event_id', $event->id)
                    ->where('kis_category_id', $category->id)
                    ->where('status', 'Confirmed')
                    ->count(),
                'total_finished' => EventRegistration::where('event_id', $event->id)
                    ->where('kis_category_id', $category->id)
                    ->whereNotNull('result_position')
                    ->count(),
            ];
        }

        return view('leaderboard.event', [
            'event' => $event,
            'categories' => $categories,
            'overallResults' => $overallResults,
            'categoryStats' => $categoryStats
        ]);
    }

    /**
     * Step 3: Tampilkan leaderboard untuk kategori tertentu di event tertentu
     */
    public function show(Event $event, $categoryId)
    {
        // Cek apakah event sudah selesai
        if ($event->event_date >= now()) {
            return redirect()
                ->route('leaderboard.index')
                ->with('error', 'Event ini belum selesai.');
        }

        // Load event dengan kategori
        $event->load('proposingClub');
        
        // Cari kategori langsung (bukan via event relation karena pivot table mungkin kosong)
        $category = KisCategory::findOrFail($categoryId);

        // Ambil hasil untuk kategori ini
        $results = EventRegistration::with(['pembalap.profile.club'])
            ->where('event_id', $event->id)
            ->where('kis_category_id', $categoryId)
            ->whereNotNull('result_position')
            ->orderBy('result_position', 'asc')
            ->get();

        // DNF & DSQ
        $dnfDsq = EventRegistration::with(['pembalap'])
            ->where('event_id', $event->id)
            ->where('kis_category_id', $categoryId)
            ->whereIn('result_status', ['DNF', 'DSQ'])
            ->get();

        return view('leaderboard.category', [
            'event' => $event,
            'category' => $category,
            'results' => $results,
            'dnfDsq' => $dnfDsq
        ]);
    }
}