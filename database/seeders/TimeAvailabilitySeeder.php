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
        \App\Models\TimeAvailability::insert([
            [
                'purpose_id' => 1,
                'day_of_week' => 1, // Change 'Monday' to 1
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
                'max_slots' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'purpose_id' => 1,
                'day_of_week' => 1, // Change 'Monday' to 1
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'max_slots' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
