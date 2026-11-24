<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Auth\AuthenticationException;

class PortalAuthenticateSession extends AuthenticateSession
{
    public function handle($request, \Closure $next)
    {
        if (! $request->is('admin', 'admin/*', 'client', 'client/*', 'agent', 'agent/*', 'broadcasting/auth')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    protected function redirectTo($request): ?string
    {
        return match ($this->auth->getDefaultDriver()) {
            'admin' => '/admin/login',
            'client' => '/client/login',
            'agent' => '/agent/login',
            default => '/',
        };
    }

    protected function logout($request)
    {
        $guard = $this->auth->getDefaultDriver();
        $this->guard()->logoutCurrentDevice();
        $request->session()->forget('password_hash_' . $guard);

        throw new AuthenticationException(
            'Unauthenticated.', [$guard], $this->redirectTo($request)
        );
    }
}
