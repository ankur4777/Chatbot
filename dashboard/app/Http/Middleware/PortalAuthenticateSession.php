<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Auth\AuthenticationException;

class PortalAuthenticateSession extends AuthenticateSession
{
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
