<?php

namespace App\Filament\Resources\OfficeDepartmentResource\Pages;

use App\Filament\Resources\OfficeDepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOfficeDepartment extends EditRecord
{
    protected static string $resource = OfficeDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
