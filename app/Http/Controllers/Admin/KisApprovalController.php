<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KisApplication;
use Auth;
use Illuminate\Http\Request;

class KisApprovalController extends Controller
{
    public function index()
    {
        $pendingApplications = KisApplication::where('status', 'Pending')
                                             ->with('pembalap') // Eager load relasi pembalap
                                             ->orderBy('created_at', 'asc') // Tampilkan yang terlama dulu
                                             // ->paginate(10); // Gunakan ini jika ingin pagination
                                             ->get(); 

        return view('admin.kis.index', [
            'applications' => $pendingApplications // Kirim data dengan nama variabel 'applications'
        ]);
    }
    public function show(KisApplication $application)
    {
        $application->load(['pembalap', 'pembalap.profile', 'pembalap.profile.club']); 

        // 3. Kirim data aplikasi ke view detail
        return view('admin.kis.show', [
            'application' => $application // Kirim objek aplikasi
        ]);
    }

    /**
     * Approve a KIS application.
     * Handles PATCH /admin/kis-approvals/{application}/approve
     */
    public function approve(KisApplication $application)
    {
        // Ensure only pending applications can be approved
        if ($application->status !== 'Pending') {
            return redirect()->route('admin.kis.show', $application->id)->with('error', 'Pengajuan ini tidak lagi dalam status Pending.');
        }

        // Update the application status
        $application->update([
            'status' => 'Approved',
            'processed_by_user_id' => Auth::id(), // Record who approved it
            'approved_at' => now(), // Record approval time
            'rejection_reason' => null, // Clear rejection reason if any
        ]);

        // The TRIGGER `auto_create_kis_license_on_approval` will automatically
        // create/update the kis_licenses table now.

        // The TRIGGER `log_kis_application_update` will automatically log this change.

        return redirect()->route('admin.kis.index')->with('status', "Pengajuan KIS #{$application->id} berhasil disetujui.");
    }

    /**
     * Reject a KIS application.
     * Handles PATCH /admin/kis-approvals/{application}/reject
     */
    public function reject(Request $request, KisApplication $application)
    {
         // Ensure only pending applications can be rejected
        if ($application->status !== 'Pending') {
            return redirect()->route('admin.kis.show', $application->id)->with('error', 'Pengajuan ini tidak lagi dalam status Pending.');
        }

        // Validate that a reason is provided
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500', // Reason is mandatory
        ]);

        // Update the application status
        $application->update([
            'status' => 'Rejected',
            'processed_by_user_id' => Auth::id(), // Record who rejected it
            'rejection_reason' => $validated['rejection_reason'],
            'approved_at' => null, // Ensure approval date is null
        ]);

        // The TRIGGER `log_kis_application_update` will automatically log this change.

        // Note: The KIS License trigger won't run because status is not 'Approved'.

        return redirect()->route('admin.kis.index')->with('status', "Pengajuan KIS #{$application->id} berhasil ditolak.");
    }

    
}
