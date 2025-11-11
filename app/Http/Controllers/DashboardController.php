<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\KisApplication;
use App\Models\ClubDues;
use App\Models\Club;
use App\Models\PembalapProfile;
use App\Models\Event; 

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard yang sesuai berdasarkan peran user.
     */
    public function index()
    {
        $user = Auth::user();
        $data = []; // Data yang akan dikirim ke view
        $data['user'] = $user;

        // --- GUNAKAN 'SWITCH' UNTUK "SORTING HAT" YANG BERSIH ---

        switch ($user->role) {

            // ==========================================================
            // ==       1. ROLE: SUPER ADMIN                           ==
            // ==========================================================
            case 'super_admin':
                return redirect()->route('superadmin.users.index');

            // ==========================================================
            // ==       2. ROLE: PENGURUS IMI & PIMPINAN IMI         ==
            // ==========================================================
            // Mereka berbagi 'dashboard-admin'
            case 'pengurus_imi':
            case 'pimpinan_imi':
                $data['pendingKisCount'] = KisApplication::where('status', 'Pending')->count();
                $data['pendingIuranCount'] = ClubDues::where('status', 'Pending')->count();
                $data['totalKlub'] = Club::count();
                $data['totalPembalap'] = PembalapProfile::count();

                $data['latestPendingKis'] = KisApplication::where('status', 'Pending')
                                                ->with('pembalap') 
                                                ->latest() 
                                                ->take(5) 
                                                ->get();
                
                $data['latestPendingIuran'] = ClubDues::where('status', 'Pending')
                                                ->with('club') 
                                                ->latest()
                                                ->take(5)
                                                ->get();
                
                $data['upcomingEvents'] = Event::where('is_published', true) 
                                            ->where('event_date', '>=', now()->toDateString())
                                            ->with('proposingClub')
                                            ->orderBy('event_date', 'asc')
                                            ->take(5)
                                            ->get();
                
                return view('dashboard-admin', $data); // <-- MENGARAHKAN KE VIEW ADMIN
            
            // ==========================================================
            // ==       3. ROLE: PENYELENGGARA EVENT                 ==
            // ==========================================================
            case 'penyelenggara_event':
                // Arahkan ke Rute Dashboard Penyelenggara yang sudah kita buat
                return redirect()->route('penyelenggara.dashboard');

            // ==========================================================
            // ==       4. ROLE: PEMBALAP                            ==
            // ==========================================================
            case 'pembalap':
                $data['hasProfile'] = $user->profile()->exists();
                $data['hasActiveKis'] = false;
                $data['hasPendingKis'] = false;
                $data['latestRejectedApplication'] = null;

                if ($data['hasProfile']) {
                    $data['hasActiveKis'] = $user->kisLicense()->exists() && $user->kisLicense->expiry_date >= now()->toDateString();
                    $data['hasPendingKis'] = $user->kisApplications()->where('status', 'Pending')->exists();
                    
                    if (!$data['hasActiveKis'] && !$data['hasPendingKis']) {
                         $data['latestRejectedApplication'] = $user->kisApplications()
                                                                    ->where('status', 'Rejected')
                                                                    ->latest()
                                                                    ->first();
                    }
                }
                
                if($data['hasActiveKis']) {
                    $data['upcomingEvents'] = Event::where('is_published', true)
                                            ->where('event_date', '>=', now()->toDateString())
                                            ->with('proposingClub')
                                            ->orderBy('event_date', 'asc') 
                                            ->take(5) // Ambil 5 event terdekat
                                            ->get();
                } else {
                     $data['upcomingEvents'] = collect();
                }
                
                return view('dashboard-pembalap', $data); 

            default:
                return redirect('/')->with('error', 'Role tidak dikenali.');
        }
    }
}