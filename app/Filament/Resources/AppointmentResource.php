<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    // This puts the resource into the "System Maintenance" group
    protected static ?string $navigationGroup = 'System Maintenance';
    
    // Use this to change the icon (e.g., a building icon)
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('appoint_id')
                    ->label('Appoint No')
                    ->required(),

                Select::make('applicant_id')
                    ->relationship('applicant', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->required(),

                Select::make('principal_id')
                    ->label('Principal')
                    ->relationship('principal', 'name')
                    ->live() // CRITICAL: This makes the form refresh when the principal changes
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('purpose_id', null)) // Clear purpose if principal changes
                    ->required(),

                Select::make('purpose_id')
                    ->label('Purpose')
                    ->placeholder(fn (Get $get): string => empty($get('principal_id')) ? 'First select a principal' : 'Select a purpose')
                    ->options(function (Get $get): Collection {
                        $principalId = $get('principal_id');
                        
                        if (!$principalId) {
                            return collect();
                        }

                        // Fetch only purposes belonging to the selected principal
                        return \App\Models\Purpose::query()
                            ->where('principal_id', $principalId)
                            ->pluck('purpose_name', 'id');
                    })
                    ->required()
                    ->key('purpose_select'), // Ensures the component re-renders

                Forms\Components\DatePicker::make('appointment_date')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),

                Forms\Components\Textarea::make('remarks')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Name (From Applicant relationship)
                Tables\Columns\TextColumn::make('applicant.first_name')
                    ->label('Applicant Name')
                    ->formatStateUsing(fn ($record) => "{$record->applicant->first_name} {$record->applicant->last_name}")
                    ->searchable(),

                // 2. Principal
                Tables\Columns\TextColumn::make('principal.name')
                    ->label('Principal')
                    ->sortable(),

                // 3. Purposes
                Tables\Columns\TextColumn::make('purpose.purpose_name')
                    ->label('Purpose'),

                // 4. Date & Time
                Tables\Columns\TextColumn::make('appointment_date')
                    ->label('Schedule')
                    ->dateTime('M d, Y') // You can concatenate time if stored separately
                    ->sortable(),

                // 5. Appoint ID
                Tables\Columns\TextColumn::make('appoint_id')
                    ->label('Appointment ID')
                    ->searchable(),

                // 6. Status
                Tables\Columns\TextColumn::make('status')
                    ->badge() // Makes it look professional
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
