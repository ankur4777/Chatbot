<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SelectPortalGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = match (true) {
            $request->is('admin', 'admin/*') => 'admin',
            $request->is('client', 'client/*') => 'client',
            $request->is('agent', 'agent/*', 'broadcasting/auth') => 'agent',
            default => null,
        };

        if ($guard !== null) {
            Auth::shouldUse($guard);
        }

        return $next($request);
    }
}
