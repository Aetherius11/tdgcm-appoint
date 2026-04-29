<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApplicantAppointmentController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Appointment::with(['applicant', 'principal', 'purpose']);

        // 1. New: Filter by Specific Date
        if ($request->filled('filter_date')) {
            $query->whereDate('appointment_date', $request->get('filter_date'));
        }

        // 1. Search Logic
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('appoint_id', 'like', "%{$search}%")
                ->orWhereHas('applicant', function($sq) use ($search) {
                    $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        // 2. Conditional Filters based on Type
        $filterType = $request->get('filter_type');

        if ($filterType === 'principal' && $request->filled('principal_id')) {
            $query->where('principal_id', $request->get('principal_id'));
        } 
        elseif ($filterType === 'purpose' && $request->filled('purpose_id')) {
            $query->where('purpose_id', $request->get('purpose_id'));
        }

        $appointments = $query->latest()->paginate(10)->withQueryString();

        return view('appointments-table', compact('appointments'));
    }
}
