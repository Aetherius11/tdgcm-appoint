<?php

namespace App\Http\Controllers;

use App\Models\Window;
use App\Models\Appointment;
use Illuminate\Support\Facades\Cache;

class TVDisplayController extends Controller
{
    public function index()
    {
        // 1. DYNAMIC: Fetch all windows from your maintenance table
        $windowModels = Window::orderBy('id', 'asc')->get();
        
        $counters = [];

        // 2. Initialize counters based on your database windows instead of a hard-coded loop
        foreach ($windowModels as $window) {
            // Using window ID for cache lookup to maintain individual window states
            $windowStatus = Cache::get("window_status_{$window->id}", 'closed');
            $defaultStatus = $windowStatus === 'break' ? 'On Break' : 'Vacant';
            
            $counters[$window->id] = [
                'counter' => $window->name, // Displays the custom name from Admin (e.g., "Window 1")
                'appointment' => null,
                'status' => $defaultStatus,
                'window_status' => $windowStatus,
            ];
        }

        // 3. Fetch active appointments (Only calling/serving show on counters)
        $appointments = Appointment::with(['applicant', 'timeSlot'])
            ->whereIn('status', ['calling', 'serving'])
            ->whereNotNull('window_number')
            ->where(function($q) {
                $q->whereDate('appointment_date', today())
                  ->orWhere(function($q2) {
                      $q2->whereNotNull('remarks')->where('remarks', 'like', 'Guard override:%');
                  });
            })
            ->orderBy('window_number')
            ->get();

        // 4. Fill in the dynamic counters with actual appointments
        foreach ($appointments as $appointment) {
            if (isset($counters[$appointment->window_number])) {
                $statusLabel = 'Vacant';
                if ($appointment->status === 'calling') {
                    $statusLabel = 'Calling';
                } elseif ($appointment->status === 'serving') {
                    $statusLabel = 'Serving';
                } elseif ($appointment->status === 'on_hold') {
                    $statusLabel = 'On Hold';
                }

                $counters[$appointment->window_number]['appointment'] = $appointment;
                $counters[$appointment->window_number]['status'] = $statusLabel;
            }
        }

        // 5. Get the most recently called appointment for the large display
        $currentCall = Appointment::with(['applicant', 'timeSlot'])
            ->whereIn('status', ['calling', 'serving'])
            ->whereNotNull('called_at')
            ->where(function($q) {
                $q->whereDate('appointment_date', today())
                  ->orWhere(function($q2) {
                      $q2->whereNotNull('remarks')->where('remarks', 'like', 'Guard override:%');
                  });
            })
            ->orderBy('called_at', 'desc')
            ->first();

        // 6. Get upcoming appointments (No changes needed here)
        $upNext = Appointment::with(['applicant', 'timeSlot'])
            ->where(function($q) {
                $q->where(function($inner) {
                    $inner->whereIn('status', ['pending', 'approved'])
                          ->whereNull('window_number');
                })
                ->orWhere(function($inner) {
                    $inner->whereIn('status', ['pending', 'approved'])
                          ->whereNotNull('window_number');
                })
                ->orWhere('status', 'on_hold');
            })
            ->whereNotNull('checked_in_at')
            ->where(function($q) {
                $q->whereDate('appointment_date', today())
                  ->orWhere(function($q2) {
                      $q2->whereNotNull('remarks')->where('remarks', 'like', 'Guard override:%');
                  });
            })
            ->orderBy('checked_in_at', 'asc')
            ->take(5)
            ->get();

        return view('tv-display', [
            'counters' => $counters,
            'currentCall' => $currentCall,
            'upNext' => $upNext
        ]);
    }
}