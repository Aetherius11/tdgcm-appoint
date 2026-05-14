<?php

namespace App\Http\Controllers;

use App\Models\Appointment;

class TVDisplayController extends Controller
{
    public function index()
    {
        // Initialize 12 counters, reflecting cached window status (open/break/closed)
        $counters = [];
        for ($i = 1; $i <= 12; $i++) {
            $windowStatus = \Cache::get("window_status_{$i}", 'closed');
            $defaultStatus = $windowStatus === 'break' ? 'On Break' : 'Vacant';
            $counters[$i] = [
                'counter' => $i,
                'appointment' => null,
                'status' => $defaultStatus,
                'window_status' => $windowStatus,
            ];
        }

        // Only calling/serving show on counters — on_hold tickets go back to Up Next
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

        // Fill in the counters with actual appointments
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

                $counters[$appointment->window_number] = [
                    'counter' => $appointment->window_number,
                    'appointment' => $appointment,
                    'status' => $statusLabel
                ];
            }
        }

        // Get the most recently called appointment for the large display
        // Only show today's appointments or guard override check-ins
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

        // Get upcoming appointments: general queue, transferred (pending with window assigned), and on-hold
        $upNext = Appointment::with(['applicant', 'timeSlot'])
            ->where(function($q) {
                // General queue: checked in, no window assigned yet
                $q->where(function($inner) {
                    $inner->whereIn('status', ['pending', 'approved'])
                          ->whereNull('window_number');
                })
                // Transferred: pending/approved with a target window (waiting to be called there)
                ->orWhere(function($inner) {
                    $inner->whereIn('status', ['pending', 'approved'])
                          ->whereNotNull('window_number');
                })
                // On-hold: sent back to queue
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