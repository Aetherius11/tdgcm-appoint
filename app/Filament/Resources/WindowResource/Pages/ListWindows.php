<?php

namespace App\Filament\Resources\WindowResource\Pages;

use App\Filament\Resources\WindowResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWindows extends ListRecords
{
    protected static string $resource = WindowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
