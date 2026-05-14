<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\UserInvitationController;
use App\Http\Controllers\TVDisplayController;
use App\Http\Controllers\WindowStaffController;
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
Route::get('/tv-display', [TVDisplayController::class, 'index'])->name('tv.display');

// Window Staff routes
Route::get('/WindowStaff', [App\Http\Controllers\WindowStaffController::class, 'index'])->name('Window.Staff');



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
Route::get('/window/transaction-history', [WindowStaffController::class, 'transactionHistory'])->name('window.transaction-history');


// API routes for real-time data (used by TV display)
Route::get('/api/window-status', [WindowStaffController::class, 'getWindowStatus'])->name('api.window-status');
Route::get('/api/queue-data', [WindowStaffController::class, 'getQueueData'])->name('api.queue-data');

// User Invitation routes (public, token-based authentication)
Route::get('/invite/{token}', [UserInvitationController::class, 'show'])->name('invitations.show');
Route::post('/invite/{token}', [UserInvitationController::class, 'accept'])->name('invitations.accept');

// 2FA Setup routes (authenticated users who just accepted invitation)
Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {
    Route::get('/invite/2fa-setup', [UserInvitationController::class, 'show2FASetup'])->name('invitations.2fa-setup');
    Route::post('/invite/2fa-confirm', [UserInvitationController::class, 'confirm2FA'])->name('invitations.2fa-confirm');
});
