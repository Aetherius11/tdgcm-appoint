<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Window;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TVDisplayController extends Controller
{
    private function dateFilter($q): void
    {
        $q->whereDate('appointment_date', today())
          ->orWhere(function ($q2) {
              $q2->whereNotNull('remarks')->where('remarks', 'like', 'Guard override:%');
          });
    }

    private function buildCounters(): array
    {
        $windows  = Window::orderBy('id')->get();
        $counters = [];

        foreach ($windows as $window) {
            $windowStatus = Cache::get("window_status_{$window->id}", 'closed');
            $counters[$window->id] = [
                'counter'       => $window->name,
                'appointment'   => null,
                'status'        => $windowStatus === 'break' ? 'On Break' : 'Vacant',
                'window_status' => $windowStatus,
            ];
        }

        $appointments = Appointment::with('applicant')
            ->whereIn('status', ['calling', 'serving'])
            ->whereNotNull('window_number')
            ->where(fn($q) => $this->dateFilter($q))
            ->orderBy('window_number')
            ->get();

        foreach ($appointments as $appt) {
            $n = $appt->window_number;
            if (!isset($counters[$n])) continue;
            $counters[$n] = [
                'counter'       => $counters[$n]['counter'],
                'appointment'   => $appt,
                'status'        => $appt->status === 'calling' ? 'Calling' : 'Serving',
                'window_status' => $counters[$n]['window_status'],
            ];
        }

        return $counters;
    }

    private function buildCurrentCall()
    {
        return Appointment::with('applicant')
            ->whereIn('status', ['calling', 'serving'])
            ->whereNotNull('called_at')
            ->where(fn($q) => $this->dateFilter($q))
            ->orderBy('called_at', 'desc')
            ->first();
    }

    private function buildUpNext()
    {
        return Appointment::with('applicant')
            ->where(function ($q) {
                $q->where(function ($inner) {
                    $inner->whereIn('status', ['pending', 'approved'])->whereNull('window_number');
                })
                ->orWhere(function ($inner) {
                    $inner->whereIn('status', ['pending', 'approved'])->whereNotNull('window_number');
                })
                ->orWhere('status', 'on_hold');
            })
            ->whereNotNull('checked_in_at')
            ->where(fn($q) => $this->dateFilter($q))
            ->orderBy('checked_in_at', 'asc')
            ->take(10)
            ->get();
    }

    public function index()
    {
        return view('tv-display', [
            'counters'    => $this->buildCounters(),
            'currentCall' => $this->buildCurrentCall(),
            'upNext'      => $this->buildUpNext(),
        ]);
    }

    public function getData(): JsonResponse
    {
        $counters    = $this->buildCounters();
        $currentCall = $this->buildCurrentCall();
        $upNext      = $this->buildUpNext();

        $countersJson = array_values(array_map(function ($c) {
            $appt = $c['appointment'];
            return [
                'counter'    => $c['counter'],
                'appoint_no' => $appt ? str_pad(substr($appt->appoint_no, -4), 4, '0', STR_PAD_LEFT) : null,
                'name'       => $appt ? (substr($appt->applicant->first_name, 0, 1) . '. ' . $appt->applicant->last_name) : null,
                'status'     => $c['status'],
            ];
        }, $counters));

        $callJson = null;
        if ($currentCall) {
            $callJson = [
                'appoint_no'     => str_pad(substr($currentCall->appoint_no, -4), 4, '0', STR_PAD_LEFT),
                'name'           => substr($currentCall->applicant->first_name, 0, 1) . '. ' . $currentCall->applicant->last_name,
                'counter_number' => $currentCall->counter_number,
                'status'         => $currentCall->status,
                'call_key'       => $currentCall->appoint_no . '_' . ($currentCall->called_at?->timestamp),
            ];
        }

        $upNextJson = $upNext->map(function ($next) {
            $isTransferred = in_array($next->status, ['pending', 'approved']) && $next->window_number;
            return [
                'appoint_no'     => str_pad(substr($next->appoint_no, -4), 4, '0', STR_PAD_LEFT),
                'name'           => substr($next->applicant->first_name, 0, 1) . '. ' . $next->applicant->last_name,
                'status'         => $next->status,
                'window_number'  => $next->window_number,
                'is_transferred' => $isTransferred,
                'checked_in_at'  => $next->checked_in_at?->format('g:i A'),
            ];
        })->values();

        return response()->json([
            'counters'    => $countersJson,
            'currentCall' => $callJson,
            'upNext'      => $upNextJson,
            'now'         => now()->format('l, F j, Y — g:i A'),
        ]);
    }
}
