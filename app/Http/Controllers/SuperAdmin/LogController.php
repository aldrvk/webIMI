<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Log; 

class LogController extends Controller
{
    /**
     * Menampilkan daftar log aktivitas.
     */
    public function index()
    {
        // 2. Ambil data log, 50 per halaman
        // Kita gunakan 'with('user')' untuk mengambil nama user yang melakukan aksi
        $logs = Log::with('user')
                   ->orderBy('created_at', 'desc') 
                   ->paginate(50);

        // 3. Tampilkan view
        return view('superadmin.logs.index', [
            'logs' => $logs
        ]);
    }
}