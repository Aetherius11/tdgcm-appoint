<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApplicantAppointmentController extends Controller
{
    public function index()
    {
        $appointments = \App\Models\Appointment::with(['applicant', 'principal', 'purpose'])
            ->latest()
            ->paginate(10);

        return view('appointments-table', compact('appointments'));
    }
}
