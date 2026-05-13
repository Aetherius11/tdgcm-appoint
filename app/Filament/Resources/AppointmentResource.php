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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Carbon\Carbon;


// Add this

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationGroup = 'Operations'; // Group it for better UI

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 1;

    
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

                TextColumn::make('appointment_date')
                    ->label('DATE')
                    ->date('M d, Y'),

                TextColumn::make('timeAvailability.time_range')
                    ->label('TIME SLOT')
                    ->placeholder('N/A'),

                TextColumn::make('window.name')
                    ->label('WINDOW')
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'transferred' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                // Transaction History showing Call and End time with icons
                TextColumn::make('transaction_history')
                    ->label('TRANSACTION HISTORY')
                    ->html()
                    ->state(function ($record) {
                        if (!$record->called_at) return '<span class="text-gray-400">No activity</span>';
                        
                        $call = $record->called_at->format('h:i A');
                        $end = $record->serve_at ? $record->serve_at->format('h:i A') : '--';
                        
                        return "
                            <div class='text-xs'>
                                <span class='text-danger-600'>📊 $call</span><br>
                                <span class='text-success-600'>▶ $end</span>
                            </div>
                        ";
                    }),

                // Calculation of the duration
                TextColumn::make('transaction_time')
                    ->label('TRANSACTION TIME')
                    ->state(function ($record) {
                        if (!$record->called_at || !$record->serve_at) return '--';
                        return $record->called_at->diffAsCarbonInterval($record->serve_at)->forHumans(['short' => true]);
                    }),
            ])   
            ->filters([
                // 1. THE CALENDAR DATE FILTER (Add this one)
                Tables\Filters\Filter::make('appointment_date')
                    ->form([
                        Forms\Components\DatePicker::make('date')
                            ->label('SELECT DATE'),
                    ])
                    // Add the backslash before Illuminate in both places
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query->when(
                            $data['date'],
                            fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('appointment_date', $date),
                        );
                    }),

                // 2. Filter by Department
                Tables\Filters\SelectFilter::make('principal')
                    ->relationship('principal', 'name')
                    ->label('Filter by Department'),

                // 3. Filter by Purpose
                Tables\Filters\SelectFilter::make('purpose')
                    ->relationship('purpose', 'purpose_name')
                    ->label('Filter by Purpose'),

                // 4. Filter by Status
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed', // Updated to match your badge logic
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
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
