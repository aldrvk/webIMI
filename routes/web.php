<?php

use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\ClubDuesController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\IuranApprovalController;
use App\Http\Controllers\Admin\KisApprovalController;
use App\Http\Controllers\Admin\PembalapController;
use App\Http\Controllers\Admin\PembalapRegistrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventControllerPembalap;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\KisApplicationController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicIuranController;
use App\Http\Controllers\RacerHistoryController;
use App\Http\Controllers\SuperAdmin\LogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Penyelenggara\DashboardController as PenyelenggaraDashboardController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\Penyelenggara\EventResultController;
use App\Http\Controllers\Pimpinan\DashboardController as PimpinanDashboardController;
use App\Http\Controllers\Pimpinan\ExportController;
use App\Http\Controllers\Pimpinan\PimpinanController;
use App\Http\Controllers\Pimpinan\AnalyticsController;

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
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard route sesuai role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard Pimpinan dengan filter tahun
    Route::get('/dashboard-pimpinan', [PimpinanController::class, 'dashboard'])->name('dashboard.pimpinan');

    // Route Pembalap 
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/kis/apply', [KisApplicationController::class, 'create'])->name('kis.apply');
    Route::post('/kis/apply', [KisApplicationController::class, 'store'])->name('kis.store');
    
    // Protected routes - Requires Active KIS
    Route::middleware('kis.active')->group(function () {
        Route::get('/events', [EventControllerPembalap::class, 'index'])->name('events.index');
        Route::get('/events/{event}', [EventControllerPembalap::class, 'show'])->name('events.show');
        Route::post('/events/{event}/register', [EventRegistrationController::class, 'store'])->name('events.register');
        Route::post('/events/{event}/register-sp', [EventRegistrationController::class, 'storeWithProcedure'])->name('events.register.sp');
        Route::get('/events/{event}/results', [EventControllerPembalap::class, 'results'])->name('events.results');
        
        // Leaderboard / Hasil Event Flow
        Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
        Route::get('/leaderboard/event/{event}', [LeaderboardController::class, 'showEvent'])->name('leaderboard.event');
        Route::get('/leaderboard/event/{event}/kategori/{category}', [LeaderboardController::class, 'show'])->name('leaderboard.show');
        Route::get('/leaderboard/overall', [LeaderboardController::class, 'overall'])->name('leaderboard.overall');

        // History Pembalap Routes - PROTECTED WITH KIS.ACTIVE
        Route::get('/racers/history', [RacerHistoryController::class, 'index'])->name('racers.history.index');
        Route::get('/racers/{user}/history', [RacerHistoryController::class, 'show'])->name('racers.history.show');
    });

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
        
        // Registrasi Pembalap dengan Stored Procedure
        Route::get('/pembalap/register/create', [PembalapRegistrationController::class, 'create'])->name('pembalap.register.create');
        Route::post('/pembalap/register', [PembalapRegistrationController::class, 'store'])->name('pembalap.register.store');
        Route::put('/pembalap/{user}/update-sp', [PembalapRegistrationController::class, 'update'])->name('pembalap.update.sp');

        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::patch('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
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

    // Route Pimpinan IMI
    Route::middleware('role:pimpinan_imi')->prefix('pimpinan')->name('pimpinan.')->group(function () {
        // Route dashboard sudah ada di atas (dashboard.pimpinan)
        
        // Analytics routes - Menggunakan 8 Views
        Route::get('/analytics/event-revenue', [AnalyticsController::class, 'eventRevenueRanking'])->name('analytics.event-revenue');
        Route::get('/analytics/alerts', [AnalyticsController::class, 'operationalAlerts'])->name('analytics.alerts');
        Route::get('/analytics/revenue-ytd', [AnalyticsController::class, 'revenueBreakdownYTD'])->name('analytics.revenue-ytd');
        Route::get('/analytics/top-clubs', [AnalyticsController::class, 'topClubsPerformance'])->name('analytics.top-clubs');
        Route::get('/analytics/dashboard-widgets', [AnalyticsController::class, 'dashboardWidgets'])->name('analytics.widgets');
        
        // Export routes
        Route::get('/export/pembalap/pdf', [ExportController::class, 'pembalapPdf'])->name('export.pembalap.pdf');
        Route::get('/export/pembalap/excel', [ExportController::class, 'pembalapExcel'])->name('export.pembalap.excel');
        Route::get('/export/event/pdf', [ExportController::class, 'eventPdf'])->name('export.event.pdf');
        Route::get('/export/event/excel', [ExportController::class, 'eventExcel'])->name('export.event.excel');
        Route::get('/export/iuran/pdf', [ExportController::class, 'iuranPdf'])->name('export.iuran.pdf');
        Route::get('/export/iuran/excel', [ExportController::class, 'iuranExcel'])->name('export.iuran.excel');
    });

    // Route Super Admin
    Route::middleware('role:super_admin')->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::resource('users', SuperAdminUserController::class);
        Route::get('logs', [LogController::class, 'index'])->name('logs.index');

    });

});

// Route test PDF Iuran (HAPUS SETELAH BERHASIL)
Route::get('/test-iuran-pdf', function() {
    $year = now()->year;
    $iuran = \App\Models\ClubDues::where('payment_year', $year)
        ->with('club')
        ->orderBy('payment_date', 'desc')
        ->get();
    
    return view('exports.iuran-pdf', [
        'iuran' => $iuran,
        'year' => $year
    ]);
});

require __DIR__ . '/auth.php';
