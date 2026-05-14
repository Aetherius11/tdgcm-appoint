<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Window;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WindowStaffController extends Controller
{
    /**
     * Display the window staff interface
     */
    public function index()
    {
        $user = auth()->user();
        
        // app/Http/Controllers/WindowStaffController.php

        // 1. DYNAMIC: Get windows linked to the user's Department via the conjunction table
        $availableWindows = Window::whereHas('principals', function($q) use ($user) {
            $q->where('principals.id', $user->current_team_id);
        })
        ->orderBy('id', 'asc')
        ->get();

        // 2. Default to the first available window for this department if none in session
        $windowId = session('staff_window_id', $availableWindows->first()?->id);
        $currentWindow = Window::find($windowId);
        
        // 3. DYNAMIC: Get Purposes linked to this specific Office Department
        // This ensures staff only see applicants for tasks their office handles
        $departmentPurposes = Purpose::whereHas('principals', function($q) use ($user) {
            $q->where('principals.id', $user->current_team_id);
        })->pluck('id');

        // 4. Filter Current Appointment for this specific window
        $currentAppointment = Appointment::where('window_number', $windowId)
            ->whereIn('status', ['calling', 'serving'])
            ->where(function($q) {
                $q->whereDate('appointment_date', today())
                  ->orWhere(function($q2) {
                      $q2->whereNotNull('remarks')->where('remarks', 'like', 'Guard override:%');
                  });
            })
            ->with(['applicant', 'purpose', 'timeSlot'])
            ->latest('updated_at')
            ->first();

        // 5. DYNAMIC: Filter Next Queue by Department's Purposes
        $nextQueue = Appointment::whereIn('status', ['pending', 'approved', 'on_hold'])
            ->whereIn('purpose_id', $departmentPurposes)
            ->whereNotNull('checked_in_at')
            ->where(function($q) use ($windowId) {
                $q->whereNull('window_number')  
                  ->orWhere('window_number', $windowId); 
            })
            ->whereDate('appointment_date', today())
            ->with(['applicant', 'purpose', 'timeSlot'])
            ->orderByRaw('CASE WHEN window_number = ? THEN 0 ELSE 1 END', [$windowId])
            ->orderBy('checked_in_at', 'asc')
            ->take(5)
            ->get();

        // Stats and Completed filtered by the dynamic windowId
        $todayCompleted = Appointment::where('window_number', $windowId)
            ->where('status', 'completed')
            ->whereDate('transaction_completed_at', today())
            ->orderBy('transaction_completed_at', 'desc')
            ->take(5)
            ->get();

        $todayTotal = Appointment::where('window_number', $windowId)
            ->where('status', 'completed')
            ->whereDate('transaction_completed_at', today())
            ->count();

        $availableWindows = $this->getWindowsForUser();

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
     * Change the assigned window based on the dynamic database ID
     */
    public function changeWindow(Request $request)
    {
        $windowId = $request->input('window_number');
        $window   = Window::find($windowId);

        if (!$window) {
            return back()->with('error', 'Invalid window selection.');
        }

        $allowed = $this->getWindowsForUser()->contains('id', $window->id);
        if (!$allowed) {
            return back()->with('error', 'Window ' . $window->name . ' is not assigned to your department.');
        }

        session(['staff_window_number' => $window->id]);

        return back()->with('success', "You are now assigned to Window {$window->name}.");
    }

    private function getWindowsForUser()
    {
        $principal = auth()->user()?->team;

        if ($principal && $principal->windows()->exists()) {
            return $principal->windows()->orderBy('windows.id')->get();
        }

        return Window::orderBy('id')->get();
    }

    /**
     * Notify/Call the next applicant
     */
    public function notify(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $windowId = session('staff_window_id');
        $window = Window::find($windowId);
        
        $appointment = Appointment::findOrFail($appointmentId);

        $appointment->update([
            'status' => 'calling',
            'called_at' => Carbon::now(),
            'window_number' => $windowId,
            'counter_number' => $window->name, // Saves the dynamic name for TV display
        ]);

        return back()->with('success', "Applicant has been called to {$window->name}.");
    }

    public function startService(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $appointment = Appointment::findOrFail($appointmentId);

        $appointment->update([
            'status' => 'serving',
            'served_at' => Carbon::now(),
            'served_by_user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Service started.');
    }

    public function endService(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $appointment = Appointment::findOrFail($appointmentId);

        $appointment->update([
            'status' => 'completed',
            'transaction_completed_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Service finished.');
    }

    public function holdTicket(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $appointment = Appointment::findOrFail($appointmentId);

        $appointment->update(['status' => 'on_hold']);
        return back()->with('success', 'Ticket put on hold.');
    }

    public function resumeTicket(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $appointment = Appointment::findOrFail($appointmentId);

        $appointment->update(['status' => 'serving']);
        return back()->with('success', 'Ticket resumed.');
    }

    public function transfer(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $targetWindowId = $request->input('window_number'); // Renamed internally for clarity
        $appointment = Appointment::findOrFail($appointmentId);

        $appointment->update([
            'window_number' => $targetWindowId,
            'status' => 'pending',
        ]);

        return back()->with('success', "Transferred successfully.");
    }

    public function openCounter(Request $request)
    {
        $windowId = session('staff_window_id');
        Cache::put("window_status_{$windowId}", 'open', now()->addHours(24));
        session(['window_status' => 'open']);

        return back()->with('success', 'Counter is now OPEN.');
    }

    public function closeCounter(Request $request)
    {
        $windowId = session('staff_window_id');
        Cache::put("window_status_{$windowId}", 'closed', now()->addHours(24));
        session(['window_status' => 'closed']);

        return back()->with('success', 'Counter is now CLOSED.');
    }

    public function startBreak(Request $request)
    {
        $windowId = session('staff_window_id');
        Cache::put("window_status_{$windowId}", 'break', now()->addHours(24));
        session(['window_status' => 'break']);

        return back()->with('success', 'You are now on BREAK.');
    }

    /**
     * API: Get window data for TV display (Dynamic Source)
     */
    public function getQueueData(): JsonResponse
    {
        $windows = Window::all();
        $counters = [];

        foreach ($windows as $window) {
            $appointment = Appointment::with('applicant')
                ->where('window_number', $window->id)
                ->whereIn('status', ['calling', 'serving'])
                ->whereDate('appointment_date', today())
                ->first();

            $counters[$window->id] = [
                'counter' => $window->name,
                'appointment' => $appointment,
                'status' => $appointment ? ucfirst($appointment->status) : 'Vacant'
            ];
        }

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