<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Principal;
use App\Models\Purpose;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get; // Add this
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Add this

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationGroup = 'Operations'; // Group it for better UI

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 1;

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
                    ->preload()
                    ->required(),

                // 1. Select Main Department
                Select::make('main_principal_id')
                    ->label('Department/Category')
                    ->options(Principal::whereNull('parent_id')->pluck('name', 'id'))
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('principal_id', null)),

                // 2. Select Sub-Department (Only shows if the main one has children)
                Select::make('principal_id')
                    ->label('Sub-Department')
                    ->options(fn (Get $get) => Principal::where('parent_id', $get('main_principal_id'))->pluck('name', 'id')
                    )
                    // Only show if the selected Main Department actually has children
                    ->visible(fn (Get $get) => Principal::where('parent_id', $get('main_principal_id'))->exists()
                    )
                    ->live()
                    ->required(fn (Get $get) => Principal::where('parent_id', $get('main_principal_id'))->exists()
                    ),

                // 3. Select Purpose (Filters based on the Sub-Department selected)
                Select::make('purpose_id')
                    ->label('Purpose')
                    ->options(function (Get $get) {
                        // Fallback logic: if no sub-principal is picked, use the main one
                        $id = $get('principal_id') ?? $get('main_principal_id');

                        if (! $id) {
                            return [];
                        }

                        return Purpose::whereHas('principals', fn ($q) => $q->where('principals.id', $id)
                        )->pluck('purpose_name', 'id');
                    })
                    ->live() // Ensure it updates when selection changes
                    ->required(),

                Forms\Components\DatePicker::make('appointment_date')
                    ->required(),

                Select::make('status')
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
                Tables\Columns\TextColumn::make('applicant.first_name')
                    ->label('Applicant Name')
                    ->formatStateUsing(fn ($record) => "{$record->applicant->first_name} {$record->applicant->last_name}")
                    ->searchable(), // This allows searching by Name

                Tables\Columns\TextColumn::make('principal.name')
                    ->label('Department')
                    ->sortable(),

                Tables\Columns\TextColumn::make('purpose.purpose_name')
                    ->label('Purpose'),

                Tables\Columns\TextColumn::make('appoint_no')
                    ->label('Appointment ID')
                    ->searchable(), // This allows searching by Appointment ID

                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                // 1. Filter by Department
                Tables\Filters\SelectFilter::make('principal')
                    ->relationship('principal', 'name')
                    ->label('Filter by Department'),

                // 2. Filter by Purpose
                Tables\Filters\SelectFilter::make('purpose')
                    ->relationship('purpose', 'purpose_name')
                    ->label('Filter by Purpose'),

                // 3. Filter by Status (Optional but helpful)
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'cancelled' => 'Cancelled',
                    ]),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
