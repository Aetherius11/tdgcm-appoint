<?php

namespace App\Http\Controllers;

use App\Models\Appointment; // Add this line!
use Illuminate\Http\Request;
use Carbon\Carbon;

class WindowStaffController extends Controller
{
    public function index()
    {
        // Your code will work now
        $appointments = Appointment::whereDate('appointment_date', now()->toDateString())
            ->whereIn('status', ['pending', 'approved'])
            ->with(['applicant', 'purpose'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('WindowStaff', compact('appointments'));
    }
    public function notify($id)
{
    $appointment = Appointment::findOrFail($id);

    // Update status to 'calling' and record the time
    $appointment->update([
        'status' => 'calling',
        'called_at' => Carbon::now(),
        'counter_number' => 1 // Assuming this is Window 1
    ]);

    return back()->with('success', 'Applicant has been called to Window 1.');
}

public function startService($id)
{
    $appointment = Appointment::findOrFail($id);

    // Update status to 'serving'
    $appointment->update([
        'status' => 'serving',
        // If you want to use checked_in_at as the start time:
        'checked_in_at' => Carbon::now()
    ]);

    return back()->with('success', 'Service started.');
}

public function endService($id)
{
    $appointment = Appointment::findOrFail($id);

    // Finalize the ticket
    $appointment->update(['status' => 'completed']);

    return back()->with('success', 'Service finished. Next applicant, please!');
}
}
