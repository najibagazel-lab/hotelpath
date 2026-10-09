<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/login-hotelpath.css') }}?v=2"></head>
<body class="hotelpath-login-body">
    <main class="login-stage">
        <section class="login-frame">
            <a class="frame-brand" href="{{ route('login') }}" aria-label="{{ config('app.name') }}">{{ \Illuminate\Support\Str::before(config('app.name'), 'Path') }}<b>{{ \Illuminate\Support\Str::after(config('app.name'), 'Hotel') }}</b></a>
            <div class="login-outline">
                <section class="login-form-panel">
                    <p class="form-eyebrow">HOTEL CONTRACT MANAGEMENT</p>
                    <h1>Login</h1>
                    <p class="form-intro">Access your hotel contract workspace.</p>
                    <form method="POST" action="{{ route('login.store') }}" novalidate>
                        @csrf
                        @error('username')<p class="login-error" role="alert">{{ $message }}</p>@enderror
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="Enter your username" autocomplete="username" required autofocus aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}">
                        <label for="password">Password</label>
                        <div class="password-field"><input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required><button type="button" class="password-toggle" aria-controls="password" aria-pressed="false" aria-label="Show password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.8"/></svg></button></div>
                        <label class="remember-row"><input type="checkbox"><span>Remember me</span></label>
                        <button class="login-button" type="submit">Login</button>
                    </form>
                </section>
                <section class="hotel-illustration" aria-label="{{ config('app.name') }} hotel contract illustration">
                    @php echo file_get_contents(public_path('images/login-scene.svg')); @endphp
                </section>
            </div>
            <footer>{{ config('app.name') }} <span>·</span> Hotel contract tracking</footer>
        </section>
    </main>
    <script>document.querySelector('.password-toggle')?.addEventListener('click', function () { const password = this.closest('.password-field').querySelector('input'); const visible = password.type === 'text'; password.type = visible ? 'password' : 'text'; this.setAttribute('aria-pressed', String(!visible)); this.setAttribute('aria-label', visible ? 'Show password' : 'Hide password'); });</script>
</body>
</html>
