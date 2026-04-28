<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Appointment;
use App\Models\Principal;
use App\Models\Purpose;
use App\Models\TimeAvailability;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display the appointment booking form.
     */
    public function create()
    {
        $principals = Principal::with('department')->get();
        $purposes = Purpose::all();
        $timeSlots = TimeAvailability::all();

        return view('appointment', compact('principals', 'purposes', 'timeSlots'));
    }

    /**
     * Get available time slots for a specific purpose and date.
     */
    public function getAvailableTimeSlots(Request $request)
    {
        $validated = $request->validate([
            'purpose_id' => 'required|exists:purposes,id',
            'appointment_date' => 'required|date',
        ]);

        $date = $validated['appointment_date'];
        $dayOfWeek = date('N', strtotime($date)); // 1 (Mon) to 7 (Sun)

        $timeSlots = TimeAvailability::where('purpose_id', $validated['purpose_id'])
            ->where('day_of_week', $dayOfWeek)
            ->get();

        return response()->json($timeSlots);
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'principal_id' => 'required|exists:principals,id',
            'purpose_id' => 'required|exists:purposes,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'time_slot_id' => 'required|exists:time_availabilities,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:50',
            'consent' => 'accepted',
        ]);

        // Create or find the applicant
        $applicant = Applicant::firstOrCreate(
            ['email' => $validated['email']],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
            ]
        );

        // Update applicant details if they already existed
        if ($applicant->wasRecentlyCreated === false) {
            $applicant->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
            ]);
        }

        // Create the appointment
        $appointment = Appointment::create([
            'applicant_id' => $applicant->id,
            'principal_id' => $validated['principal_id'],
            'purpose_id' => $validated['purpose_id'],
            'appointment_date' => $validated['appointment_date'],
            'time_slot_id' => $validated['time_slot_id'],
            'status' => 'pending',
        ]);

        // Prepare QR code data
        $qrData = json_encode([
            'appointment_no' => $appointment->appoint_no,
            'applicant' => $appointment->applicant->first_name.' '.$appointment->applicant->last_name,
            'principal' => $appointment->principal->name ?? '',
            'purpose' => $appointment->purpose->purpose_name ?? '',
            'date' => $appointment->appointment_date,
            'time_slot' => $appointment->timeSlot ? $appointment->timeSlot->start_time.' - '.$appointment->timeSlot->end_time : '',
        ]);

        // Return JSON for AJAX requests, otherwise return the view
        if (request()->expectsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'appointment' => [
                    'appoint_no' => $appointment->appoint_no,
                    'principal' => $appointment->principal ? ['name' => $appointment->principal->name] : null,
                    'purpose' => $appointment->purpose ? ['purpose_name' => $appointment->purpose->purpose_name] : null,
                    'appointment_date' => $appointment->appointment_date,
                    'time_slot' => $appointment->timeSlot ? $appointment->timeSlot->start_time.' - '.$appointment->timeSlot->end_time : null,
                ],
                'qr_data' => $qrData,
            ]);
        }

        // Return the success view with appointment details and QR code data
        return view('appointment-success', compact('appointment', 'qrData'));
    }

    /**
     * Display the list of appointments (for admin).
     */
    public function index()
    {
        $appointments = Appointment::with(['applicant', 'principal', 'purpose', 'timeSlot'])->latest()->paginate(15);

        return view('appointments.index', compact('appointments'));
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['applicant', 'principal', 'purpose', 'timeSlot']);

        // Prepare QR code data
        $qrData = json_encode([
            'appointment_no' => $appointment->appoint_no,
            'applicant' => $appointment->applicant->first_name.' '.$appointment->applicant->last_name,
            'principal' => $appointment->principal->name ?? '',
            'purpose' => $appointment->purpose->purpose_name ?? '',
            'date' => $appointment->appointment_date,
            'time_slot' => $appointment->timeSlot ? $appointment->timeSlot->start_time.' - '.$appointment->timeSlot->end_time : '',
        ]);

        return view('appointment-success', compact('appointment', 'qrData'));
    }
}