<?php

namespace App\Filament\Resources\PurposeResource\Pages;

use App\Filament\Resources\PurposeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurpose extends EditRecord
{
    protected static string $resource = PurposeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
