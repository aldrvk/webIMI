<?php

use App\Http\Controllers\Admin\KisApprovalController;
use App\Http\Controllers\KisApplicationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
    });
});

require __DIR__ . '/auth.php';
