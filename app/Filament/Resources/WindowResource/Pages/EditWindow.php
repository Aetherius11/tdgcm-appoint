<?php

namespace App\Filament\Resources\WindowResource\Pages;

use App\Filament\Resources\WindowResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWindow extends EditRecord
{
    protected static string $resource = WindowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
