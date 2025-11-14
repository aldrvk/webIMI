<?php

namespace App\Http\Controllers\Penyelenggara;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Auth;

class PaymentApprovalController extends Controller
{
    /**
     * Menampilkan daftar pendaftaran yang perlu divalidasi.
     */
    public function index(Event $event)
    {
        // 1. Guardrail: Pastikan event ini milik klub penyelenggara
        if ($event->proposing_club_id !== Auth::user()->club_id) {
            abort(403, 'Akses Ditolak');
        }

        // 2. Ambil pendaftaran (Urutkan: Pending Confirmation paling atas)
        $registrations = $event->registrations()
                               ->with(['pembalap', 'kisCategory'])
                               ->orderByRaw("FIELD(status, 'Pending Confirmation', 'Pending Payment', 'Confirmed', 'Rejected')")
                               ->orderBy('created_at', 'desc')
                               ->get();

        return view('penyelenggara.events.payments', [
            'event' => $event,
            'registrations' => $registrations
        ]);
    }

    /**
     * Menyetujui Pembayaran (Approve).
     */
    public function approve(EventRegistration $registration)
    {
        // Guardrail
        if ($registration->event->proposing_club_id !== Auth::user()->club_id) abort(403);

        $registration->update([
            'status' => 'Confirmed',
            'payment_processed_at' => now(),
            'payment_processed_by_user_id' => Auth::id(),
            'admin_note' => null // Hapus catatan error jika ada
        ]);

        return back()->with('status', 'Pembalap ' . $registration->pembalap->name . ' berhasil dikonfirmasi.');
    }

    /**
     * Menolak Pembayaran (Reject).
     */
    public function reject(Request $request, EventRegistration $registration)
    {
        // Guardrail
        if ($registration->event->proposing_club_id !== Auth::user()->club_id) abort(403);

        $request->validate(['admin_note' => 'required|string|max:255']);

        $registration->update([
            'status' => 'Rejected',
            'payment_processed_at' => now(),
            'payment_processed_by_user_id' => Auth::id(),
            'admin_note' => $request->admin_note
        ]);

        return back()->with('status', 'Pendaftaran ditolak. Alasan telah dikirim ke pembalap.');
    }
}