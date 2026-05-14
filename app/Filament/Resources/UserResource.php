<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Team;
use App\Models\User;
use App\Models\UserInvitation;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    // This puts the resource into the "User Management" group
    protected static ?string $navigationGroup = 'User Management';

    // Use this to change the icon (e.g., a building icon)
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('email')->email()->required(),

                // Combining Two-Factor fields into one section
                Section::make('Two-Factor Authentication')
                    ->description('Manage security secrets and recovery codes')
                    ->schema([
                        Forms\Components\Textarea::make('two_factor_secret')
                            ->label('Secret Key')
                            ->rows(2),
                        Forms\Components\Textarea::make('two_factor_recovery_codes')
                            ->label('Recovery Codes')
                            ->rows(3),
                    ])->columns(1),

                Forms\Components\DateTimePicker::make('two_factor_confirmed_at'),

                // Changing "Current Team ID" to "Office Department"
                Forms\Components\Select::make('current_team_id')
                    ->label('Office Department')
                    ->relationship('currentTeam', 'name')
                    ->preload()
                    ->searchable(),

                Forms\Components\TextInput::make('profile_photo_path'),
                Forms\Components\DateTimePicker::make('email_verified_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Name Column
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                // 2. Email Column
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                // 3. Office Department (Replaces Current team id)
                Tables\Columns\TextColumn::make('currentTeam.name')
                    ->label('Office Department')
                    ->badge()
                    ->color('info'),
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
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * Get the URL for accepting an invitation.
     */
    public static function getInvitationUrl(UserInvitation $invitation): string
    {
        return url('/invite/'.$invitation->token);
    }
}
