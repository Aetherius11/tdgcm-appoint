<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function store(\Illuminate\Http\Request $request)
    {
        // 1. Find or Create the Applicant based on email
        $applicant = \App\Models\Applicant::firstOrCreate(
            ['email' => $request->email],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->contact_number,
            ]
        );

        // 2. Create the Appointment record
        // 2. Create the Appointment record
        \App\Models\Appointment::create([
            'appoint_no'       => str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
            'applicant_id'     => $applicant->id,
            'principal_id'     => $request->principal_id,
            'purpose_id'       => $request->purpose_id,
            'appointment_date' => $request->appointment_date, // This must match the HTML 'name'
            'time_slot_id'     => $request->appointment_time,
            'status'           => 'pending',
        ]);

        // 3. Redirect back to the table view you built
        return redirect('/appointments-list')->with('success', 'Appointment successfully secured!');
    }
}