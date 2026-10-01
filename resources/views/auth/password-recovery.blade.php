<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>{{ $reset ? 'Reset password' : 'Forgot password' }} — GiveABU</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7f5; color: #183d2b; margin: 0; }
        main { max-width: 420px; margin: 8vh auto; padding: 32px; background: white; border-radius: 12px; }
        label { display: block; margin: 20px 0 8px; }
        input, button { width: 100%; padding: 12px; box-sizing: border-box; border-radius: 6px; border: 1px solid #ccc; }
        button { margin-top: 24px; background: #064e3b; color: white; cursor: pointer; }
        a { color: #064e3b; } .error { color: #b91c1c; } footer { margin-top: 30px; font-size: 12px; }
    </style>
</head>
<body>
<main>
    <h1>{{ $reset ? 'Reset password' : 'Forgot password' }}</h1>
    <p>{{ $reset ? 'Choose a new password with at least 8 characters.' : 'Enter your account email to receive a password reset link. If you registered with Google, use Sign in with Google.' }}</p>
    @if(session('status')) <p role="status">{{ session('status') }}</p> @endif
    @if($errors->any())
        <div class="error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
    @endif
    <form method="POST" action="{{ $formAction ?? route($admin ? ($reset ? 'password.store' : 'password.email') : ($reset ? 'donor.password.store' : 'donor.password.email')) }}">
        @csrf
        @if($reset) <input type="hidden" name="token" value="{{ $token }}"> @endif
        @if(!$reset || $admin)
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" autocomplete="email" maxlength="255" value="{{ old('email', $email ?? '') }}" required>
        @endif
        @if($reset)
            <label for="password">New password</label>
            <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required>
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
        @endif
        <button type="submit">{{ $reset ? 'Reset password' : 'Send reset link' }}</button>
    </form>
    @if($reset)<p><a href="{{ route($admin ? 'password.request' : 'donor.password.request') }}">Request a new reset link</a></p>@endif
    <p><a href="{{ $admin ? route('admin.login') : url('/') }}">{{ $admin ? 'Back to sign in' : 'Back to website' }}</a></p>
    <footer>© {{ date('Y') }} ABU. All rights reserved. Powered by @@KADICT Hub.</footer>
</main>
</body>
</html>
