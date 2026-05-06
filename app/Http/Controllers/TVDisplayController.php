<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class TVDisplayController extends Controller
{
    public function index()
    {
        // Initialize 12 counters
        $counters = [];
        for ($i = 1; $i <= 12; $i++) {
            $counters[$i] = [
                'counter' => $i,
                'appointment' => null,
                'status' => 'Vacant'
            ];
        }

        // Get today's appointments with assigned windows
        $appointments = Appointment::with(['applicant', 'timeSlot'])
            ->whereDate('appointment_date', today())
            ->whereIn('status', ['calling', 'serving', 'on_hold', 'completed'])
            ->whereNotNull('window_number')
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
                } elseif ($appointment->status === 'completed') {
                    $statusLabel = 'Completed';
                }

                $counters[$appointment->window_number] = [
                    'counter' => $appointment->window_number,
                    'appointment' => $appointment,
                    'status' => $statusLabel
                ];
            }
        }

        // Get the most recently called appointment for the large display
        $currentCall = Appointment::with(['applicant', 'timeSlot'])
            ->whereDate('appointment_date', today())
            ->where('status', 'calling')
            ->whereNotNull('called_at')
            ->orderBy('called_at', 'desc')
            ->first();

        // Get upcoming appointments (next in queue - only those who have checked in with guard)
        $upNext = Appointment::with(['applicant', 'timeSlot'])
            ->whereDate('appointment_date', today())
            ->whereIn('status', ['pending', 'approved'])
            ->whereNotNull('checked_in_at') // Only show appointments that have checked in
            ->whereNull('window_number')
            ->orderBy('checked_in_at', 'asc') // Order by check-in time
            ->take(5)
            ->get();

        return view('livewire.queue-display', [
            'counters' => $counters,
            'currentCall' => $currentCall,
            'upNext' => $upNext
        ]);
    }
}