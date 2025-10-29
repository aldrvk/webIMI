<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role The required role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is logged in AND has the specified role
        if (!Auth::check() || Auth::user()->role !== $role) {
            // If not, redirect to dashboard or show an unauthorized error
            // For simplicity, redirecting to dashboard with an error message
            return redirect('dashboard')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
             // Alternatively, you could abort(403, 'Unauthorized action.');
        }

        // If the user has the role, continue with the request
        return $next($request);
    }
}
