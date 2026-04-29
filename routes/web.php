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

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/', function () {
    // Get the top-level categories (DCL, WOW, etc.)
    $principals = Principal::whereNull('parent_id')->get();
    
    // Get all purposes for the second dropdown
    $purposes = Purpose::all();

    // The 'compact' function sends the variables to appointment.blade.php
    return view('appointment', compact('principals', 'purposes'));
});

Route::get('/tv-display', [App\Http\Controllers\TVDisplayController::class, 'index'])->name('tv.display');


// The Logic to Save
// Update this line to point to the Controller instead of the temporary function
Route::post('/submit-appointment', [AppointmentController::class, 'store'])->name('appointment.store');

// The Table List (Your previous work)
Route::get('/appointments-list', [ApplicantAppointmentController::class, 'index'])->name('appointments.list');

// Make sure this matches the URL you are typing in the browser
Route::get('/appointments-list', [ApplicantAppointmentController::class, 'index']);
