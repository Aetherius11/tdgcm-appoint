<?php

namespace App\Livewire;

use App\Models\Appointment;
use Livewire\Component;

class QueueDisplay extends Component
{
    public function render()
    {
        $counters = [];

        for ($i = 1; $i <= 12; $i++) {
            $counters[$i] = ['counter' => $i, 'appointment' => null, 'status' => 'Vacant'];
        }

        $appointments = Appointment::with('applicant')
            ->whereDate('appointment_date', today())
            ->whereIn('status', ['calling', 'serving', 'on_hold'])
            ->whereNotNull('counter_number')
            ->orderBy('counter_number')
            ->get();

        foreach ($appointments as $appointment) {
            if (isset($counters[$appointment->counter_number])) {
                $counters[$appointment->counter_number] = [
                    'counter' => $appointment->counter_number,
                    'appointment' => $appointment,
                    'status' => match($appointment->status) {
                        'calling' => 'Calling',
                        'serving' => 'Serving',
                        'on_hold' => 'On Hold',
                        default   => ucfirst($appointment->status),
                    },
                ];
            }
        }

        $currentCall = Appointment::with('applicant')
            ->whereIn('status', ['calling', 'serving'])
            ->whereNotNull('called_at')
            ->where(function ($q) {
                $q->whereDate('appointment_date', today())
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('remarks')->where('remarks', 'like', 'Guard override:%');
                  });
            })
            ->orderBy('called_at', 'desc')
            ->first();

        $upNext = Appointment::with('applicant')
            ->whereIn('status', ['pending', 'approved'])
            ->whereNotNull('checked_in_at')
            ->whereNull('window_number')
            ->where(function ($q) {
                $q->whereDate('appointment_date', today())
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('remarks')->where('remarks', 'like', 'Guard override:%');
                  });
            })
            ->orderBy('checked_in_at', 'asc')
            ->take(5)
            ->get();

        return view('livewire.queue-display', [
            'counters'    => $counters,
            'currentCall' => $currentCall,
            'upNext'      => $upNext,
        ]);
    }
}
