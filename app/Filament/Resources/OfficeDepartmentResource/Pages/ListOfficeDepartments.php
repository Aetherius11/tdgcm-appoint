<?php

namespace App\Filament\Resources\OfficeDepartmentResource\Pages;

use App\Filament\Resources\OfficeDepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfficeDepartments extends ListRecords
{
    protected static string $resource = OfficeDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
