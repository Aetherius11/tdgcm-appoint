<?php

namespace App\Filament\Resources\TimeAvailabilityResource\Pages;

use App\Filament\Resources\TimeAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTimeAvailability extends CreateRecord
{
    protected static string $resource = TimeAvailabilityResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $days = $data['days_of_week'] ?? [];
        
        // Safety check: if no days selected, prevent the error
        if (empty($days)) {
            throw new \Exception('Please select at least one day of the week.');
        }

        $record = null;

        foreach ($days as $day) {
            $record = static::getModel()::create([
                'day_of_week' => $day,
                'purpose_id'  => $data['purpose_id'] ?? null, // Uses the ID if selected, otherwise null
                'start_time'  => $data['start_time'],
                'end_time'    => $data['end_time'],
                'max_slots'   => $data['max_slots'],
            ]);
        }

        return $record; // Now guaranteed not to be null if a day was selected
    }
}
