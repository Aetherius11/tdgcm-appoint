<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentWindowLog;
use App\Models\Window;
use App\Models\Purpose;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WindowStaffController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $availableWindows = $this->getWindowsForUser();

        $windowId = session('staff_window_id', $availableWindows->first()?->id);

        $departmentPurposes = Purpose::whereHas('principals', function($q) use ($user) {
            $q->where('principals.id', $user->current_team_id);
        })->pluck('id');

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

        $todayCompleted = AppointmentWindowLog::where('window_id', $windowId)
            ->whereNotNull('ended_at')
            ->whereDate('called_at', today())
            ->with(['appointment.applicant'])
            ->orderBy('ended_at', 'desc')
            ->take(5)
            ->get();

        $todayTotal = AppointmentWindowLog::where('window_id', $windowId)
            ->whereNotNull('ended_at')
            ->whereDate('called_at', today())
            ->count();

        $windowNumber = $windowId;
        $windowName = $availableWindows->firstWhere('id', $windowId)?->name ?? $windowId;

        return view('WindowStaff', compact(
            'currentAppointment',
            'nextQueue',
            'windowId',
            'windowNumber',
            'windowName',
            'availableWindows',
            'todayCompleted',
            'todayTotal'
        ));
    }

    public function transactionHistory(Request $request)
    {
        $windowId   = session('staff_window_id', 1);
        $filterDate = $request->input('filter_date', now()->toDateString());
        $search     = $request->input('search', '');

        $query = AppointmentWindowLog::where('window_id', $windowId)
            ->whereNotNull('ended_at')
            ->with(['appointment.applicant']);

        if ($filterDate) {
            $query->whereDate('called_at', $filterDate);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('appoint_no', 'like', "%{$search}%")
                  ->orWhereHas('appointment.applicant', function ($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $logs       = $query->orderBy('called_at', 'desc')->paginate(20);
        $stats      = $this->calculateStats($windowId, $filterDate);
        $windowName = Window::find($windowId)?->name ?? $windowId;

        return view('transaction-history', compact('logs', 'windowId', 'windowName', 'stats', 'filterDate', 'search'));
    }

    private function calculateStats($windowId, $filterDate = null)
    {
        $query = AppointmentWindowLog::where('window_id', $windowId)->whereNotNull('ended_at');

        if ($filterDate) {
            $query->whereDate('called_at', $filterDate);
        }

        $logs             = $query->get();
        $totalServiceTime = 0;
        $countService     = 0;
        $totalWaitTime    = 0;
        $countWait        = 0;

        foreach ($logs as $log) {
            if ($log->served_at && $log->ended_at) {
                $totalServiceTime += $log->served_at->diffInSeconds($log->ended_at);
                $countService++;
            }
            if ($log->called_at && $log->served_at) {
                $totalWaitTime += $log->called_at->diffInSeconds($log->served_at);
                $countWait++;
            }
        }

        return [
            'total_completed' => $logs->count(),
            'avg_service_time' => $countService > 0 ? $totalServiceTime / $countService : 0,
            'avg_wait_time'    => $countWait > 0 ? $totalWaitTime / $countWait : 0,
        ];
    }

    public function changeWindow(Request $request)
    {
        $windowId = $request->input('window_id');

        if (!$windowId) {
            return back()->with('error', 'Please select a valid window.');
        }

        session(['staff_window_id' => $windowId]);

        return back()->with('success', 'Assigned to window successfully.');
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
        $windowNumber  = session('staff_window_id', 1);

        $windowStatus = Cache::get("window_status_{$windowNumber}", 'closed');
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

            $displayNum = (int) (Window::find($windowNumber)?->name ?? $windowNumber);

            $calledAt = Carbon::now();

            $appointment->update([
                'status'         => 'calling',
                'called_at'      => $calledAt,
                'counter_number' => $displayNum ?: $windowNumber,
                'window_number'  => $windowNumber,
            ]);

            // Open a new window log entry for this visit
            AppointmentWindowLog::updateOrCreate(
                ['appointment_id' => $appointment->id, 'window_id' => $windowNumber, 'ended_at' => null],
                ['window_name' => Window::find($windowNumber)?->name, 'appoint_no' => $appointment->appoint_no, 'called_at' => $calledAt, 'served_at' => null]
            );

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
        $appointment   = Appointment::findOrFail($appointmentId);
        $servedAt      = Carbon::now();

        $appointment->update([
            'status'                => 'serving',
            'served_at'             => $servedAt,
            'served_by_user_id'     => auth()->id(),
            'hold_started_at'       => null,
            'hold_duration_seconds' => 0,
        ]);

        AppointmentWindowLog::where('appointment_id', $appointmentId)
            ->whereNull('ended_at')
            ->update(['served_at' => $servedAt]);

        return back()->with('success', 'Service started.');
    }

    public function endService(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $appointment   = Appointment::findOrFail($appointmentId);
        $endedAt       = Carbon::now();

        $appointment->update([
            'status'                   => 'completed',
            'transaction_completed_at' => $endedAt,
        ]);

        AppointmentWindowLog::where('appointment_id', $appointmentId)
            ->whereNull('ended_at')
            ->update(['ended_at' => $endedAt, 'end_type' => 'completed']);

        return back()->with('success', 'Service finished.');
    }

    public function holdTicket(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $appointment = Appointment::findOrFail($appointmentId);

        $appointment->update([
            'status'          => 'on_hold',
            'hold_started_at' => Carbon::now(),
        ]);
        return back()->with('success', 'Ticket put on hold.');
    }

    public function resumeTicket(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $appointment = Appointment::findOrFail($appointmentId);

        $additionalHold = $appointment->hold_started_at
            ? (int) $appointment->hold_started_at->diffInSeconds(Carbon::now())
            : 0;

        $appointment->update([
            'status'                 => 'serving',
            'hold_started_at'        => null,
            'hold_duration_seconds'  => max(0, (int) $appointment->hold_duration_seconds + $additionalHold),
        ]);
        return back()->with('success', 'Ticket resumed.');
    }

    /**
     * Returns a lightweight snapshot of the queue relevant to this window.
     * The Window Staff page polls this every 8 s and reloads if anything changed.
     */
    public function getQueueState(): JsonResponse
    {
        $windowNumber = session('staff_window_id', 1);

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

    public function transfer(Request $request)
    {
        $appointmentId  = $request->input('appointment_id');
        $targetWindowId = $request->input('window_number');
        $appointment    = Appointment::findOrFail($appointmentId);
        $transferredAt  = Carbon::now();

        // Close the current window's log entry
        AppointmentWindowLog::where('appointment_id', $appointmentId)
            ->whereNull('ended_at')
            ->update(['ended_at' => $transferredAt, 'end_type' => 'transferred']);

        $appointment->update([
            'window_number' => $targetWindowId,
            'status'        => 'pending',
        ]);

        $targetWindow = Window::find($targetWindowId);
        return back()->with('success', 'Transferred to ' . ($targetWindow?->name ?? "Window {$targetWindowId}") . '.');
    }

    public function openCounter(Request $request)
    {
        $windowId = session('staff_window_id');
        Cache::put("window_status_{$windowId}", 'open', now()->addHours(24));
        session(['window_status' => 'open']);

        $windowName = Window::find($windowId)?->name ?? $windowId;
        return back()->with('success', "Window {$windowName} is now OPEN.");
    }

    public function closeCounter(Request $request)
    {
        $windowId = session('staff_window_id');

        $currentAppointment = Appointment::where('window_number', $windowId)
            ->whereIn('status', ['calling', 'serving'])
            ->first();

        if ($currentAppointment) {
            $windowName = Window::find($windowId)?->name ?? $windowId;
            return back()->with('error', "Cannot close Window {$windowName} while an applicant is being served. Please end or transfer the current appointment first.");
        }

        Cache::put("window_status_{$windowId}", 'closed', now()->addHours(24));
        session(['window_status' => 'closed', 'window_closed_at' => Carbon::now()]);

        $windowName = Window::find($windowId)?->name ?? $windowId;
        return back()->with('success', "Window {$windowName} is now CLOSED.");
    }

    public function startBreak(Request $request)
    {
        $windowId = session('staff_window_id');

        $currentAppointment = Appointment::where('window_number', $windowId)
            ->whereIn('status', ['calling', 'serving'])
            ->first();

        if ($currentAppointment) {
            $windowName = Window::find($windowId)?->name ?? $windowId;
            return back()->with('error', "Cannot go on break while an applicant is being served at Window {$windowName}. Please end or transfer the current appointment first.");
        }

        Cache::put("window_status_{$windowId}", 'break', now()->addHours(24));
        session(['window_status' => 'break', 'window_break_at' => Carbon::now()]);

        $windowName = Window::find($windowId)?->name ?? $windowId;
        return back()->with('success', "You are now on BREAK. Window {$windowName} is temporarily unavailable.");
    }

    public function getQueueData(): JsonResponse
    {
        $windows  = Window::all();
        $counters = [];

        foreach ($windows as $window) {
            $appointment = Appointment::with('applicant')
                ->where('window_number', $window->id)
                ->whereIn('status', ['calling', 'serving'])
                ->whereDate('appointment_date', today())
                ->first();

            $counters[$window->id] = [
                'counter'     => $window->name,
                'appointment' => $appointment,
                'status'      => $appointment ? ucfirst($appointment->status) : 'Vacant',
            ];
        }

        $currentCall = Appointment::with('applicant')
            ->whereDate('appointment_date', today())
            ->where('status', 'calling')
            ->whereNotNull('called_at')
            ->orderBy('called_at', 'desc')
            ->first();

        return response()->json([
            'counters'    => $counters,
            'currentCall' => $currentCall,
        ]);
    }
}
