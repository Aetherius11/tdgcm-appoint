<?php

namespace App\Filament\Resources;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\TimeAvailabilityResource\Pages;
use App\Filament\Resources\TimeAvailabilityResource\RelationManagers;
use App\Models\TimeAvailability;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TimeAvailabilityResource extends Resource
{
    protected static ?string $navigationGroup = 'Operations'; // Group it for better UI
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('purpose_id') // ADD THIS
                ->relationship('purpose', 'purpose_name'),

                Forms\Components\CheckboxList::make('days_of_week') // Changed to Multi-choice
                ->label('Select Days')
                ->options([
                    'Monday' => 'Monday',
                    'Tuesday' => 'Tuesday',
                    'Wednesday' => 'Wednesday',
                    'Thursday' => 'Thursday',
                    'Friday' => 'Friday',
                ])
                ->columns(2)
                ->required()
                ->dehydrated(true),
                Forms\Components\TimePicker::make('start_time')
                ->seconds(false)
                ->required(),
                Forms\Components\TimePicker::make('end_time')
                ->seconds(false)
                ->required(),
                Forms\Components\TextInput::make('max_slots')->numeric()->default(5),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('day_of_week')
                    ->label('Day')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Monday'    => 'Monday',
                        'Tuesday'   => 'Tuesday',
                        'Wednesday' => 'Wednesday',
                        'Thursday'  => 'Thursday',
                        'Friday'    => 'Friday',
                        default     => $state,
                    }),

                // 3. Shows Start and End Times
                TextColumn::make('start_time')->label('Start')->time('h:i A'),
                TextColumn::make('end_time')->label('End')->time('h:i A'),

                // 4. Shows Max Slots
                TextColumn::make('max_slots')->label('Limit'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('day_of_week')
                    ->options([
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                    ]),
                Tables\Filters\SelectFilter::make('purpose_id')
                    ->relationship('purpose', 'purpose_name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTimeAvailabilities::route('/'),
            'create' => Pages\CreateTimeAvailability::route('/create'),
            'edit' => Pages\EditTimeAvailability::route('/{record}/edit'),
        ];
    }
}
