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
     * Mendaftarkan pembalap (Create Invoice).
     * Terhubung ke POST /events/{event}/register
     */
    /**
     * Mendaftarkan pembalap (Create Invoice / Handle Status).
     * Terhubung ke POST /events/{event}/register
     */
    public function store(Request $request, Event $event)
    {
        $user = Auth::user();

        // 1. Validasi Dasar
        if ($event->registration_deadline && $event->registration_deadline->isPast()) {
             return back()->with('error', 'Pendaftaran untuk event ini sudah ditutup.');
        }

        $activeKis = $user->kisLicense()->where('expiry_date', '>=', now())->first();
        if (!$activeKis) return back()->with('error', 'Anda tidak memiliki KIS aktif.');

        // 2. CEK STATUS PENDAFTARAN LAMA
        $existingReg = EventRegistration::where('event_id', $event->id)
                                        ->where('pembalap_user_id', $user->id)
                                        ->first();

        if ($existingReg) {
            // Pembalap sudah daftar, kita cek statusnya
            switch ($existingReg->status) {
                case 'Pending Payment':
                case 'Rejected':
                    // Jika belum bayar ATAU ditolak, arahkan ke halaman bayar untuk upload/upload ulang
                    return redirect()->route('events.payment', $existingReg->id)
                                     ->with('info', 'Silakan selesaikan pendaftaran Anda.');
                case 'Pending Confirmation':
                    return back()->with('info', 'Pendaftaran Anda sedang menunggu konfirmasi panitia.');
                case 'Confirmed':
                    return back()->with('info', 'Anda SUDAH terdaftar di event ini.');
            }
        }

        // 3. JIKA TIDAK ADA PENDAFTARAN LAMA, BUAT BARU
        $initialStatus = ($event->biaya_pendaftaran > 0) ? 'Pending Payment' : 'Confirmed';

        try {
            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'pembalap_user_id' => $user->id,
                'kis_category_id' => $activeKis->kis_category_id,
                'status' => $initialStatus,
                'points_earned' => 0
            ]);

            // 4. REDIRECT SESUAI STATUS
            if ($initialStatus == 'Pending Payment') {
                return redirect()->route('events.payment', $registration->id)
                                 ->with('status', 'Pendaftaran dimulai! Silakan upload bukti pembayaran.');
            } else {
                return back()->with('status', 'Selamat! Anda berhasil terdaftar (Event Gratis).');
            }

        } catch (\Exception $e) {
            \Log::error($e);
            return back()->with('error', 'Gagal mendaftar. Coba lagi.');
        }
    }

    /**
     * Menampilkan Halaman Pembayaran / Upload Bukti.
     */
    public function showPayment(EventRegistration $registration)
    {
        // Security Check: Pastikan yang lihat adalah pemilik pendaftaran
        if ($registration->pembalap_user_id !== Auth::id()) {
            abort(403);
        }

        // Jika status bukan pending payment, tolak akses (misal udah lunas)
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
        if ($registration->pembalap_user_id !== Auth::id())
            abort(403);

        $request->validate([
            'payment_proof' => 'required|image|max:2048', // Max 2MB
        ]);

        // Upload File
        if ($request->hasFile('payment_proof')) {
            // Hapus file lama jika ada (misal re-upload setelah ditolak)
            if ($registration->payment_proof_url) {
                Storage::disk('public')->delete($registration->payment_proof_url);
            }

            $path = $request->file('payment_proof')->store('payment-proofs', 'public');

            // Update Database
            $registration->update([
                'payment_proof_url' => $path,
                'status' => 'Pending Confirmation' // Ubah status agar admin bisa cek
            ]);

            return redirect()->route('events.show', $registration->event_id)
                ->with('status', 'Bukti pembayaran berhasil diupload! Menunggu konfirmasi panitia.');
        }

        return back()->with('error', 'Gagal mengupload file.');
    }
}