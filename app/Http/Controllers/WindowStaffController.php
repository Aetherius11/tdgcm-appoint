<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class WindowStaffController extends Controller
{
    /**
     * Display the window staff interface
     */
    public function index()
    {
        // Get the assigned window number from session (default to 1)
        $windowNumber = session('staff_window_number', 1);
        
        // Get today's appointments that are pending or approved and NOT assigned to any window (waiting in queue)
        $pendingAppointments = Appointment::whereDate('appointment_date', now()->toDateString())
            ->whereIn('status', ['pending', 'approved'])
            ->whereNull('window_number')
            ->with(['applicant', 'purpose', 'timeSlot'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Get the current appointment being served at the assigned window
        // NOTE: Do NOT include 'on_hold' - held tickets are in the queue, not the current appointment
        $currentAppointment = Appointment::whereDate('appointment_date', now()->toDateString())
            ->where('window_number', $windowNumber)
            ->whereIn('status', ['calling', 'serving'])
            ->with(['applicant', 'purpose', 'timeSlot'])
            ->latest('updated_at')
            ->first();

        // Get next appointments in queue (includes transferred appointments and held tickets)
        // Include appointments regardless of appointment_date if they have been override checked-in
        $nextQueue = Appointment::whereIn('status', ['pending', 'approved', 'on_hold'])
            ->whereNotNull('checked_in_at') // Only show appointments that have checked in with guard
            ->where(function($q) {
                // Today's appointments OR override check-ins (different date with guard override remarks)
                $q->whereDate('appointment_date', now()->toDateString())
                   ->orWhere(function($q2) {
                       $q2->whereNotNull('remarks')
                          ->where('remarks', 'like', 'Guard override:%');
                   });
            })
            ->where(function($q) use ($windowNumber) {
                $q->whereNull('window_number')  // General queue
                  ->orWhere('window_number', $windowNumber);  // Transferred or held tickets for this window
            })
            ->with(['applicant', 'purpose', 'timeSlot'])
            ->orderByRaw('CASE WHEN window_number = ? THEN 0 ELSE 1 END', [$windowNumber])  // Transferred/Held first
            ->orderBy('checked_in_at', 'asc')  // Then by check-in time
            ->take(5)
            ->get();

        // Get today's completed transactions for this window (for summary box)
        $todayCompleted = Appointment::where('window_number', $windowNumber)
            ->where('status', 'completed')
            ->whereDate('transaction_completed_at', now()->toDateString())
            ->orderBy('transaction_completed_at', 'desc')
            ->take(5)
            ->get();

        // Calculate today's stats
        $todayTotal = Appointment::where('window_number', $windowNumber)
            ->where('status', 'completed')
            ->whereDate('transaction_completed_at', now()->toDateString())
            ->count();

        // Generate list of available windows (1-12)
        $availableWindows = range(1, 12);

        return view('WindowStaff', compact('pendingAppointments', 'currentAppointment', 'nextQueue', 'windowNumber', 'availableWindows', 'todayCompleted', 'todayTotal'));
    }

    /**
     * Display the transaction history for the window staff
     */
    public function transactionHistory(Request $request)
    {
        $windowNumber = session('staff_window_number', 1);
        $filterDate = $request->input('filter_date', now()->toDateString());
        $search = $request->input('search', '');

        // Get all completed appointments for this window
        $query = Appointment::where('window_number', $windowNumber)
            ->where('status', 'completed')
            ->with(['applicant', 'purpose', 'timeSlot']);

        // Apply date filter
        if ($filterDate) {
            $query->whereDate('transaction_completed_at', $filterDate);
        }

        // Apply search filter
        if ($search) {
            $query->whereHas('applicant', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->orWhere('appoint_no', 'like', "%{$search}%");
        }

        $appointments = $query->orderBy('transaction_completed_at', 'desc')->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStats($windowNumber, $filterDate);

        return view('transaction-history', compact('appointments', 'windowNumber', 'stats', 'filterDate', 'search'));
    }

    /**
     * Calculate statistics for the transaction history
     */
    private function calculateStats($windowNumber, $filterDate = null)
    {
        $query = Appointment::where('window_number', $windowNumber)
            ->where('status', 'completed');

        if ($filterDate) {
            $query->whereDate('transaction_completed_at', $filterDate);
        }

        $completed = $query->count();

        // Calculate average service time
        $appointments = $query->get();
        $totalServiceTime = 0;
        $countWithTimes = 0;

        foreach ($appointments as $apt) {
            if ($apt->served_at && $apt->transaction_completed_at) {
                $serviceTime = $apt->served_at->diffInSeconds($apt->transaction_completed_at);
                $totalServiceTime += $serviceTime;
                $countWithTimes++;
            }
        }

        $avgServiceTime = $countWithTimes > 0 ? $totalServiceTime / $countWithTimes : 0;

        // Calculate average wait time (from called_at to served_at)
        $totalWaitTime = 0;
        $countWithWait = 0;

        foreach ($appointments as $apt) {
            if ($apt->called_at && $apt->served_at) {
                $waitTime = $apt->called_at->diffInSeconds($apt->served_at);
                $totalWaitTime += $waitTime;
                $countWithWait++;
            }
        }

        $avgWaitTime = $countWithWait > 0 ? $totalWaitTime / $countWithWait : 0;

        return [
            'total_completed' => $completed,
            'avg_service_time' => $avgServiceTime,
            'avg_wait_time' => $avgWaitTime,
        ];
    }

    /**
     * Change the assigned window number for the staff member
     */
    public function changeWindow(Request $request)
    {
        $windowNumber = $request->input('window_number', 1);
        
        // Validate window number is between 1 and 12
        if ($windowNumber < 1 || $windowNumber > 12) {
            return back()->with('error', 'Invalid window number. Please select a number between 1 and 12.');
        }
        
        // Store the window number in session
        session(['staff_window_number' => $windowNumber]);
        
        return back()->with('success', "You are now assigned to Window {$windowNumber}.");
    }

    /**
     * Notify/Call the next applicant to the window
     */
    public function notify(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $windowNumber = session('staff_window_number', 1);
        
        if (!$appointmentId) {
            // Get the first pending appointment if no ID provided
            $appointment = Appointment::whereDate('appointment_date', now()->toDateString())
                ->whereIn('status', ['pending', 'approved'])
                ->whereNull('window_number')
                ->orderBy('created_at', 'asc')
                ->first();
        } else {
            $appointment = Appointment::findOrFail($appointmentId);
        }

        if (!$appointment) {
            return back()->with('error', 'No applicants in queue.');
        }

        // Update status to 'calling' and record the time
        $appointment->update([
            'status' => 'calling',
            'called_at' => Carbon::now(),
            'counter_number' => $windowNumber,
            'window_number' => $windowNumber,
        ]);

        return back()->with('success', "Applicant has been called to Window {$windowNumber}.");
    }

    /**
     * Start serving the applicant
     */
    public function startService(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $appointment = Appointment::findOrFail($appointmentId);

        // Update status to 'serving' and record the start time
        $appointment->update([
            'status' => 'serving',
            'served_at' => Carbon::now(),
            'served_by_user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Service started.');
    }

    /**
     * End service for the current applicant
     */
    public function endService(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $appointment = Appointment::findOrFail($appointmentId);

        // Finalize the ticket with transaction completion timestamp
        $appointment->update([
            'status' => 'completed',
            'transaction_completed_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Service finished. Next applicant, please!');
    }

    /**
     * Hold the current ticket
     */
    public function holdTicket(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $appointment = Appointment::findOrFail($appointmentId);

        // Update status to 'on_hold'
        $appointment->update([
            'status' => 'on_hold',
        ]);

        return back()->with('success', 'Ticket has been put on hold.');
    }

    /**
     * Resume a held ticket
     */
    public function resumeTicket(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $appointment = Appointment::findOrFail($appointmentId);

        // Resume to serving status
        $appointment->update([
            'status' => 'serving',
        ]);

        return back()->with('success', 'Ticket has been resumed.');
    }

    /**
     * Transfer to another window
     */
    public function transfer(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $windowNumber = $request->input('window_number', 2);
        $appointment = Appointment::findOrFail($appointmentId);

        // Transfer to another window
        $appointment->update([
            'window_number' => $windowNumber,
            'counter_number' => $windowNumber,
            'status' => 'transferred',
        ]);

        return back()->with('success', "Transferred to Window {$windowNumber}.");
    }

    /**
     * Open the counter - makes the window available for service
     */
    public function openCounter(Request $request)
    {
        // Store window status in session
        session(['window_status' => 'open', 'window_opened_at' => Carbon::now()]);
        
        return back()->with('success', 'Counter is now OPEN. You can start calling applicants.');
    }

    /**
     * Close the counter - makes the window unavailable
     */
    public function closeCounter(Request $request)
    {
        $windowNumber = session('staff_window_number', 1);
        
        // Check if there's a current appointment being served at this window
        $currentAppointment = Appointment::whereDate('appointment_date', now()->toDateString())
            ->where('window_number', $windowNumber)
            ->whereIn('status', ['calling', 'serving', 'on_hold'])
            ->first();

        if ($currentAppointment) {
            return back()->with('error', "Cannot close Window {$windowNumber} while an applicant is being served. Please end or transfer the current appointment first.");
        }

        // Store window status in session
        session(['window_status' => 'closed', 'window_closed_at' => Carbon::now()]);
        
        return back()->with('success', "Window {$windowNumber} is now CLOSED.");
    }

    /**
     * Start break - temporarily unavailable
     */
    public function startBreak(Request $request)
    {
        $windowNumber = session('staff_window_number', 1);
        
        // Check if there's a current appointment being served at this window
        $currentAppointment = Appointment::whereDate('appointment_date', now()->toDateString())
            ->where('window_number', $windowNumber)
            ->whereIn('status', ['calling', 'serving', 'on_hold'])
            ->first();

        if ($currentAppointment) {
            return back()->with('error', "Cannot go on break while an applicant is being served at Window {$windowNumber}. Please end or transfer the current appointment first.");
        }

        // Store window status in session
        session(['window_status' => 'break', 'window_break_at' => Carbon::now()]);
        
        return back()->with('success', "You are now on BREAK. Window {$windowNumber} is temporarily unavailable.");
    }

    /**
     * API: Get current window status for TV display
     */
    public function getWindowStatus(): JsonResponse
    {
        $windows = [];
        
        // Get all active windows (1-12)
        for ($i = 1; $i <= 12; $i++) {
            $appointment = Appointment::whereDate('appointment_date', now()->toDateString())
                ->where('window_number', $i)
                ->whereIn('status', ['calling', 'serving'])
                ->with(['applicant'])
                ->latest('updated_at')
                ->first();

            $windows[$i] = [
                'counter' => $i,
                'appointment' => $appointment,
                'status' => $appointment ? ucfirst($appointment->status) : 'Vacant'
            ];
        }

        // Get the most recently called appointment for the main display
        $currentCall = Appointment::whereDate('appointment_date', now()->toDateString())
            ->where('status', 'calling')
            ->with(['applicant'])
            ->latest('called_at')
            ->first();

        return response()->json([
            'windows' => $windows,
            'currentCall' => $currentCall
        ]);
    }

    /**
     * API: Get queue data for TV display
     */
    public function getQueueData(): JsonResponse
    {
        // Get today's appointments with assigned windows
        $appointments = Appointment::with('applicant')
            ->whereDate('appointment_date', today())
            ->whereIn('status', ['calling', 'serving', 'completed'])
            ->whereNotNull('window_number')
            ->orderBy('window_number')
            ->get();

        $counters = [];
        for ($i = 1; $i <= 12; $i++) {
            $counters[$i] = [
                'counter' => $i,
                'appointment' => null,
                'status' => 'Vacant'
            ];
        }

        foreach ($appointments as $appointment) {
            if (isset($counters[$appointment->window_number])) {
                $counters[$appointment->window_number] = [
                    'counter' => $appointment->window_number,
                    'appointment' => $appointment,
                    'status' => ucfirst($appointment->status)
                ];
            }
        }

        // Get the most recently called appointment for the large display
        $currentCall = Appointment::with('applicant')
            ->whereDate('appointment_date', today())
            ->where('status', 'calling')
            ->whereNotNull('called_at')
            ->orderBy('called_at', 'desc')
            ->first();

        return response()->json([
            'counters' => $counters,
            'currentCall' => $currentCall
        ]);
    }
}