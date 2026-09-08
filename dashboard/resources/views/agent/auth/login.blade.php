<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agent Login</title>
    <style>
        body { align-items: center; background: #f6f7fb; display: flex; font-family: Arial, Helvetica, sans-serif; justify-content: center; margin: 0; min-height: 100vh; }
        .card { background: #fff; border: 1px solid #dde3ea; border-radius: 12px; box-shadow: 0 10px 25px rgba(15, 23, 42, .08); padding: 28px; width: min(420px, calc(100vw - 32px)); }
        h1 { margin: 0 0 6px; }
        p { color: #657282; margin: 0 0 22px; }
        label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 6px; }
        input { border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; margin-bottom: 14px; padding: 10px; width: 100%; }
        button { background: #2563eb; border: 0; border-radius: 8px; color: #fff; cursor: pointer; font: inherit; font-weight: 700; padding: 10px 14px; width: 100%; }
        .errors { background: #fee2e2; border-radius: 8px; color: #991b1b; margin-bottom: 14px; padding: 10px; }
        .status { background: #dcfce7; border-radius: 8px; color: #166534; margin-bottom: 14px; padding: 10px; }
        .remember { align-items: center; display: flex; gap: 8px; margin-bottom: 16px; }
        .remember input { margin: 0; width: auto; }
        .links { display: flex; justify-content: flex-end; margin: -6px 0 16px; }
        .links a { color: #2563eb; font-size: 14px; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
    <form class="card" method="POST" action="{{ route('agent.login.store') }}">
        @csrf

        <h1>Agent Login</h1>
        <p>Sign in to manage live support chats.</p>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required>

        <div class="links">
            <a href="{{ route('agent.password.request') }}">Forgot Password?</a>
        </div>

        <label class="remember">
            <input name="remember" type="checkbox" value="1">
            Remember me
        </label>

        <button type="submit">Sign In</button>
    </form>
</body>
</html>
