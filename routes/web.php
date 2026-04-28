<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ApplicantAppointmentController;

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

// Make sure this matches the URL you are typing in the browser
Route::get('/appointments-list', [ApplicantAppointmentController::class, 'index']);
