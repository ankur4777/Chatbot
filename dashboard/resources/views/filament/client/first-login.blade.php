<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set your new password | Client Workspace</title>
    <style>
        body { margin: 0; font-family: system-ui, sans-serif; background: #f4f5f8; }
        *, *::before, *::after { box-sizing: border-box; }
        .client-first-password-backdrop { overflow-y: auto; }
        .client-first-password-modal { margin: auto; }
        .first-login-logout { margin-top: 14px; text-align: center; }
        .first-login-logout button { border: 0; background: transparent; color: #687386; cursor: pointer; text-decoration: underline; }
    </style>
</head>
<body>
    @include('filament.client.partials.password-reset-modal')
</body>
</html>
