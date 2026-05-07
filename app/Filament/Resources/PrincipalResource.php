<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrincipalResource\Pages;
use App\Models\Principal;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrincipalResource extends Resource
{
    protected static ?string $model = Principal::class;

    // This puts the resource into the "System Maintenance" group
    protected static ?string $navigationGroup = 'System Maintenance';

    // Use this to change the icon (e.g., a building icon)
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 2;

    // Display "Departments" in the admin panel navigation
    protected static ?string $pluralLabel = 'Departments';

    protected static ?string $navigationLabel = 'Departments';

    protected static ?string $modelLabel = 'Department';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Department Name')
                    ->required(),

                Select::make('purposes')
                    ->multiple()
                    ->relationship(
                        name: 'purposes',
                        titleAttribute: 'purpose_name' // FIXED: Changed from 'name' to 'purpose_name'
                    )
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('department.department_name')
                    ->label('Office/Company')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Department Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purposes.purpose_name') // FIXED: Notice 'purpose_name'
                    ->label('Purposes')
                    ->badge()
                    ->separator(','),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPrincipals::route('/'),
            'create' => Pages\CreatePrincipal::route('/create'),
            'edit' => Pages\EditPrincipal::route('/{record}/edit'),
        ];
    }
}
