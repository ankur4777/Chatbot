<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agent Login | Shabdd Workspace</title>
    @include('filament.auth.partials.login-theme')
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body.shabdd-agent-login {
            margin: 0;
            background: #f8f9fc;
            color: #20283b;
            font-family: var(--shabdd-font-sans);
            font-size: .875rem;
            font-weight: 400;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        .shabdd-agent-login .fi-simple-main { min-width: 0; }
        .shabdd-agent-login .fi-simple-page { width: 100%; }
        .shabdd-agent-login .fi-simple-header {
            display: flex;
            flex-direction: column;
            margin: 0 0 26px;
        }
        .shabdd-agent-login .fi-simple-header-heading { margin: 0; }
        .shabdd-agent-login .fi-simple-header-subheading { margin: 0; }
        .shabdd-agent-login .agent-login-form { display: grid; gap: 19px; }
        .shabdd-agent-login .agent-field { display: grid; gap: 7px; min-width: 0; }
        .shabdd-agent-login .agent-field > label {
            color: #303a4d;
            font-size: .8125rem;
            font-weight: 600;
        }
        .shabdd-agent-login .fi-input-wrp {
            display: flex;
            align-items: center;
            min-width: 0;
        }
        .shabdd-agent-login .fi-input {
            background: transparent;
            border: 0;
            border-radius: 10px;
            outline: 0;
            width: 100%;
            min-width: 0;
            padding: 12px 14px;
            font: inherit;
            line-height: 1.5;
        }
        .shabdd-agent-login .fi-input::placeholder { color: #8791a2; }
        .shabdd-agent-login .agent-password-toggle {
            display: grid;
            place-items: center;
            flex: 0 0 42px;
            align-self: stretch;
            background: transparent;
            border: 0;
            border-radius: 8px;
            color: #687386;
            cursor: pointer;
            padding: 0;
        }
        .shabdd-agent-login .agent-password-toggle:hover { color: #303a4d; }
        .shabdd-agent-login .agent-password-toggle svg { width: 18px; height: 18px; }
        .shabdd-agent-login [hidden] { display: none !important; }
        .shabdd-agent-login .agent-login-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .shabdd-agent-login .agent-remember {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            cursor: pointer;
            font-size: .8125rem;
            font-weight: 500;
        }
        .shabdd-agent-login .agent-remember input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: #f14b55;
        }
        .shabdd-agent-login .fi-link { font-size: .8125rem; text-decoration: none; }
        .shabdd-agent-login .fi-link:hover { text-decoration: underline; }
        .shabdd-agent-login .fi-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            cursor: pointer;
            padding: 12px 18px;
            font: inherit;
            font-weight: 600;
            line-height: 1.4;
            transition: background .15s ease;
        }
        .shabdd-agent-login button:focus-visible,
        .shabdd-agent-login a:focus-visible,
        .shabdd-agent-login .agent-remember input:focus-visible {
            outline: 2px solid #e9434b;
            outline-offset: 3px;
        }
        .shabdd-agent-login .agent-login-alert {
            border: 1px solid;
            border-radius: 10px;
            padding: 12px 14px;
            margin: 0 0 18px;
            font-size: .8125rem;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }
        .shabdd-agent-login .agent-login-alert.is-error { background: #fff1f0; border-color: #ffb7b1; color: #b42318; }
        .shabdd-agent-login .agent-login-alert.is-success { background: #edf8f1; border-color: #b6e4c9; color: #166534; }
        @media (max-width: 960px) {
            .shabdd-agent-login .fi-simple-layout { grid-template-rows: auto 1fr; }
            .shabdd-agent-login .fi-simple-layout > .fi-simple-main-ctn { align-items: start; }
        }
        @media (max-width: 560px) {
            .shabdd-agent-login .fi-input { font-size: 1rem; }
            .shabdd-agent-login .agent-login-form { gap: 16px; }
        }
    </style>
</head>
<body class="shabdd-agent-login">
    <div class="fi-simple-layout">
        @include('filament.auth.partials.login-brand', ['loginPortal' => 'agent'])

        <main class="fi-simple-main-ctn">
            <div class="fi-simple-main">
                <section class="fi-simple-page" aria-labelledby="agent-login-heading">
                    <header class="fi-simple-header">
                        <h1 id="agent-login-heading" class="fi-simple-header-heading">Agent Login</h1>
                        <p class="fi-simple-header-subheading">Sign in to your workspace to manage live support chats.</p>
                    </header>

                    @if (session('status'))
                        <div class="agent-login-alert is-success" role="status">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="agent-login-alert is-error" role="alert">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form class="agent-login-form" method="POST" action="{{ route('agent.login.store') }}">
                        @csrf

                        <div class="agent-field">
                            <label for="email">Email address</label>
                            <div class="fi-input-wrp">
                                <input class="fi-input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email" autocomplete="username" required autofocus>
                            </div>
                        </div>

                        <div class="agent-field">
                            <label for="password">Password</label>
                            <div class="fi-input-wrp">
                                <input class="fi-input" id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
                                <button class="agent-password-toggle" type="button" aria-label="Show password" aria-controls="password" aria-pressed="false" data-password-toggle hidden>
                                    <svg data-password-show viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg data-password-hide viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9 5.4A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a15 15 0 0 1-2.1 2.8M6.1 6.1A15.8 15.8 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 4.3-.9"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="agent-login-options">
                            <label class="agent-remember">
                                <input name="remember" type="checkbox" value="1" @checked(old('remember'))>
                                Remember me
                            </label>
                            <a class="fi-link" href="{{ route('agent.password.request') }}">Forgot password?</a>
                        </div>

                        <button class="fi-btn fi-color-primary" type="submit">Sign in</button>
                    </form>
                </section>
            </div>
        </main>
    </div>
    <script>
        (() => {
            const password = document.getElementById('password');
            const toggle = document.querySelector('[data-password-toggle]');
            if (!password || !toggle) return;
            toggle.hidden = false;
            toggle.addEventListener('click', () => {
                const visible = password.type === 'password';
                password.type = visible ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', String(visible));
                toggle.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
                toggle.querySelector('[data-password-show]').toggleAttribute('hidden', visible);
                toggle.querySelector('[data-password-hide]').toggleAttribute('hidden', !visible);
            });
        })();
    </script>
</body>
</html>
