<?php

use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\ClubDuesController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\IuranApprovalController;
use App\Http\Controllers\Admin\KisApprovalController;
use App\Http\Controllers\Admin\PembalapController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventControllerPembalap;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\KisApplicationController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicIuranController;
use App\Http\Controllers\SuperAdmin\LogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Penyelenggara\DashboardController as PenyelenggaraDashboardController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\Penyelenggara\EventResultController;

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
    Route::get('/events', [EventControllerPembalap::class, 'index'])
        ->middleware('kis.active')
        ->name('events.index');
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])
        ->middleware('kis.active')
        ->name('leaderboard.index');
    Route::get('/leaderboard/{category}', [LeaderboardController::class, 'show'])
        ->middleware('kis.active')
        ->name('leaderboard.show');
    Route::get('/events/{event}', [EventControllerPembalap::class, 'show'])
        ->middleware('kis.active')
        ->name('events.show');
    Route::post('/events/{event}/register', [EventRegistrationController::class, 'store'])
        ->name('events.register')
        ->middleware('kis.active');
    Route::get('/events/{event}/results', [EventControllerPembalap::class, 'results'])
        ->middleware('kis.active')
        ->name('events.results');
    Route::get('/registrations/{registration}/payment', [EventRegistrationController::class, 'showPayment'])
        ->name('events.payment');
    Route::patch('/registrations/{registration}/payment', [EventRegistrationController::class, 'storePayment'])
        ->name('events.payment.store');

    // Route Pengurus IMI
    Route::middleware('role:pengurus_imi')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/kis-approvals', [KisApprovalController::class, 'index'])->name('kis.index');
        Route::get('/kis-approvals/{application}', [KisApprovalController::class, 'show'])->name('kis.show');
        Route::patch('/kis-approvals/{application}/approve', [KisApprovalController::class, 'approve'])->name('kis.approve');
        Route::patch('/kis-approvals/{application}/reject', [KisApprovalController::class, 'reject'])->name('kis.reject');

        Route::resource('events', EventController::class)->only([
            'index',
            'create',
            'store',
            'edit',
            'update'
        ]);
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

    // Route Penyelenggara Event
    Route::middleware('role:penyelenggara_event')->prefix('penyelenggara')->name('penyelenggara.')->group(function () {

        // Gunakan alias 'PenyelenggaraDashboardController' yang sudah kita buat
        Route::get('/dashboard', [PenyelenggaraDashboardController::class, 'index'])->name('dashboard');

        Route::get('/events/{event}/results', [EventResultController::class, 'edit'])->name('events.results.edit');
        Route::post('/events/{event}/results', [EventResultController::class, 'update'])->name('events.results.update');
        Route::get('/events/{event}/payments', [\App\Http\Controllers\Penyelenggara\PaymentApprovalController::class, 'index'])
            ->name('events.payments.index');
        Route::post('/registrations/{registration}/approve', [\App\Http\Controllers\Penyelenggara\PaymentApprovalController::class, 'approve'])
            ->name('registrations.approve');
        Route::post('/registrations/{registration}/reject', [\App\Http\Controllers\Penyelenggara\PaymentApprovalController::class, 'reject'])
            ->name('registrations.reject');
    });

    // Route Super Admin
    Route::middleware('role:super_admin')->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::resource('users', SuperAdminUserController::class);
        Route::get('logs', [LogController::class, 'index'])->name('logs.index');

    });

});

require __DIR__ . '/auth.php';
