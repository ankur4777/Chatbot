<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Agent Password</title>
    <style>
        * { box-sizing: border-box; }
        body { align-items: center; background: #f6f7fb; display: flex; font-family: Arial, Helvetica, sans-serif; justify-content: center; margin: 0; min-height: 100vh; }
        .card { background: #fff; border: 1px solid #dde3ea; border-radius: 12px; box-shadow: 0 10px 25px rgba(15, 23, 42, .08); padding: 28px; width: min(420px, calc(100vw - 32px)); }
        h1 { margin: 0 0 6px; }
        p { color: #657282; margin: 0 0 22px; }
        label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 6px; }
        input { border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; margin-bottom: 14px; padding: 10px; width: 100%; }
        button { background: #2563eb; border: 0; border-radius: 8px; color: #fff; cursor: pointer; font: inherit; font-weight: 700; padding: 10px 14px; width: 100%; }
        .errors { background: #fee2e2; border-radius: 8px; color: #991b1b; margin-bottom: 14px; padding: 10px; }
        .status { background: #dcfce7; border-radius: 8px; color: #166534; margin-bottom: 14px; padding: 10px; }
        .back { display: block; color: #2563eb; font-size: 14px; font-weight: 700; margin-top: 16px; text-align: center; text-decoration: none; }
        @media (max-width: 575px) {
            body { align-items: flex-start; padding: 16px; }
            .card { padding: 22px; width: 100%; }
        }
    </style>
</head>
<body>
    <form class="card" method="POST" action="{{ route('agent.password.email') }}">
        @csrf

        <h1>Forgot Password</h1>
        <p>Enter your agent email address and we will send a reset link.</p>

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

        <label for="email">Email Address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>

        <button type="submit">Send Reset Link</button>

        <a class="back" href="{{ route('agent.login') }}">Back to Login</a>
    </form>
</body>
</html>
