<?php

namespace Database\Seeders;

use App\Models\Purpose;
use Illuminate\Database\Seeder;

class PurposeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purposes = [
            ['purpose_name' => 'Contract Processing', 'estimated_mins' => 30],
            ['purpose_name' => 'Interview', 'estimated_mins' => 45],
            ['purpose_name' => 'Document Submission', 'estimated_mins' => 20],
            ['purpose_name' => 'Medical Clearance', 'estimated_mins' => 30],
            ['purpose_name' => 'Visa Application', 'estimated_mins' => 45],
            ['purpose_name' => 'Pre-Departure Orientation', 'estimated_mins' => 60],
            ['purpose_name' => 'Contract Verification', 'estimated_mins' => 20],
            ['purpose_name' => 'Appeal/Concern', 'estimated_mins' => 30],
            ['purpose_name' => 'Release of Documents', 'estimated_mins' => 15],
            ['purpose_name' => 'General Inquiry', 'estimated_mins' => 15],
        ];

        foreach ($purposes as $purpose) {
            Purpose::firstOrCreate(
                ['purpose_name' => $purpose['purpose_name']],
                [
                    'estimated_mins' => $purpose['estimated_mins'],
                ]
            );
        }
    }
}