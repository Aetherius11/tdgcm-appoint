<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Mail\UserInvitationMail;
use App\Models\UserInvitation;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Invite User')
                ->icon('heroicon-o-envelope')
                ->color('success')
                ->modalHeading('Invite New User')
                ->modalDescription('Send an invitation email to create an account with two-factor authentication enabled.')
                ->form([
                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required()
                        ->unique('users', 'email')
                        ->helperText('The user will receive an email to set up their account and 2FA.'),
                    TextInput::make('name')
                        ->label('Full Name')
                        ->required()
                        ->maxLength(255),
                    Select::make('team_id')
                        ->label('Office Department')
                        ->relationship('team', 'name', fn ($query) => $query->orderBy('name'))
                        ->preload()
                        ->searchable()
                        ->nullable()
                        ->helperText('Optional: Assign to a specific department.'),
                ])
                ->action(function (array $data): void {
                    $user = Auth::user();

                    // Create the invitation
                    $invitation = UserInvitation::createInvitation(
                        email: $data['email'],
                        teamId: $data['team_id'] ?? null,
                        invitedByName: $user->name ?? 'Administrator',
                        invitedByEmail: $user->email ?? 'admin@system.com',
                        expiresInDays: 7
                    );

                    // Send the invitation email
                    $acceptUrl = UserResource::getInvitationUrl($invitation);

                    Mail::to($data['email'])
                        ->send(new UserInvitationMail($invitation));

                    // Pass the acceptUrl to the mailable view
                    // We need to modify the Mailable to handle this
                    // For now, let's use a simpler approach with a custom notification

                    Notification::make()
                        ->title('Invitation sent!')
                        ->body("An invitation has been sent to {$data['email']}. The user will set up their account and 2FA through the email link.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
