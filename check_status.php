<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

echo "=== Appointment Status Overview ===\n\n";

// Total appointments
$total = Appointment::count();
echo "Total appointments: $total\n";

// Today's appointments
$today = Appointment::whereDate('appointment_date', today())->count();
echo "Today's appointments: $today\n";

// Checked in appointments
$checkedIn = Appointment::whereNotNull('checked_in_at')->count();
echo "Checked in appointments: $checkedIn\n";

// Status breakdown
echo "\n=== Status Breakdown ===\n";
$statuses = Ay('status')
    ppointment::select('status', DB::raw('count(*) as count'))
    ->groupB->get();
foreach ($statuses as $s) {
    echo "$s->status: $s->count\n";
}

// Window number breakdown
echo "\n=== Window Number Breakdown ===\n";
$windows = Appointment::select('window_number', DB::raw('count(*) as count'))
    ->whereNotNull('window_number')
    ->groupBy('window_number')
    ->get();
foreach ($windows as $w) {
    echo "Window $w->window_number: $w->count appointments\n";
}

// Check today's checked-in appointments without window
echo "\n=== Today's Queue (checked-in, no window) ===\n";
$queue = Appointment::whereDate('appointment_date', today())
    ->whereNotNull('checked_in_at')
    ->whereNull('window_number')
    ->whereIn('status', ['pending', 'approved'])
    ->orderBy('checked_in_at', 'asc')
    ->take(5)
    ->get();
foreach ($queue as $q) {
    echo "ID: $q->id, Name: " . $q->applicant->first_name . " " . $q->applicant->last_name . ", Status: $q->status, Checked in: " . ($q->checked_in_at ? $q->checked_in_at->format('g:i A') : 'N/A') . "\n";
}