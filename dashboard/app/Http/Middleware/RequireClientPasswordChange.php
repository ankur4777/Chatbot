<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireClientPasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->role !== 'owner' || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('client.password.first-login', 'client.password.first-login.show', 'filament.client.auth.logout')) {
            return $next($request);
        }

        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return redirect()->route('client.password.first-login.show');
        }

        abort(403, 'Change your temporary password before continuing.');
    }
}
