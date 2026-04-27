<?php

namespace App\Filament\Resources\PurposeResource\Pages;

use App\Filament\Resources\PurposeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurposes extends ListRecords
{
    protected static string $resource = PurposeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
