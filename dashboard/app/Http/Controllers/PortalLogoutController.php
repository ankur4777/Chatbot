<?php

namespace App\Http\Controllers;

use Filament\Auth\Http\Controllers\LogoutController;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Filament\Facades\Filament;

class PortalLogoutController extends LogoutController
{
    public function __invoke(): LogoutResponse
    {
        $guard = Filament::getAuthGuard();
        Filament::auth()->logout();

        // Never flush the shared session: the other guards remain signed in.
        session()->forget('password_hash_' . $guard);
        if ($guard === 'agent') {
            session()->forget('impersonator_id');
        }

        return app(LogoutResponse::class);
    }
}
