<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Log; 

class LogController extends Controller
{
    /**
     * Menampilkan daftar log aktivitas dengan filter dan pencarian.
     */
    public function index(Request $request)
    {
        $query = Log::with('user');

        // Filter by action type
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by table name
        if ($request->filled('table_name')) {
            $query->where('table_name', $request->table_name);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search in record_id or values
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('record_id', 'LIKE', "%{$search}%")
                  ->orWhere('old_value', 'LIKE', "%{$search}%")
                  ->orWhere('new_value', 'LIKE', "%{$search}%");
            });
        }

        // Get logs with pagination
        $logs = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        // Get distinct table names for filter dropdown
        $tableNames = Log::select('table_name')->distinct()->pluck('table_name');

        // Get users who have logs for filter dropdown
        $users = \App\Models\User::whereIn('id', Log::select('user_id')->distinct())->get();

        return view('superadmin.logs.index', [
            'logs' => $logs,
            'tableNames' => $tableNames,
            'users' => $users,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show detailed log entry
     */
    public function show(Log $log)
    {
        $log->load('user');
        
        return view('superadmin.logs.show', [
            'log' => $log
        ]);
    }
}
