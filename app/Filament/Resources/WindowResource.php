<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WindowResource\Pages;
use App\Filament\Resources\WindowResource\RelationManagers;
use App\Models\Window;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WindowResource extends Resource
{
    protected static ?string $model = Window::class;

    protected static ?string $navigationGroup = 'Operations'; // Group it for better UI
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 1;

    // app/Filament/Resources/WindowResource.php

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // app/Filament/Resources/WindowResource.php
                Tables\Columns\TextColumn::make('name')
                    ->formatStateUsing(fn (string $state): string => "Window {$state}")
                    ->searchable()
                    ->sortable(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWindows::route('/'),
            'create' => Pages\CreateWindow::route('/create'),
            'edit' => Pages\EditWindow::route('/{record}/edit'),
        ];
    }
}
