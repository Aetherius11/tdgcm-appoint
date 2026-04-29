<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimeAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purposes = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]; // IDs for Contract, Interview, Inquiry
        $days = [1, 2, 3, 4, 5];  // Mon, Tue, Wed, Thu

        foreach ($purposes as $purpose) {
            foreach ($days as $day) {
                \App\Models\TimeAvailability::insert([
                    'purpose_id' => $purpose,
                    'day_of_week' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '10:00:00',
                    'max_slots' => 5,
                ]);
            }
        }

        foreach ($purposes as $purpose) {
            foreach ($days as $day) {
                \App\Models\TimeAvailability::insert([
                    'purpose_id' => $purpose,
                    'day_of_week' => $day,
                    'start_time' => '10:00:00',
                    'end_time' => '11:00:00',
                    'max_slots' => 5,
                ]);
            }
        }

        foreach ($purposes as $purpose) {
            foreach ($days as $day) {
                \App\Models\TimeAvailability::insert([
                    'purpose_id' => $purpose,
                    'day_of_week' => $day,
                    'start_time' => '11:00:00',
                    'end_time' => '12:00:00',
                    'max_slots' => 5,
                ]);
            }
        }

        foreach ($purposes as $purpose) {
            foreach ($days as $day) {
                \App\Models\TimeAvailability::insert([
                    'purpose_id' => $purpose,
                    'day_of_week' => $day,
                    'start_time' => '01:30:00',
                    'end_time' => '02:30:00',
                    'max_slots' => 5,
                ]);
            }
        }

        foreach ($purposes as $purpose) {
            foreach ($days as $day) {
                \App\Models\TimeAvailability::insert([
                    'purpose_id' => $purpose,
                    'day_of_week' => $day,
                    'start_time' => '02:30:00',
                    'end_time' => '03:30:00',
                    'max_slots' => 5,
                ]);
            }
        }

        foreach ($purposes as $purpose) {
            foreach ($days as $day) {
                \App\Models\TimeAvailability::insert([
                    'purpose_id' => $purpose,
                    'day_of_week' => $day,
                    'start_time' => '02:30:00',
                    'end_time' => '03:30:00',
                    'max_slots' => 5,
                ]);
            }
        }

        foreach ($purposes as $purpose) {
            foreach ($days as $day) {
                \App\Models\TimeAvailability::insert([
                    'purpose_id' => $purpose,
                    'day_of_week' => $day,
                    'start_time' => '03:30:00',
                    'end_time' => '04:30:00',
                    'max_slots' => 5,
                ]);
            }
        }

        foreach ($purposes as $purpose) {
            foreach ($days as $day) {
                \App\Models\TimeAvailability::insert([
                    'purpose_id' => $purpose,
                    'day_of_week' => $day,
                    'start_time' => '04:30:00',
                    'end_time' => '05:30:00',
                    'max_slots' => 5,
                ]);
            }
        }
    }
}
