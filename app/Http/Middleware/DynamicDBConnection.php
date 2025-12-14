<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DynamicDBConnection
{
    /**
     * Role to database connection mapping
     */
    private array $roleConnectionMap = [
        'super_admin' => 'mysql',
        'pengurus_imi' => 'pengurus_mysql',
        'pimpinan_imi' => 'pimpinan_mysql',
        'penyelenggara_event' => 'penyelenggara_mysql',
        'pembalap' => 'pembalap_mysql',
    ];

    /**
     * Handle an incoming request.
     * Switch database connection based on authenticated user's role.
     * 
     * RBAC Strict Mode (env: RBAC_STRICT_MODE=true):
     * - Jika true: throw error jika koneksi gagal (production recommended)
     * - Jika false: fallback ke mysql default (development/testing)
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $role = Auth::user()->role;
            $targetConnection = $this->roleConnectionMap[$role] ?? 'pembalap_mysql';
            $strictMode = env('RBAC_STRICT_MODE', false);

            try {
                Config::set('database.default', $targetConnection);
                DB::reconnect();
                
                // Test connection dengan simple query
                DB::connection()->getPdo();
                
                Log::debug('RBAC DB Connection switched', [
                    'user_id' => Auth::id(),
                    'role' => $role,
                    'connection' => $targetConnection
                ]);
                
            } catch (\Exception $e) {
                Log::error('RBAC DB Connection FAILED', [
                    'user_id' => Auth::id(),
                    'role' => $role,
                    'attempted_connection' => $targetConnection,
                    'error' => $e->getMessage(),
                    'strict_mode' => $strictMode
                ]);
                
                if ($strictMode) {
                    // STRICT MODE: Tidak fallback, throw error
                    // User akan melihat error page
                    throw new \RuntimeException(
                        "Database connection failed for role '{$role}'. " .
                        "Please ensure the database user '{$targetConnection}' is properly configured. " .
                        "Contact system administrator."
                    );
                }
                
                // NON-STRICT MODE: Fallback ke mysql (development only)
                Log::warning('RBAC Fallback to mysql (non-strict mode)', [
                    'role' => $role,
                    'original_connection' => $targetConnection
                ]);
                
                Config::set('database.default', 'mysql');
                DB::reconnect();
            }
        }

        return $next($request);
    }
}

