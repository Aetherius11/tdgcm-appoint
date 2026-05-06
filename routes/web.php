<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

// Public appointment routes
Route::get('/', [AppointmentController::class, 'create'])->name('appointment.create');
Route::post('/submit-appointment', [AppointmentController::class, 'store'])->name('appointment.store');
Route::get('/appointment/{appointment}', [AppointmentController::class, 'show'])->name('appointment.show');
Route::get('/api/time-slots', [AppointmentController::class, 'getAvailableTimeSlots'])->name('api.time-slots');

// Public Queue Display
Route::get('/queue-display', function () {
    return view('livewire.queue-display');
})->name('queue.display');

// Admin appointments list (protected)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/appointments-list', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/qr-scanner', [AppointmentController::class, 'showScanner'])->name('qr.scanner');
    Route::post('/qr-scan', [AppointmentController::class, 'processQRScan'])->name('qr.scan');

    Route::get('/dashboard', [AppointmentController::class, 'index'])->name('dashboard');
});

Route::get('/tv-display', [App\Http\Controllers\TVDisplayController::class, 'index'])->name('tv.display');
 Route::get('/WindowStaff', [App\Http\Controllers\WindowStaffController::class, 'index'])->name('Window.Staff');

 use App\Http\Controllers\WindowStaffController;

// Routes for the Window Staff actions
Route::post('/window/notify/{id}', [WindowStaffController::class, 'notify'])->name('window.notify');
Route::post('/window/start/{id}', [WindowStaffController::class, 'startService'])->name('window.start');
Route::post('/window/end/{id}', [WindowStaffController::class, 'endService'])->name('window.end');

