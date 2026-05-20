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
                Forms\Components\TimePicker::make('start_time')->required(),
                Forms\Components\TimePicker::make('end_time')->required(),
                Forms\Components\TextInput::make('max_slots')->numeric()->default(5),
            ]);
    }


    // app/Filament/Resources/TimeAvailabilityResource.php

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Clean display of the text day column
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Day')
                    ->sortable(),

                // 3. Shows Start and End Times
                Tables\Columns\TextColumn::make('start_time')->label('Start')->time('h:i A'),
                Tables\Columns\TextColumn::make('end_time')->label('End')->time('h:i A'),

                // 4. Shows Max Slots
                Tables\Columns\TextColumn::make('max_slots')->label('Limit'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('day_of_week')
                    ->options([
                        'Monday'    => 'Monday',    // 👈 Fixed keys from integers to matching strings
                        'Tuesday'   => 'Tuesday',
                        'Wednesday' => 'Wednesday',
                        'Thursday'  => 'Thursday',
                        'Friday'    => 'Friday',
                    ]),

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
