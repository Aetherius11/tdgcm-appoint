<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentConfirmation;
use App\Models\Applicant;
use App\Models\Appointment;
use App\Models\Principal;
use App\Models\Purpose;
use App\Models\TimeAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    /**
     * Display the appointment booking form.
     */
    public function create()
    {
        $principals = Principal::with('department')->get();
        $purposes = Purpose::all();
        // Get unique time slots (group by start_time and end_time to avoid duplicates)
        $timeSlots = TimeAvailability::selectRaw('MIN(id) as id, start_time, end_time')
            ->groupBy('start_time', 'end_time')
            ->get();

        // Get current Philippine time (UTC+8)
        $philippineTime = now()->setTimezone('Asia/Manila');
        $currentTime = $philippineTime->format('H:i');
        $currentDate = $philippineTime->format('Y-m-d');

        return view('appointment', compact('principals', 'purposes', 'timeSlots', 'currentTime', 'currentDate'));
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

        // Send confirmation email
        try {
            Mail::to($applicant->email)->send(new AppointmentConfirmation($appointment));
        } catch (\Exception $e) {
            // Log the error but don't fail the appointment creation
            \Log::error('Failed to send appointment confirmation email', [
                'appointment_id' => $appointment->id,
                'email' => $applicant->email,
                'error' => $e->getMessage(),
            ]);
        }

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
    public function index(Request $request)
    {
        $query = Appointment::with(['applicant', 'principal', 'purpose', 'timeSlot']);

        // Apply filters
        if ($request->filled('filter_date')) {
            $query->whereDate('appointment_date', $request->filter_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('applicant', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('principal', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('appoint_no', 'like', "%{$search}%");
        }

        if ($request->filter_type === 'principal' && $request->filled('principal_id')) {
            $query->where('principal_id', $request->principal_id);
        }

        if ($request->filter_type === 'purpose' && $request->filled('purpose_id')) {
            $query->where('purpose_id', $request->purpose_id);
        }

        $appointments = $query->latest()->paginate(15)->withQueryString();

        return view('appointments-table', compact('appointments'));
    }

    /**
     * Display the QR scanner page for guards.
     */
    public function showScanner()
    {
        return view('qr-scanner');
    }

    /**
     * Process QR code scan and validate appointment.
     */
    public function processQRScan(Request $request)
    {
        $validated = $request->validate([
            'qr_data' => 'required|string',
            'guard_override' => 'nullable|boolean',
        ]);

        try {
            // Decode the QR data (it's a JSON string)
            $qrData = json_decode($validated['qr_data'], true);
            
            // Log for debugging
            \Log::info('QR Scan received:', ['raw_data' => $validated['qr_data'], 'decoded' => $qrData]);

            // If qr_data is already an appointment number (not JSON), handle it
            if (is_string($qrData) && preg_match('/^A-\d+$/i', $qrData)) {
                $appointmentNo = $qrData;
            } 
            // If qr_data is JSON with appointment_no
            elseif (is_array($qrData) && isset($qrData['appointment_no'])) {
                $appointmentNo = $qrData['appointment_no'];
            }
            // If the entire request is just an appointment number
            elseif (preg_match('/^A-\d+$/i', $validated['qr_data'])) {
                $appointmentNo = $validated['qr_data'];
            }
            else {
                \Log::warning('Invalid QR data format', ['qr_data' => $validated['qr_data']]);
                return response()->json([
                    'success' => false,
                    'status' => 'not_found',
                    'message' => 'Appointment not found. Invalid QR code format.',
                    'debug' => [
                        'received' => $validated['qr_data'],
                        'decoded' => $qrData
                    ]
                ]);
            }

            \Log::info('Looking for appointment:', ['appointment_no' => $appointmentNo]);

            // Find the appointment by appointment number
            $appointment = Appointment::with(['applicant', 'principal', 'purpose', 'timeSlot'])
                ->where('appoint_no', $appointmentNo)
                ->first();

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'status' => 'not_found',
                    'message' => 'Appointment not found. Invalid QR code.',
                ]);
            }

            // Check if already checked in
            if ($appointment->checked_in_at) {
                // Build transaction history with durations
                $transactionHistory = [];
                
                if ($appointment->called_at) {
                    $calledAt = $appointment->called_at;
                    $servedAt = $appointment->served_at;
                    $completedAt = $appointment->transaction_completed_at;
                    
                    // Calculate waiting time (called to served)
                    $waitingDuration = $servedAt ? $calledAt->diff($servedAt)->format('%h:%i') : null;
                    
                    // Calculate service duration (served to completed)
                    $serviceDuration = null;
                    if ($servedAt && $completedAt) {
                        $serviceDuration = $servedAt->diff($completedAt)->format('%h:%i');
                    } elseif ($servedAt && $appointment->status === 'serving') {
                        $now = \Carbon\Carbon::now();
                        $serviceDuration = $servedAt->diff($now)->format('%h:%i') . ' (ongoing)';
                    }
                    
                    // Calculate total duration at window
                    $totalDuration = null;
                    if ($completedAt) {
                        $totalDuration = $calledAt->diff($completedAt)->format('%h:%i');
                    } elseif ($appointment->status !== 'completed') {
                        $now = \Carbon\Carbon::now();
                        $totalDuration = $calledAt->diff($now)->format('%h:%i') . ' (ongoing)';
                    }
                    
                    $transactionHistory = [
                        'window_number' => $appointment->window_number,
                        'counter_number' => $appointment->counter_number,
                        'status' => ucfirst($appointment->status),
                        'called_at' => $calledAt->format('h:i A'),
                        'served_at' => $servedAt ? $servedAt->format('h:i A') : null,
                        'completed_at' => $completedAt ? $completedAt->format('h:i A') : null,
                        'waiting_time' => $waitingDuration,
                        'service_duration' => $serviceDuration,
                        'total_duration' => $totalDuration,
                    ];
                }
                
                return response()->json([
                    'success' => false,
                    'status' => 'already_checked_in',
                    'message' => 'This appointment has already been checked in.',
                    'appointment' => [
                        'appoint_no' => $appointment->appoint_no,
                        'name' => $appointment->applicant->first_name . ' ' . $appointment->applicant->last_name,
                        'principal' => $appointment->principal->name ?? 'N/A',
                        'purpose' => $appointment->purpose->purpose_name ?? 'N/A',
                        'date' => \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y'),
                        'time_slot' => $appointment->timeSlot ? $appointment->timeSlot->start_time . ' - ' . $appointment->timeSlot->end_time : 'N/A',
                        'checked_in_at' => $appointment->checked_in_at->format('h:i A'),
                        'transaction_history' => $transactionHistory,
                    ],
                ]);
            }

            // Current Philippine time
            $now = \Carbon\Carbon::now()->setTimezone('Asia/Manila');

            $appointmentDate = \Carbon\Carbon::parse($appointment->appointment_date)->startOfDay();
            $today = $now->copy()->startOfDay();
            $guardOverride = $request->boolean('guard_override');

            // Block check-in on the wrong date unless guard overrides
            if (!$appointmentDate->equalTo($today)) {
                if (!$guardOverride) {
                    $isEarly = $appointmentDate->greaterThan($today);
                    $daysAway = (int) abs($appointmentDate->diffInDays($today));
                    return response()->json([
                        'success' => false,
                        'status' => 'wrong_date',
                        'is_early' => $isEarly,
                        'message' => $isEarly
                            ? "This appointment is for {$appointmentDate->format('M d, Y')} — {$daysAway} day(s) from now. Check-in is not allowed yet."
                            : "This appointment was for {$appointmentDate->format('M d, Y')}. Check-in can only be done on the appointment date.",
                        'appointment' => [
                            'appoint_no' => $appointment->appoint_no,
                            'name' => $appointment->applicant->first_name . ' ' . $appointment->applicant->last_name,
                            'principal' => $appointment->principal->name ?? 'N/A',
                            'purpose' => $appointment->purpose->purpose_name ?? 'N/A',
                            'date' => $appointmentDate->format('M d, Y'),
                            'time_slot' => $appointment->timeSlot ? $appointment->timeSlot->start_time . ' - ' . $appointment->timeSlot->end_time : 'N/A',
                        ],
                    ]);
                }

                // Guard override: check in regardless of date
                $appointment->update([
                    'status' => 'approved',
                    'checked_in_at' => $now,
                    'remarks' => 'Guard override: checked in on ' . $now->format('M d, Y') . ' (scheduled: ' . $appointmentDate->format('M d, Y') . ')',
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 'checked_in',
                    'message' => 'Successfully checked in! (Guard override applied)',
                    'appointment' => [
                        'appoint_no' => $appointment->appoint_no,
                        'name' => $appointment->applicant->first_name . ' ' . $appointment->applicant->last_name,
                        'principal' => $appointment->principal->name ?? 'N/A',
                        'purpose' => $appointment->purpose->purpose_name ?? 'N/A',
                        'date' => $appointmentDate->format('M d, Y'),
                        'time_slot' => $appointment->timeSlot ? $appointment->timeSlot->start_time . ' - ' . $appointment->timeSlot->end_time : 'N/A',
                        'checked_in_at' => $now->format('h:i A'),
                    ],
                ]);
            }

            // Get the appointment end time (today's appointments only)
            $appointmentDateTime = \Carbon\Carbon::parse($appointment->appointment_date);
            $endTime = null;

            if ($appointment->timeSlot) {
                $endHourMinute = $appointment->timeSlot->end_time; // e.g., "11:00"
                list($hour, $minute) = explode(':', $endHourMinute);
                $appointmentDateTime = $appointmentDateTime->setTime((int)$hour, (int)$minute);
                $endTime = $appointmentDateTime;
            }

            // Check if appointment is expired (more than 1 hour past end time)
            if ($endTime && $now->diffInMinutes($endTime, false) < -60) {
                if (!$guardOverride) {
                    return response()->json([
                        'success' => false,
                        'status' => 'expired',
                        'message' => 'Appointment expired. This appointment was scheduled for ' . $endTime->format('h:i A') . ' and it\'s now past the 1-hour grace period.',
                        'appointment' => [
                            'appoint_no' => $appointment->appoint_no,
                            'name' => $appointment->applicant->first_name . ' ' . $appointment->applicant->last_name,
                            'principal' => $appointment->principal->name ?? 'N/A',
                            'purpose' => $appointment->purpose->purpose_name ?? 'N/A',
                            'date' => \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y'),
                            'time_slot' => $appointment->timeSlot ? $appointment->timeSlot->start_time . ' - ' . $appointment->timeSlot->end_time : 'N/A',
                            'scheduled_end' => $endTime->format('h:i A'),
                        ],
                    ]);
                }

                // Guard override: allow late check-in
                $appointment->update([
                    'status'       => 'approved',
                    'checked_in_at' => $now,
                    'remarks'      => 'Guard override: checked in late at ' . $now->format('h:i A') . ' (scheduled end: ' . $endTime->format('h:i A') . ')',
                ]);

                return response()->json([
                    'success' => true,
                    'status'  => 'checked_in',
                    'message' => 'Successfully checked in! (Guard override — late check-in allowed)',
                    'appointment' => [
                        'appoint_no'   => $appointment->appoint_no,
                        'name'         => $appointment->applicant->first_name . ' ' . $appointment->applicant->last_name,
                        'principal'    => $appointment->principal->name ?? 'N/A',
                        'purpose'      => $appointment->purpose->purpose_name ?? 'N/A',
                        'date'         => \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y'),
                        'time_slot'    => $appointment->timeSlot ? $appointment->timeSlot->start_time . ' - ' . $appointment->timeSlot->end_time : 'N/A',
                        'checked_in_at' => $now->format('h:i A'),
                    ],
                ]);
            }

            // Valid appointment - check in
            $appointment->update([
                'status' => 'approved',
                'checked_in_at' => $now,
            ]);

            return response()->json([
                'success' => true,
                'status' => 'checked_in',
                'message' => 'Successfully checked in!',
                'appointment' => [
                    'appoint_no' => $appointment->appoint_no,
                    'name' => $appointment->applicant->first_name . ' ' . $appointment->applicant->last_name,
                    'principal' => $appointment->principal->name ?? 'N/A',
                    'purpose' => $appointment->purpose->purpose_name ?? 'N/A',
                    'date' => \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y'),
                    'time_slot' => $appointment->timeSlot ? $appointment->timeSlot->start_time . ' - ' . $appointment->timeSlot->end_time : 'N/A',
                    'checked_in_at' => $now->format('h:i A'),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'An error occurred while processing the QR code: ' . $e->getMessage(),
            ], 500);
        }
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