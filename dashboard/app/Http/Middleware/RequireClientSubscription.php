<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireClientSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('client', 'client/*')) {
            return $next($request);
        }

        $user = $request->user('client');

        // Unauthenticated requests are handled by the existing authentication middleware.
        if (! $user) {
            return $next($request);
        }

        if ($request->routeIs('filament.client.auth.logout')) {
            return $next($request);
        }

        abort_unless($user->role === 'owner' && $user->status && $user->company?->status, 403);

        if ($user->company->hasActiveSubscription()) {
            return $next($request);
        }

        // Overview and mandatory password changes are the only available screens.
        // Livewire replays this middleware against the signed original page URL.
        if ($request->is('client') && $request->isMethod('GET')) {
            return $next($request);
        }

        if ($request->routeIs('client.password.first-login', 'client.password.first-login.show', 'filament.client.auth.login')) {
            return $next($request);
        }

        if ($request->headers->has('X-Livewire') || $request->expectsJson() || ! $request->isMethod('GET')) {
            abort(403, 'Your subscription is not active. Contact your administrator to restore access.');
        }

        return redirect('/client');
    }
}
