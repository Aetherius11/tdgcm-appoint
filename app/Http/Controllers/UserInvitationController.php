<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\UserInvitation;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class UserInvitationController extends Controller
{
    /**
     * Show the invitation acceptance page.
     */
    public function show(string $token)
    {
        $invitation = UserInvitation::findByToken($token);

        if (! $invitation) {
            return view('invitations.invalid')
                ->with('title', 'Invalid or Expired Invitation')
                ->with('message', 'This invitation link is invalid or has expired. Please contact your administrator to request a new invitation.');
        }

        // Check if user already has an account
        $existingUser = User::where('email', $invitation->email)->first();

        return view('invitations.accept', [
            'invitation' => $invitation,
            'existingUser' => $existingUser,
        ]);
    }

    /**
     * Accept the invitation and create/update user account.
     */
    public function accept(Request $request, string $token)
    {
        $invitation = UserInvitation::findByToken($token);

        if (! $invitation) {
            return view('invitations.invalid')
                ->with('title', 'Invalid or Expired Invitation')
                ->with('message', 'This invitation link is invalid or has expired. Please contact your administrator to request a new invitation.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'enable_2fa' => 'boolean',
        ]);

        // Check if user already exists
        $user = User::where('email', $invitation->email)->first();

        $roles = $invitation->roles ?? ['staff'];

        if ($user) {
            $user->update([
                'name'              => $validated['name'],
                'password'          => Hash::make($validated['password']),
                'email_verified_at' => now(),
                'roles'             => $roles,
                'current_team_id'   => $invitation->team_id ?? $user->current_team_id,
            ]);
        } else {
            $user = User::create([
                'name'              => $validated['name'],
                'email'             => $invitation->email,
                'password'          => Hash::make($validated['password']),
                'email_verified_at' => now(),
                'roles'             => $roles,
                'current_team_id'   => $invitation->team_id,
            ]);
        }

        // Mark invitation as accepted
        $invitation->markAsAccepted($user);

        // Set up two-factor authentication if requested
        if ($validated['enable_2fa'] ?? false) {
            $this->enableTwoFactorAuthentication($user);

            // Log the user in
            Auth::login($user);

            // Redirect to 2FA setup page to scan QR code
            return redirect()->route('invitations.2fa-setup');
        }

        // Log the user in
        Auth::login($user);

        return $this->roleRedirect()->with('success', 'Your account has been set up successfully!');
    }

    /**
     * Show the 2FA setup page with QR code.
     */
    public function show2FASetup()
    {
        $user = Auth::user();

        // If session was lost or user isn't logged in, redirect to home
        if (! $user) {
            return redirect()->route('login');
        }

        // Ensure they actually need to be here
        if (! $user->two_factor_secret || $user->two_factor_confirmed_at) {
            return $this->roleRedirect();
        }

        // Generate QR code
        $provider = app(TwoFactorAuthenticationProvider::class);
        $secretKey = decrypt($user->two_factor_secret);

        $qrCodeUrl = $provider->qrCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );

        // Generate SVG QR code
        $writer = new Writer(
            new RendererStyle(200, 0, 'rgb(0,0,0)', 'rgb(255,255,255)')
        );
        $svg = $writer->writeString($qrCodeUrl);

        return view('invitations.2fa-setup', [
            'user' => $user,
            'secretKey' => $secretKey,
            'qrCodeSvg' => $svg,
        ]);
    }

    /**
     * Confirm 2FA setup.
     */
    public function confirm2FA(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $provider = app(TwoFactorAuthenticationProvider::class);
        $secretKey = decrypt($user->two_factor_secret);

        $isValid = $provider->verifyCode($secretKey, $validated['code']);

        if (! $isValid) {
            return back()
                ->withErrors(['code' => 'Invalid authentication code. Please try again.']);
        }

        // Confirm 2FA
        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $this->roleRedirect()
            ->with('success', 'Two-factor authentication has been enabled successfully!');
    }

    private function roleRedirect(): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        if ($user?->hasRole('admin')) return redirect('/admin');
        if ($user?->hasRole('staff')) return redirect()->route('Window.Staff');
        return redirect()->route('appointments.index');
    }

    /**
     * Enable two-factor authentication for the user.
     */
    protected function enableTwoFactorAuthentication(User $user): void
    {
        $provider = app(TwoFactorAuthenticationProvider::class);

        $user->forceFill([
            'two_factor_secret' => encrypt($provider->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(collect(range(1, 8))->map(function () {
                return sprintf('%s-%s', bin2hex(random_bytes(2)), bin2hex(random_bytes(2)));
            })->all())),
            'two_factor_confirmed_at' => null, // Will be confirmed after scanning QR code
        ])->save();
    }
}
