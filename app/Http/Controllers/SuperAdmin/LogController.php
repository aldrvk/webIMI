<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Log; 

class LogController extends Controller
{
    /**
     * Menampilkan daftar log aktivitas dengan filter.
     */
    public function index(Request $request)
    {
        // Query builder untuk log
        $query = Log::with('user');

        // Filter berdasarkan Action Type
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter berdasarkan Table Name
        if ($request->filled('table_name')) {
            $query->where('table_name', $request->table_name);
        }

        // Filter berdasarkan User ID
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by keyword (di new_value atau old_value)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('new_value', 'like', "%{$search}%")
                  ->orWhere('old_value', 'like', "%{$search}%");
            });
        }

        // Order by created_at descending
        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get unique table names untuk dropdown filter
        $tableNames = Log::select('table_name')
                          ->distinct()
                          ->orderBy('table_name')
                          ->pluck('table_name');

        // Get unique action types
        $actionTypes = ['INSERT', 'UPDATE', 'DELETE'];

        // Tampilkan view dengan data filter
        return view('superadmin.logs.index', [
            'logs' => $logs,
            'tableNames' => $tableNames,
            'actionTypes' => $actionTypes,
            'filters' => $request->only(['action_type', 'table_name', 'user_id', 'date_from', 'date_to', 'search'])
        ]);
    }
}