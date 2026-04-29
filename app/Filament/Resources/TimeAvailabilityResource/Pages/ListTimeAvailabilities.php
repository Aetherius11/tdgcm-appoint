<?php

namespace App\Filament\Resources\TimeAvailabilityResource\Pages;

use App\Filament\Resources\TimeAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimeAvailabilities extends ListRecords
{
    protected static string $resource = TimeAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
