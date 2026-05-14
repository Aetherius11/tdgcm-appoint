<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictGuardRole
{
    // Routes guards ARE allowed to access
    private const GUARD_ALLOWED = [
        'dashboard',
        'appointments-list',
        'qr-scanner',
        'qr-scan',
        'queue-display',
        'tv-display',
        'api/time-slots',
        'api/tv-data',
        'api/window-status',
        'api/queue-data',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isGuard()) {
            return $next($request);
        }

        // Guard-only users: only allow their permitted routes

        $path = ltrim($request->path(), '/');

        foreach (self::GUARD_ALLOWED as $allowed) {
            if ($path === $allowed || str_starts_with($path, "{$allowed}/")) {
                return $next($request);
            }
        }

        // Guards that hit a restricted route get sent to the guard dashboard
        return redirect()->route('dashboard');
    }
}
