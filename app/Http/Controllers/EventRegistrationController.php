<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventRegistrationController extends Controller
{
    /**
     * Mendaftarkan pembalap (Create Invoice / Handle Status).
     */
    public function store(Request $request, Event $event)
    {
        $user = Auth::user();

        // 1. Validasi Deadline 
        if ($event->registration_deadline && $event->registration_deadline->isPast()) {
             return back()->with('error', 'Pendaftaran untuk event ini sudah ditutup.');
        }

        // 2. Validasi KIS Aktif 
        $activeKis = $user->kisLicense()->with('kisCategory')->where('expiry_date', '>=', now())->first();
        if (!$activeKis) {
            return back()->with('error', 'Anda tidak memiliki KIS (Kartu Izin Start) yang aktif.');
        }
        
        $racerCategoryId = $activeKis->kis_category_id; // KIS Pembalap (misal: 1 - 'C1')
        $racerCategoryName = $activeKis->kisCategory->nama_kategori; // Nama KIS Pembalap
        
        // 3. Ambil semua ID kelas yang DITAWARKAN oleh event ini
        $eventCategoryIds = $event->kisCategories->pluck('id')->toArray(); // Misal: [5, 7, 8]

        // 4. Cek apakah KIS pembalap ada di daftar yang diizinkan
        if (!in_array($racerCategoryId, $eventCategoryIds)) {
            return back()->with('error', 'Gagal: Lisensi KIS Anda ('. $racerCategoryName .') tidak valid untuk mendaftar di kelas manapun pada event ini.');
        }


        // 5. CEK STATUS PENDAFTARAN LAMA (Logika "Sadar Status")
        $existingReg = EventRegistration::where('event_id', $event->id)
                                        ->where('pembalap_user_id', $user->id)
                                        ->first();

        if ($existingReg) {
            switch ($existingReg->status) {
                case 'Pending Payment':
                case 'Rejected':
                    return redirect()->route('events.payment', $existingReg->id)
                                     ->with('info', 'Silakan selesaikan pendaftaran Anda.');
                case 'Pending Confirmation':
                    return back()->with('info', 'Pendaftaran Anda sedang menunggu konfirmasi panitia.');
                case 'Confirmed':
                    return back()->with('info', 'Anda SUDAH terdaftar di event ini.');
            }
        }

        // 6. JIKA LOLOS SEMUA VALIDASI, BUAT BARU
        $initialStatus = ($event->biaya_pendaftaran > 0) ? 'Pending Payment' : 'Confirmed';

        try {
            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'pembalap_user_id' => $user->id,
                'kis_category_id' => $racerCategoryId, // Simpan KIS pembalap
                'status' => $initialStatus,
                'points_earned' => 0
            ]);

            // 7. REDIRECT SESUAI STATUS
            if ($initialStatus == 'Pending Payment') {
                return redirect()->route('events.payment', $registration->id)
                                 ->with('status', 'Pendaftaran dimulai! Silakan upload bukti pembayaran.');
            } else {
                return back()->with('status', 'Selamat! Anda berhasil terdaftar (Event Gratis).');
            }

        } catch (\Exception $e) {
            \Log::error($e);
            return back()->with('error', 'Gagal mendaftar. Terjadi kesalahan database.');
        }
    }

    /**
     * Menampilkan Halaman Pembayaran / Upload Bukti.
     */
    public function showPayment(EventRegistration $registration)
    {
        if ($registration->pembalap_user_id !== Auth::id()) abort(403);

        if ($registration->status !== 'Pending Payment' && $registration->status !== 'Rejected') {
             return redirect()->route('events.show', $registration->event_id)
                              ->with('info', 'Pembayaran sedang diproses atau sudah lunas.');
        }

        return view('events.payment', [
            'registration' => $registration,
            'event' => $registration->event
        ]);
    }

    /**
     * Memproses Upload Bukti Bayar.
     */
    public function storePayment(Request $request, EventRegistration $registration)
    {
        if ($registration->pembalap_user_id !== Auth::id()) abort(403);

        $request->validate([
            'payment_proof' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('payment_proof')) {
            if ($registration->payment_proof_url) {
                Storage::disk('public')->delete($registration->payment_proof_url);
            }

            $path = $request->file('payment_proof')->store('payment-proofs', 'public');

            $registration->update([
                'payment_proof_url' => $path,
                'status' => 'Pending Confirmation'
            ]);

            return redirect()->route('events.show', $registration->event_id)
                             ->with('status', 'Bukti pembayaran berhasil diupload! Menunggu konfirmasi panitia.');
        }

        return back()->with('error', 'Gagal mengupload file.');
    }
    
    /**
     * Mendaftarkan pembalap ke event menggunakan Stored Procedure (alternatif method store)
     */
    public function storeWithProcedure(Request $request, Event $event)
    {
        $user = Auth::user();

        // 1. Validasi Deadline 
        if ($event->registration_deadline && $event->registration_deadline->isPast()) {
             return back()->with('error', 'Pendaftaran untuk event ini sudah ditutup.');
        }

        // 2. Validasi KIS Aktif 
        $activeKis = $user->kisLicense()->with('kisCategory')->where('expiry_date', '>=', now())->first();
        if (!$activeKis) {
            return back()->with('error', 'Anda tidak memiliki KIS (Kartu Izin Start) yang aktif.');
        }
        
        $racerCategoryId = $activeKis->kis_category_id;
        $racerCategoryName = $activeKis->kisCategory->nama_kategori;
        
        // 3. Validasi kategori event
        $eventCategoryIds = $event->kisCategories->pluck('id')->toArray();
        if (!in_array($racerCategoryId, $eventCategoryIds)) {
            return back()->with('error', 'Gagal: Lisensi KIS Anda ('. $racerCategoryName .') tidak valid untuk mendaftar di kelas manapun pada event ini.');
        }

        // 4. CEK STATUS PENDAFTARAN LAMA
        $existingReg = EventRegistration::where('event_id', $event->id)
                                        ->where('pembalap_user_id', $user->id)
                                        ->first();

        if ($existingReg) {
            switch ($existingReg->status) {
                case 'Pending Payment':
                case 'Rejected':
                    return redirect()->route('events.payment', $existingReg->id)
                                     ->with('info', 'Silakan selesaikan pendaftaran Anda.');
                case 'Pending Confirmation':
                    return back()->with('info', 'Pendaftaran Anda sedang menunggu konfirmasi panitia.');
                case 'Confirmed':
                    return back()->with('info', 'Anda SUDAH terdaftar di event ini.');
            }
        }

        try {
            // 5. Panggil Stored Procedure 'Proc_RegisterPembalapToEvent'
            DB::select(
                'CALL Proc_RegisterPembalapToEvent(?, ?, ?, ?)',
                [
                    $event->id,
                    $user->id,
                    $racerCategoryId,
                    null // payment_proof_url (null untuk registrasi awal)
                ]
            );

            // 6. Ambil registrasi yang baru dibuat untuk redirect
            $registration = EventRegistration::where('event_id', $event->id)
                                            ->where('pembalap_user_id', $user->id)
                                            ->first();

            // 7. REDIRECT SESUAI STATUS
            if ($registration && $registration->status == 'Pending Payment') {
                return redirect()->route('events.payment', $registration->id)
                                 ->with('status', 'Pendaftaran dimulai! Silakan upload bukti pembayaran.');
            } else {
                return back()->with('status', 'Selamat! Anda berhasil terdaftar (Event Gratis).');
            }

        } catch (\Exception $e) {
            \Log::error('Gagal mendaftar event (Proc_RegisterPembalapToEvent): ' . $e->getMessage());
            return back()->with('error', 'Gagal mendaftar. Terjadi kesalahan database.');
        }
    }
}