<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;

class PortalLogin extends Login
{
    public function mount(): void
    {
        $panel = Filament::getCurrentOrDefaultPanel();
        $guard = $panel->auth();
        $user = $guard->user();

        if ($user instanceof User && ! $user->canAccessPanel($panel)) {
            $guard->logoutCurrentDevice();
            session()->forget('password_hash_' . $panel->getAuthGuard());
            session()->flash(
                'portal_login_error_' . $panel->getId(),
                'This account cannot access this portal. Use the correct active account and check its company subscription.'
            );
        } elseif ($guard->check()) {
            // Do not follow a shared intended URL from a different portal.
            $this->redirect($panel->getUrl());

            return;
        }

        parent::mount();

        if ($message = session()->pull('portal_login_error_' . $panel->getId())) {
            $this->addError('data.email', $message);
        }
    }
}
