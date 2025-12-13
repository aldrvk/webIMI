<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DynamicDBConnection
{
    /**
     * Handle an incoming request.
     * Switch database connection based on authenticated user's role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $role = Auth::user()->role;

            switch ($role) {
                case 'super_admin':
                    Config::set('database.default', 'mysql');
                    break;

                case 'pengurus_imi':
                    Config::set('database.default', 'pengurus_mysql');
                    break;

                case 'pimpinan_imi':
                    Config::set('database.default', 'pimpinan_mysql');
                    break;

                case 'penyelenggara_event':
                    Config::set('database.default', 'penyelenggara_mysql');
                    break;

                case 'pembalap':
                default:
                    Config::set('database.default', 'pembalap_mysql');
                    break;
            }

            DB::reconnect();
        }

        return $next($request);
    }
}