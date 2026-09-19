<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agent Login</title>
    <style>
        * { box-sizing: border-box; }

        body {
            align-items: center;
            background:
                radial-gradient(circle at 20% 15%, rgba(59, 130, 246, .10), transparent 30%),
                linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
            color: #0f172a;
            display: flex;
            font-family: Arial, Helvetica, sans-serif;
            justify-content: center;
            margin: 0;
            min-height: 100vh;
            padding: 24px;
        }

        .card {
            background: rgba(255, 255, 255, .96);
            border: 1px solid #dbe3ef;
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, .10);
            padding: 32px;
            width: min(440px, 100%);
        }

        h1 {
            font-size: 32px;
            line-height: 1.1;
            margin: 0 0 8px;
        }

        p {
            color: #475569;
            font-size: 16px;
            margin: 0 0 26px;
        }

        label {
            color: #111827;
            display: block;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        input {
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            color: #0f172a;
            font: inherit;
            margin-bottom: 16px;
            outline: none;
            padding: 12px 14px;
            transition: border-color .15s ease, box-shadow .15s ease;
            width: 100%;
        }

        input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .14);
        }

        button {
            background: #2563eb;
            border: 0;
            border-radius: 10px;
            box-shadow: 0 10px 18px rgba(37, 99, 235, .18);
            color: #fff;
            cursor: pointer;
            font: inherit;
            font-weight: 700;
            padding: 12px 16px;
            transition: background .15s ease, transform .15s ease;
            width: 100%;
        }

        button:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .errors,
        .status {
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 16px;
            padding: 12px 14px;
        }

        .errors {
            background: #fee2e2;
            color: #991b1b;
        }

        .status {
            background: #dcfce7;
            color: #166534;
        }

        .remember {
            align-items: center;
            color: #111827;
            display: flex;
            gap: 9px;
            margin-bottom: 18px;
        }

        .remember input {
            accent-color: #2563eb;
            height: 16px;
            margin: 0;
            width: 16px;
        }

        .links {
            display: flex;
            justify-content: flex-end;
            margin: -8px 0 18px;
        }

        .links a {
            color: #2563eb;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 520px) {
            body {
                align-items: flex-start;
                padding: 20px 14px;
            }

            .card {
                border-radius: 14px;
                padding: 24px 18px;
            }

            h1 {
                font-size: 28px;
            }
        }
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
