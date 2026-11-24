<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function showFirstLogin(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->role === 'owner' && $user->status && $user->company?->hasActiveSubscription(), 403);

        if (! $user->must_change_password) {
            return redirect('/client');
        }

        return view('filament.client.first-login');
    }

    public function updateFirstLogin(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->role === 'owner' && $user->status && $user->company?->hasActiveSubscription() && $user->must_change_password, 403);

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', PasswordRule::min(8)],
        ]);

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
            'remember_token' => \Illuminate\Support\Str::random(60),
        ])->save();

        $request->session()->regenerate();
        $request->session()->put('password_hash_client', $user->getAuthPassword());

        return redirect('/client')->with('status', 'Your password has been updated successfully.');
    }
}
