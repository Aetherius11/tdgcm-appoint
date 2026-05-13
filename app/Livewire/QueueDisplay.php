<?php

namespace App\Livewire;

use App\Models\Appointment;
use Livewire\Component;

class QueueDisplay extends Component
{
    public function render()
    {
        $counters = [];

        // Initialize 12 counters
        for ($i = 1; $i <= 12; $i++) {
            $counters[$i] = [
                'counter' => $i,
                'appointment' => null,
                'status' => 'Vacant'
            ];
        }

        // Get today's appointments with assigned counters
        $appointments = Appointment::with('applicant')
            ->whereDate('appointment_date', today())
            ->whereIn('status', ['ready', 'ongoing'])
            ->whereNotNull('counter_number')
            ->orderBy('counter_number')
            ->get();

        foreach ($appointments as $appointment) {
            if (isset($counters[$appointment->counter_number])) {
                $counters[$appointment->counter_number] = [
                    'counter' => $appointment->counter_number,
                    'appointment' => $appointment,
                    'status' => $appointment->status === 'ongoing' ? 'Ongoing' : 'Ready'
                ];
            }
        }

        // Get the most recently called appointment for the large display
        $currentCall = Appointment::with('applicant')
            ->whereDate('appointment_date', today())
            ->where('status', 'ongoing')
            ->whereNotNull('called_at')
            ->orderBy('called_at', 'desc')
            ->first();

        return view('livewire.queue-display', [
            'counters' => $counters,
            'currentCall' => $currentCall
        ]);
    }
}
