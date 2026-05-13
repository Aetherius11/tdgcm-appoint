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

// TV Display routes
Route::get('/tv-display', [App\Http\Controllers\TVDisplayController::class, 'index'])->name('tv.display');

// Window Staff routes
Route::get('/WindowStaff', [App\Http\Controllers\WindowStaffController::class, 'index'])->name('Window.Staff');
Route::get('/WindowStaff/transaction-history', [App\Http\Controllers\WindowStaffController::class, 'transactionHistory'])->name('window.transaction-history');

use App\Http\Controllers\WindowStaffController;

// Routes for the Window Staff actions (form-based)
Route::post('/window/notify', [WindowStaffController::class, 'notify'])->name('window.notify');
Route::post('/window/start', [WindowStaffController::class, 'startService'])->name('window.start');
Route::post('/window/end', [WindowStaffController::class, 'endService'])->name('window.end');
Route::post('/window/hold', [WindowStaffController::class, 'holdTicket'])->name('window.hold');
Route::post('/window/resume', [WindowStaffController::class, 'resumeTicket'])->name('window.resume');
Route::post('/window/transfer', [WindowStaffController::class, 'transfer'])->name('window.transfer');
Route::post('/window/open-counter', [WindowStaffController::class, 'openCounter'])->name('window.open-counter');
Route::post('/window/close-counter', [WindowStaffController::class, 'closeCounter'])->name('window.close-counter');
Route::post('/window/break', [WindowStaffController::class, 'startBreak'])->name('window.break');
Route::post('/window/change-window', [WindowStaffController::class, 'changeWindow'])->name('window.change-window');

// API routes for real-time data (used by TV display)
Route::get('/api/window-status', [WindowStaffController::class, 'getWindowStatus'])->name('api.window-status');
Route::get('/api/queue-data', [WindowStaffController::class, 'getQueueData'])->name('api.queue-data');

