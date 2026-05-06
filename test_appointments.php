<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Appointment;

echo "Checking appointments for today: " . date('Y-m-d') . "\n";

$appointments = Appointment::whereDate('appointment_date', today())->get();
echo "Count: " . $appointments->count() . "\n";

foreach ($appointments as $a) {
    echo "ID: " . $a->id . " Status: " . $a->status . " Window: " . ($a->window_number ?? 'null') . "\n";
}