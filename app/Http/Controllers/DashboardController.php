<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\KisApplication;
use App\Models\ClubDues;
use App\Models\Club;
use App\Models\PembalapProfile;
use App\Models\Event; // Pastikan Event di-import

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard yang sesuai berdasarkan peran user.
     */
    public function index()
    {
        $user = Auth::user();
        $data = []; 
        $data['user'] = $user; 

        // --- LOGIKA UNTUK PEMBALAP ---
        if ($user->role === 'pembalap') {
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
            }
        }

        // --- LOGIKA UNTUK PENGURUS & PIMPINAN ---
        if (in_array($user->role, ['pengurus_imi', 'pimpinan_imi', 'super_admin'])) {
            
            // 1. Data KPI (4 Kartu)
            $data['pendingKisCount'] = KisApplication::where('status', 'Pending')->count();
            $data['pendingIuranCount'] = ClubDues::where('status', 'Pending')->count();
            $data['totalKlub'] = Club::count();
            $data['totalPembalap'] = PembalapProfile::count();

            // 2. Data WIDGET "TO-DO LIST"
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
            
            // --- INI PERBAIKANNYA ---
            // Widget 3: Event Terdekat (yang 'is_published' = true)
            $data['upcomingEvents'] = Event::where('is_published', true) // <-- Menggunakan 'is_published'
                                        ->where('event_date', '>=', now()->toDateString())
                                        ->with('proposingClub')
                                        ->orderBy('event_date', 'asc') // Urutkan tanggal terdekat
                                        ->take(5)
                                        ->get();
            // --- AKHIR PERBAIKAN ---
        }

        return view('dashboard', $data);
    }
}