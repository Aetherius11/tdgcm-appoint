<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictGuardRole
{
    // Always allowed regardless of role (public pages + auth-flow routes)
    private const ALWAYS_ALLOW = [
        '',                     // /
        'logout',
        'user',                 // Jetstream profile / 2FA management
        'two-factor-challenge',
        'invite',               // invitation acceptance + 2FA setup
        'queue-display',
        'tv-display',
        'api',                  // all /api/* endpoints
        'appointment',          // /appointment/{id}
        'submit-appointment',
        'up',                   // health check
        'livewire',             // Livewire internal endpoints
    ];

    private const GUARD_ALLOWED = [
        'appointments-list',
        'dashboard',
        'qr-scanner',
        'qr-scan',
    ];

    private const STAFF_ALLOWED = [
        'WindowStaff',
        'window',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Unauthenticated — let the auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Admins are unrestricted
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        $path = ltrim($request->path(), '/');

        // Always allow auth-flow and public paths for any logged-in user
        foreach (self::ALWAYS_ALLOW as $allowed) {
            if ($path === $allowed || str_starts_with($path, "{$allowed}/")) {
                return $next($request);
            }
        }

        // Guards
        if ($user->hasRole('guard')) {
            foreach (self::GUARD_ALLOWED as $allowed) {
                if ($path === $allowed || str_starts_with($path, "{$allowed}/")) {
                    return $next($request);
                }
            }
            return redirect()->route('appointments.index');
        }

        // Window Staff
        if ($user->hasRole('staff')) {
            foreach (self::STAFF_ALLOWED as $allowed) {
                if ($path === $allowed || str_starts_with($path, "{$allowed}/")) {
                    return $next($request);
                }
            }
            return redirect()->route('Window.Staff');
        }

        // No recognized role — deny access
        return redirect('/login');
    }
}
