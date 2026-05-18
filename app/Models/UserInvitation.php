<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserInvitation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'invited_by_name',
        'invited_by_email',
        'team_id',
        'roles',
        'expires_at',
        'accepted_at',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'roles' => 'array',
        ];
    }

    /**
     * Get the user that accepted this invitation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team the invitation is for.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Check if the invitation has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the invitation has been accepted.
     */
    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    /**
     * Mark the invitation as accepted.
     */
    public function markAsAccepted(User $user): void
    {
        $this->update([
            'accepted_at' => now(),
            'user_id' => $user->id,
        ]);
    }

    /**
     * Generate a new invitation token.
     */
    public static function generateToken(): string
    {
        return hash('sha256', Str::random(40));
    }

    /**
     * Find a valid invitation by token.
     */
    public static function findByToken(string $token): ?self
    {
        return static::where('token', $token)
            ->where('expires_at', '>', now())
            ->whereNull('accepted_at') // <--- THIS is what is blocking you now
            ->first();
    }

    /**
     * Create a new invitation.
     */
    public static function createInvitation(
        string $email,
        ?int $teamId = null,
        ?string $invitedByName = null,
        ?string $invitedByEmail = null,
        int $expiresInDays = 7,
        ?array $roles = null,
    ): self {
        static::where('email', $email)
            ->whereNull('accepted_at')
            ->delete();

        return static::create([
            'email'            => $email,
            'token'            => self::generateToken(),
            'team_id'          => $teamId,
            'roles'            => $roles ?? ['staff'],
            'invited_by_name'  => $invitedByName,
            'invited_by_email' => $invitedByEmail,
            'expires_at'       => now()->addDay(),
        ]);
    }
}
