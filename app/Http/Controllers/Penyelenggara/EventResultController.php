<?php

namespace App\Http\Controllers\Penyelenggara;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventResultController extends Controller
{
    /**
     * Menampilkan form untuk menginput hasil lomba.
     */
    public function edit(Event $event)
    {
        // 1. Otorisasi: Pastikan user ini HANYA bisa mengedit event milik klubnya
        if ($event->proposing_club_id !== Auth::user()->club_id) {
            abort(403, 'ANDA TIDAK BERHAK MENGAKSES EVENT INI.');
        }

        if (EventRegistration::where('event_id', $event->id)->count() == 0) {
            $pembalapTes = \App\Models\User::where('role', 'pembalap')->first();
            if ($pembalapTes) {
                EventRegistration::create([
                    'event_id' => $event->id,
                    'pembalap_user_id' => $pembalapTes->id,
                    'kis_category_id' => $pembalapTes->kisApplications()->first()->kis_category_id ?? 1,
                ]);
            }
        }

        // 3. Ambil pendaftar (registrants)
        $registrations = EventRegistration::where('event_id', $event->id)
        ->with('pembalap', 'kisCategory')
        ->orderBy('id', 'asc')
        ->get();

        // 4. Kirim data ke view
        return view('penyelenggara.events.results', [
            'event' => $event,
            'registrations' => $registrations
        ]);
    }

    /**
     * Menyimpan (update) hasil lomba ke database.
     * Terhubung ke Rute POST /penyelenggara/events/{event}/results
     */
    public function update(Request $request, Event $event)
    {
        // 1. Otorisasi
        if ($event->proposing_club_id !== Auth::user()->club_id) {
            abort(403, 'ANDA TIDAK BERHAK MENGAKSES EVENT INI.');
        }

        // 2. Validasi Input
        // $request->results akan berbentuk array: [ 'reg_id_1' => ['position' => 1, 'points' => 25], ... ]
        $request->validate([
            'results' => 'required|array',
            'results.*.position' => 'nullable|integer|min:0',
            'results.*.points' => 'nullable|integer|min:0',
        ]);

        // 3. Gunakan Transaksi Database
        DB::beginTransaction();
        try {
            
            foreach ($request->results as $registration_id => $result) {
                // Amankan input (pastikan integer atau null)
                $position = !empty($result['position']) ? (int)$result['position'] : null;
                $points = !empty($result['points']) ? (int)$result['points'] : 0;

                // Update data di tabel 'event_registrations'
                EventRegistration::where('id', $registration_id)
                                 ->where('event_id', $event->id) // Keamanan tambahan
                                 ->update([
                                     'result_position' => $position,
                                     'points_earned' => $points,
                                 ]);
            }

            // 4. Commit transaksi jika semua berhasil
            DB::commit();

            return redirect()->route('penyelenggara.dashboard')->with('status', 'Hasil Lomba untuk event ' . $event->event_name . ' berhasil disimpan!');

        } catch (\Exception $e) {
            // 5. Batalkan semua jika ada 1 error
            DB::rollBack();
            \Log::error('Gagal simpan hasil lomba: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan. Data tidak tersimpan.');
        }
    }
}