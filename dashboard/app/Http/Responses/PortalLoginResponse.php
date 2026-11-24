<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class PortalLoginResponse implements LoginResponse
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        // A shared intended URL may belong to another portal.
        return redirect()->to(Filament::getUrl());
    }
}
