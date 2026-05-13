<?php

namespace App\Filament\Resources\TimeAvailabilityResource\Pages;

use App\Filament\Resources\TimeAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimeAvailability extends EditRecord
{
    protected static string $resource = TimeAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
