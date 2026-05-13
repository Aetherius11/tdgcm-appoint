<?php

namespace Database\Seeders;

use App\Models\Purpose;
use App\Models\TimeAvailability;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all purposes
        $purposes = Purpose::all();

        if ($purposes->isEmpty()) {
            $this->command->error('No purposes found. Please seed purposes first.');

            return;
        }

        // Time slots from 8:30 AM to 5:00 PM with 30-minute increments
        // 8:30, 9:00, 9:30, 10:00, 10:30, 11:00, 11:30, 12:00, 12:30, 13:00, 13:30, 14:00, 14:30, 15:00, 15:30, 16:00, 16:30, 17:00
        $timeSlots = [
            ['start' => '08:30', 'end' => '09:30'],
            ['start' => '09:30', 'end' => '10:30'],
            ['start' => '10:30', 'end' => '11:30'],
            ['start' => '11:30', 'end' => '12:30'],
            ['start' => '13:30', 'end' => '14:30'],
            ['start' => '14:30', 'end' => '15:30'],
            ['start' => '15:30', 'end' => '16:30'],
            ['start' => '16:30', 'end' => '17:30'],
        ];
    

        // Days of the week (1 = Monday to 5 = Friday, excluding weekends)
        $workingDays = [1, 2, 3, 4, 5]; // Monday to Friday

        $maxSlots = 24; // Limit of 24 bookings per timeslot

        $created = 0;
        foreach ($purposes as $purpose) {
            foreach ($workingDays as $day) {
                foreach ($timeSlots as $slot) {
                    TimeAvailability::firstOrCreate(
                        [
                            'purpose_id' => $purpose->id,
                            'day_of_week' => $day,
                            'start_time' => $slot['start'],
                            'end_time' => $slot['end'],
                        ],
                        [
                            'max_slots' => $maxSlots,
                        ]
                    );
                    $created++;
                }
            }
        }

        $this->command->info("Created {$created} time availability slots for {$purposes->count()} purposes.");
    }
}
