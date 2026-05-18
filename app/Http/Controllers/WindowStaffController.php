<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Window;
use App\Models\Purpose;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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
        $windowName = $availableWindows->firstWhere('id', $windowNumber)?->name ?? $windowNumber;

        return view('WindowStaff', compact('pendingAppointments', 'currentAppointment', 'nextQueue', 'windowNumber', 'windowName', 'availableWindows', 'todayCompleted', 'todayTotal'));
    }

    /**
     * Display the transaction history for the window staff
     */
    public function transactionHistory(Request $request)
    {
        // CHANGE THIS: Use 'staff_window_id' to match your changeWindow method
        $windowId = session('staff_window_id', 1); 
        
        $filterDate = $request->input('filter_date', now()->toDateString());
        $search = $request->input('search', '');

        $query = Appointment::where('window_number', $windowId)
            ->where('status', 'completed')
            ->with(['applicant', 'purpose', 'timeSlot']);

        // ... rest of the filtering logic ...

        $appointments = $query->orderBy('transaction_completed_at', 'desc')->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStats($windowId, $filterDate);

        // Ensure the view name matches your blade file (appointments-table)
        return view('appointments-table', compact('appointments', 'windowId', 'stats', 'filterDate', 'search'));
    }

    /**
     * Calculate statistics for the transaction history
     */
    private function calculateStats($windowId, $filterDate = null)
    {
        $query = Appointment::where('window_number', $windowId)
        ->where('status', 'completed');

    if ($filterDate) {
        $query->whereDate('transaction_completed_at', $filterDate);
    }

    $appointments = $query->get();
    $totalServiceTime = 0;
    $countWithTimes = 0;

    foreach ($appointments as $apt) {
        // Ensure we use 'served_at' (start) and 'transaction_completed_at' (end)
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
        // Ensure the name here matches the 'name' attribute in your Blade select tag
        $windowId = $request->input('window_id'); 
        
        if (!$windowId) {
            return back()->with('error', 'Please select a valid window.');
        }

        // Save to session using the same key used in index() and notify()
        session(['staff_window_id' => $windowId]); 
        
        return back()->with('success', "Assigned to window successfully.");
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
     * Notify/Call the next applicant to the window.
     *
     * Wrapped in a transaction with lockForUpdate() so that if two staff
     * click "Call" at the same instant, the second request blocks until the
     * first commits, then finds the appointment already in 'calling' status
     * and returns gracefully instead of double-assigning.
     */
    public function notify(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $windowNumber  = session('staff_window_number', 1);

        $windowStatus = \Cache::get("window_status_{$windowNumber}", 'closed');
        if ($windowStatus !== 'open') {
            return back()->with('error', 'Please open your counter before calling applicants.');
        }

        [$appointment, $errorKey] = DB::transaction(function () use ($appointmentId, $windowNumber) {

            if (!$appointmentId) {
                $appointment = Appointment::whereDate('appointment_date', now()->toDateString())
                    ->whereIn('status', ['pending', 'approved'])
                    ->whereNull('window_number')
                    ->whereNotNull('checked_in_at')
                    ->orderBy('checked_in_at', 'asc')
                    ->lockForUpdate()
                    ->first();

                if (!$appointment) {
                    return [null, 'no_queue'];
                }
            } else {
                $appointment = Appointment::where('id', $appointmentId)
                    ->where(function ($q) use ($windowNumber) {
                        $q->where(function ($inner) use ($windowNumber) {
                            $inner->whereIn('status', ['pending', 'approved'])
                                  ->where(function ($w) use ($windowNumber) {
                                      $w->whereNull('window_number')
                                        ->orWhere('window_number', $windowNumber);
                                  });
                        })
                        ->orWhere(function ($inner) use ($windowNumber) {
                            $inner->where('status', 'calling')
                                  ->where('window_number', $windowNumber);
                        });
                    })
                    ->whereNotNull('checked_in_at')
                    ->where(function ($q) {
                        $q->whereDate('appointment_date', now()->toDateString())
                          ->orWhere(function ($q2) {
                              $q2->whereNotNull('remarks')
                                 ->where('remarks', 'like', 'Guard override:%');
                          });
                    })
                    ->lockForUpdate()
                    ->first();

                if (!$appointment) {
                    return [null, 'not_eligible'];
                }
            }

            $appointment->update([
                'status'         => 'calling',
                'called_at'      => Carbon::now(),
                'counter_number' => $windowNumber,
                'window_number'  => $windowNumber,
            ]);

            return [$appointment, null];
        });

        if ($errorKey === 'no_queue') {
            return back()->with('error', 'No applicants in queue.');
        }

        if ($errorKey === 'not_eligible') {
            return back()->with('error', 'This applicant is not in the queue or has already been called.');
        }

        $windowName = Window::find($windowNumber)?->name ?? $windowNumber;
        return back()->with('success', "Applicant has been called to Window {$windowName}.");
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

    /**
     * Returns a lightweight snapshot of the queue relevant to this window.
     * The Window Staff page polls this every 8 s and reloads if anything changed.
     */
    public function getQueueState(): JsonResponse
    {
        $windowNumber = session('staff_window_number', 1);

        $state = Appointment::whereIn('status', ['pending', 'approved', 'on_hold', 'calling', 'serving'])
            ->where(function ($q) {
                $q->whereDate('appointment_date', now()->toDateString())
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('remarks')->where('remarks', 'like', 'Guard override:%');
                  });
            })
            ->where(function ($q) use ($windowNumber) {
                $q->whereNull('window_number')
                  ->orWhere('window_number', $windowNumber);
            })
            ->pluck('status', 'id');

        return response()->json(['state' => $state]);
    }

    /**
     * Transfer to another window
     */
    public function transfer(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $targetwindowId = $request->input('window_number'); // Renamed internally for clarity
        $appointment = Appointment::findOrFail($appointmentId);

        $appointment->update([
            'window_number' => $targetwindowId,
            'status' => 'pending',
        ]);

        $targetWindow = Window::find($windowNumber);
        return back()->with('success', "Transferred to Window " . ($targetWindow?->name ?? $windowNumber) . ".");
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
        $windowNumber = session('staff_window_number', 1);
        
        // Check if there's a current appointment being served at this window
        $currentAppointment = Appointment::whereDate('appointment_date', now()->toDateString())
            ->where('window_number', $windowNumber)
            ->whereIn('status', ['calling', 'serving', 'on_hold'])
            ->first();

        if ($currentAppointment) {
            $windowName = Window::find($windowNumber)?->name ?? $windowNumber;
            return back()->with('error', "Cannot close Window {$windowName} while an applicant is being served. Please end or transfer the current appointment first.");
        }

        session(['window_status' => 'closed', 'window_closed_at' => Carbon::now()]);
        \Cache::put("window_status_{$windowNumber}", 'closed', now()->addHours(24));

        $windowName = Window::find($windowNumber)?->name ?? $windowNumber;
        return back()->with('success', "Window {$windowName} is now CLOSED.");
    }

    public function startBreak(Request $request)
    {
        $windowId = session('staff_window_id');
        Cache::put("window_status_{$windowId}", 'break', now()->addHours(24));
        session(['window_status' => 'break']);

        if ($currentAppointment) {
            $windowName = Window::find($windowNumber)?->name ?? $windowNumber;
            return back()->with('error', "Cannot go on break while an applicant is being served at Window {$windowName}. Please end or transfer the current appointment first.");
        }

        session(['window_status' => 'break', 'window_break_at' => Carbon::now()]);
        \Cache::put("window_status_{$windowNumber}", 'break', now()->addHours(24));

        $windowName = Window::find($windowNumber)?->name ?? $windowNumber;
        return back()->with('success', "You are now on BREAK. Window {$windowName} is temporarily unavailable.");
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