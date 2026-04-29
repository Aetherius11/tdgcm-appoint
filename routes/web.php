<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ApplicantAppointmentController;
use App\Http\Controllers\AppointmentController;
use App\Models\Principal;
use App\Models\Purpose;


// 1. This SHOWS the TCM EasyQue page
// Make sure your file is named 'appointment.blade.php' in resources/views
Route::get('/', function () {
    return view('appointment');
});

// 2. This HANDLES the form submission
Route::post('/submit-appointment', function (Request $request) {
    return $request->all();
})->name('appointment.store');

// 3. Protected Dashboard Routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

}); // <--- This was the missing closing bracket and parenthesis!

// The Form Page
Route::get('/appointment/create', function () {
    return view('appointment', [
        'principals' => \App\Models\Principal::whereNull('parent_id')->get(),
        'purposes' => \App\Models\Purpose::all()
    ]);
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
