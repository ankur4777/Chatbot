<?php

namespace App\Http\Middleware;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;

class AuthenticatePortal extends Authenticate
{
    protected function authenticate($request, array $guards): void
    {
        $panel = Filament::getCurrentOrDefaultPanel();
        $guard = $panel->auth();
        $user = $guard->user();

        if ($user instanceof User && ! $user->canAccessPanel($panel)) {
            // Clear only the rejected portal, including any stale remember cookie.
            // Keep the other portals' authenticated sessions untouched.
            $guard->logoutCurrentDevice();
            $request->session()->forget('password_hash_' . $panel->getAuthGuard());
            $request->session()->flash(
                'portal_login_error_' . $panel->getId(),
                'This account cannot access this portal. Use the correct active account and check its company subscription.'
            );

            $this->unauthenticated($request, [$panel->getAuthGuard()]);
        }

        parent::authenticate($request, $guards);
    }
}
