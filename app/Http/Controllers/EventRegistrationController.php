<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Auth;

class EventRegistrationController extends Controller
{
    /**
     * Mendaftarkan pembalap yang sedang login ke sebuah event.
     * Terhubung ke Rute POST /events/{event}/register
     */
    public function store(Request $request, Event $event)
    {   
        $user = Auth::user();

        // 1. Cek apakah event masih di masa depan
        if (\Carbon\Carbon::parse($event->event_date)->isPast()) {
            return redirect()->back()->with('error', 'Pendaftaran untuk event ini sudah ditutup.');
        }

        // 2. Dapatkan Kategori KIS aktif pembalap
        $activeKis = $user->kisLicense()->where('expiry_date', '>=', now())->first();
        if (!$activeKis) {
            return redirect()->back()->with('error', 'Anda tidak memiliki KIS aktif.');
        }
        
        // Ambil ID Kategori dari KIS 
        $kisCategoryId = $activeKis->kis_category_id;
        if (!$kisCategoryId) {
            return redirect()->back()->with('error', 'Kategori KIS Anda tidak ditemukan.');
        }

        // 3. Cek pendaftaran ganda
        $isAlreadyRegistered = EventRegistration::where('event_id', $event->id)
                                                ->where('pembalap_user_id', $user->id)
                                                ->exists();

        if ($isAlreadyRegistered) {
            return redirect()->back()->with('info', 'Anda sudah terdaftar di event ini.');
        }

        // 4. Buat Pendaftaran (sesuai struktur tabel)
        try {
            EventRegistration::create([
                'event_id' => $event->id,
                'pembalap_user_id' => $user->id,
                'kis_category_id' => $kisCategoryId,
                // 'result_position' dan 'points_earned' akan diisi oleh Penyelenggara nanti
            ]);

            return redirect()->back()->with('status', 'Anda berhasil terdaftar di event: ' . $event->event_name);

        } catch (\Exception $e) {
            \Log::error('Gagal daftar event: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan database saat mendaftar.');
        }
    }
}