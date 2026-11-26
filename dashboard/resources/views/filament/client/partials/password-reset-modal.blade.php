@if (auth()->user()?->role === 'owner' && auth()->user()?->must_change_password)
    <div class="client-first-password-backdrop" role="dialog" aria-modal="true" aria-labelledby="client-first-password-title">
        <div class="client-first-password-modal">
            <div class="client-first-password-icon">🔐</div>
            <p class="client-first-password-kicker">SECURITY CHECK</p>
            <h2 id="client-first-password-title">Set your new password</h2>
            <p class="client-first-password-copy">Please change the temporary password before continuing to your client dashboard.</p>

            @if ($errors->any())
                <div class="client-first-password-errors">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('client.password.first-login') }}" class="client-first-password-form">
                @csrf
                <label>
                    Current password
                    <input type="password" name="current_password" required autocomplete="current-password">
                </label>
                <label>
                    New password
                    <input type="password" name="password" minlength="8" required autocomplete="new-password">
                </label>
                <label>
                    Confirm new password
                    <input type="password" name="password_confirmation" minlength="8" required autocomplete="new-password">
                </label>
                <button type="submit">Update password</button>
            </form>
            <form method="POST" action="{{ route('filament.client.auth.logout') }}" class="first-login-logout">
                @csrf
                <button type="submit">Log out</button>
            </form>
        </div>
    </div>

    <style>
        .client-first-password-backdrop { align-items: center; background: rgba(9, 13, 27, .62); display: flex; inset: 0; justify-content: center; padding: 20px; position: fixed; z-index: 9999; }
        .client-first-password-modal { background: #fff; border-radius: 20px; box-shadow: 0 24px 70px rgba(9, 13, 27, .28); max-width: 440px; padding: 30px; width: 100%; }
        .client-first-password-icon { align-items: center; background: #fff0f1; border-radius: 14px; display: flex; font-size: 24px; height: 48px; justify-content: center; width: 48px; }
        .client-first-password-kicker { color: #e33d46; font-size: 11px; font-weight: 700; letter-spacing: .12em; margin: 18px 0 6px; }
        .client-first-password-modal h2 { color: #20283b; font-size: 25px; margin: 0; }
        .client-first-password-copy { color: #687386; font-size: 14px; line-height: 1.55; margin: 8px 0 20px; }
        .client-first-password-form { display: grid; gap: 14px; }
        .client-first-password-form label { color: #20283b; display: grid; font-size: 13px; font-weight: 700; gap: 6px; }
        .client-first-password-form input { border: 1px solid #dfe3eb; border-radius: 9px; padding: 10px 12px; width: 100%; }
        .client-first-password-form button { background: #e9493e; border: 0; border-radius: 9px; color: #fff; cursor: pointer; font-weight: 700; margin-top: 4px; padding: 12px 16px; }
        .client-first-password-errors { background: #fff1f0; border: 1px solid #ffb7b1; border-radius: 8px; color: #b42318; font-size: 13px; margin-bottom: 14px; padding: 10px 12px; }
    </style>
@endif
