<?php

use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\ClubDuesController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\IuranApprovalController;
use App\Http\Controllers\Admin\KisApprovalController;
use App\Http\Controllers\Admin\PembalapController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KisApplicationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicIuranController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Publik (Guest)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});
// --- RUTE PUBLIK IURAN KLUB (BARU) ---
Route::get('/iuran/submit', [PublicIuranController::class, 'create'])->name('iuran.create');
Route::post('/iuran/store', [PublicIuranController::class, 'store'])->name('iuran.store');
// --- AKHIR RUTE BARU ---

/*
|--------------------------------------------------------------------------
| Rute Terotentikasi (User Login)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {
    // Route Pembalap 
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/kis/apply', [KisApplicationController::class, 'create'])->name('kis.apply');
    Route::post('/kis/apply', [KisApplicationController::class, 'store'])->name('kis.store');

    // Route Pengurus IMI
    Route::middleware('role:pengurus_imi')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/kis-approvals', [KisApprovalController::class, 'index'])->name('kis.index');
        Route::get('/kis-approvals/{application}', [KisApprovalController::class, 'show'])->name('kis.show');
        Route::patch('/kis-approvals/{application}/approve', [KisApprovalController::class, 'approve'])->name('kis.approve');
        Route::patch('/kis-approvals/{application}/reject', [KisApprovalController::class, 'reject'])->name('kis.reject');

        // Rute untuk MENAMPILKAN form buat event
        Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
        // Rute untuk MENYIMPAN event baru
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        // Rute untuk menampilkan DAFTAR event (Persetujuan Event)
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        // Rute untuk MANAJEMEN CLUB baru
        Route::resource('clubs', ClubController::class);

        Route::get('/iuran-approvals', [IuranApprovalController::class, 'index'])->name('iuran.index');
        Route::get('/iuran-approvals/{clubDues}', [IuranApprovalController::class, 'show'])->name('iuran.show');
        Route::patch('/iuran-approvals/{clubDues}/approve', [IuranApprovalController::class, 'approve'])->name('iuran.approve');
        Route::patch('/iuran-approvals/{clubDues}/reject', [IuranApprovalController::class, 'reject'])->name('iuran.reject');

        Route::get('/clubs/{club}/dues/create', [ClubDuesController::class, 'create'])->name('clubs.dues.create');
        Route::post('/clubs/{club}/dues', [ClubDuesController::class, 'store'])->name('clubs.dues.store');

        Route::get('/pembalap', [PembalapController::class, 'index'])->name('pembalap.index');
        Route::get('/pembalap/{profile}', [PembalapController::class, 'show'])->name('pembalap.show');
        Route::patch('/pembalap/{user}/deactivate', [PembalapController::class, 'deactivate'])->name('pembalap.deactivate');
        Route::patch('/pembalap/{user}/activate', [PembalapController::class, 'activate'])->name('pembalap.activate');
    });

});

require __DIR__ . '/auth.php';
