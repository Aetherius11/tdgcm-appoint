<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

// Public appointment routes
Route::get('/', [AppointmentController::class, 'create'])->name('appointment.create');
Route::post('/submit-appointment', [AppointmentController::class, 'store'])->name('appointment.store');
Route::get('/appointment/{appointment}', [AppointmentController::class, 'show'])->name('appointment.show');
Route::get('/api/time-slots', [AppointmentController::class, 'getAvailableTimeSlots'])->name('api.time-slots');

// Admin appointments list (protected)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/appointments-list', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/qr-scanner', [AppointmentController::class, 'showScanner'])->name('qr.scanner');
    Route::post('/qr-scan', [AppointmentController::class, 'processQRScan'])->name('qr.scan');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/tv-display', [App\Http\Controllers\TVDisplayController::class, 'index'])->name('tv.display');
