<?php

namespace App\Http\Middleware; 

use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckKisStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // 1. Cek apakah user adalah pembalap
        if ($user && $user->role === 'pembalap') {
            
            // 2. Cek apakah dia punya profil DAN KIS aktif
            // (Kita gunakan relasi yang sudah kita buat di Model User)
            $hasActiveKis = $user->profile()->exists() &&
                            $user->kisLicense()->exists() &&
                            $user->kisLicense->expiry_date >= now()->toDateString();

            if ($hasActiveKis) {
                // 3. Jika KIS aktif, izinkan akses
                return $next($request);
            }

            // 4. Jika KIS tidak aktif, blokir dan kembalikan ke dashboard
            return redirect()->route('dashboard')->with('error', 'Anda harus memiliki KIS yang aktif untuk mengakses halaman ini.');
        }

        // 5. Jika bukan pembalap (misal: Admin), izinkan akses
        return $next($request);
    }
}