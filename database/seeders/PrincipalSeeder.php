<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\OfficeDepartment;
use App\Models\Principal;
use Illuminate\Database\Seeder;

class PrincipalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create the company
        $company = Company::first();
        if (! $company) {
            $company = Company::create([
                'name' => 'TDGCM',
            ]);
        }

        // Get or create the office department
        $department = OfficeDepartment::first();
        if (! $department) {
            $department = OfficeDepartment::create([
                'company_id' => $company->id,
                'name' => 'Administrative Department',
                'description' => 'Main administrative office',
                'is_active' => true,
            ]);
        }

        // Create principals
        $principals = [
            ['name' => 'DCL', 'contact_info' => 'dcl@tdgcm.com'],
            ['name' => 'NYKC', 'contact_info' => 'nykc@tdgcm.com'],
            ['name' => 'WOW', 'contact_info' => 'wow@tdgcm.com'],
            ['name' => 'DG3', 'contact_info' => 'dg3@tdgcm.com'],
            ['name' => 'Cruise Visa Processing', 'contact_info' => 'cruisevisa@tdgcm.com'],
            ['name' => 'Fleet B', 'contact_info' => 'fleetb@tdgcm.com'],
            ['name' => 'Recruitment', 'contact_info' => 'recruitment@tdgcm.com'],
            ['name' => 'Crew Welfare', 'contact_info' => 'crewelfare@tdgcm.com'],
        ];

        foreach ($principals as $principal) {
            Principal::firstOrCreate(
                ['name' => $principal['name']],
                [
                    'department_id' => $department->id,
                    'contact_info' => $principal['contact_info'],
                ]
            );
        }
    }
}
