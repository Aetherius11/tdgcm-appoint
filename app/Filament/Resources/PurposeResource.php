<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurposeResource\Pages;
use App\Filament\Resources\PurposeResource\RelationManagers;
use App\Models\Purpose;
use Filament\Forms\Components\Select;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\MultiSelect;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Collection;

class PurposeResource extends Resource
{
    protected static ?string $model = Purpose::class;

    // This puts the resource into the "System Maintenance" group
    protected static ?string $navigationGroup = 'System Maintenance';
    
    // Use this to change the icon (e.g., a building icon)
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 2;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('purpose_name')->required(),
                
                Select::make('time_availabilities') // This is the form field name
                ->multiple()
                ->relationship('timeAvailabilities', 'id') // This must match the function name in Purpose.php
                ->getOptionLabelFromRecordUsing(fn ($record) => "Day {$record->day_of_week}: {$record->start_time} - {$record->end_time}")
                ->preload()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purpose_name')
                    ->searchable(),
                // Change 'timeAvailabilities' to 'availabilities' (a custom name)
                Tables\Columns\TextColumn::make('availabilities')
                    ->label('Available Days')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->state(function ($record): array {
                        return $record->timeAvailabilities
                            ->groupBy(function ($slot) {
                                // Strips off trailing seconds (:00) for a cleaner format
                                $start = \Carbon\Carbon::parse($slot->start_time)->format('H:i');
                                $end = \Carbon\Carbon::parse($slot->end_time)->format('H:i');
                                return "{$start}-{$end}";
                            })
                            ->map(function ($group, $timeRange) {
                                $days = $group->pluck('day_of_week')
                                    ->unique() 
                                    ->map(fn ($day) => match ($day) {
                                        'Monday'    => 'Mon',
                                        'Tuesday'   => 'Tue',
                                        'Wednesday' => 'Wed',
                                        'Thursday'  => 'Thu',
                                        'Friday'    => 'Fri',
                                        'Saturday'  => 'Sat',
                                        'Sunday'    => 'Sun',
                                        default     => substr($day, 0, 3),
                                    })
                                    ->implode(',');

                                // Custom requested presentation: HH:MM-HH:MM (Days)
                                return "{$timeRange} ({$days})";
                            })
                            ->values()
                            ->toArray();
                    })
                    ->color('success')
                    ->weight('bold'),
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
            'index' => Pages\ListPurposes::route('/'),
            'create' => Pages\CreatePurpose::route('/create'),
            'edit' => Pages\EditPurpose::route('/{record}/edit'),
        ];
    }
}
