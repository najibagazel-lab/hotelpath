<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HotelPath — Login</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy: #13243d;
            --teal: #1b7a6b;
            --teal-dark: #135c50;
            --background: #f3f6fa;
            --muted: #5f6f86;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-width: 320px;
            min-height: 100vh;
            color: var(--navy);
            background:
                radial-gradient(circle at 94% 8%, #dce6f4 0 88px, transparent 89px),
                radial-gradient(circle at 7% 92%, #d8f1ed 0 90px, transparent 91px),
                var(--background);
            font-family: "Hanken Grotesk", Arial, sans-serif;
        }

        .login-stage {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 40px;
        }

        .login-frame {
            width: min(1240px, 100%);
            position: relative;
            padding-top: 60px;
        }

        .frame-brand {
            position: absolute;
            top: 5px;
            left: 0;
            color: var(--navy);
            font: 900 clamp(27px, 2.4vw, 36px) "Nunito", sans-serif;
            letter-spacing: -1.4px;
            text-decoration: none;
        }

        .frame-brand b {
            color: #2dbe9e;
        }

        .login-outline {
            display: grid;
            grid-template-columns: minmax(390px, 38%) minmax(0, 62%);
            min-height: 528px;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e1eaf2;
            border-radius: 22px;
            box-shadow: 0 22px 46px rgba(26, 53, 81, .12);
        }

        .login-form-panel {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 54px 42px;
            background: #fff;
        }

        .form-eyebrow {
            margin: 0 0 9px;
            color: #087a79;
            font: 800 10px "Nunito", sans-serif;
            letter-spacing: 1.3px;
        }

        .login-form-panel h1 {
            margin: 0;
            color: var(--navy);
            font: 900 38px/1.05 "Nunito", sans-serif;
            letter-spacing: -1px;
        }

        .form-intro {
            margin: 10px 0 25px;
            color: var(--muted);
            font-size: 14px;
        }

        .login-form-panel form {
            display: grid;
            gap: 9px;
        }

        .login-form-panel label:not(.remember-row) {
            margin-top: 5px;
            color: var(--navy);
            font: 800 12px "Nunito", sans-serif;
        }

        .login-form-panel input[type="text"],
        .password-field input {
            width: 100%;
            height: 45px;
            padding: 0 13px;
            color: var(--navy);
            background: #eaf1fb;
            border: 1px solid #cad8e8;
            border-radius: 8px;
            outline: none;
            font: 600 14px "Hanken Grotesk", sans-serif;
        }

        .login-form-panel input:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(27, 122, 107, .14);
        }

        .password-field {
            position: relative;
        }

        .password-field input {
            padding-right: 48px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            width: 28px;
            height: 28px;
            padding: 5px;
            border: 0;
            color: var(--teal);
            background: transparent;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .password-toggle svg {
            display: block;
            width: 100%;
            height: 100%;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 4px 0;
            color: var(--muted);
            font-size: 12px;
        }

        .remember-row input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: var(--teal);
        }

        .login-button {
            height: 46px;
            border: 0;
            border-radius: 9px;
            color: #fff;
            background: var(--teal);
            box-shadow: 0 9px 18px rgba(27, 122, 107, .2);
            font: 800 15px "Nunito", sans-serif;
            cursor: pointer;
        }

        .login-button:hover {
            background: var(--teal-dark);
        }

        .login-error {
            margin: 0;
            color: #b42318;
            font-size: 12px;
        }

        .hotel-illustration {
            position: relative;
            z-index: 1;
            min-width: 0;
            overflow: hidden;
            background: #dcebf1;
        }

        .hotel-illustration img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            pointer-events: none;
        }

        .login-frame footer {
            margin-top: 10px;
            color: #68809c;
            text-align: right;
            font-size: 10px;
        }

        @media (max-width: 1100px) {
            .login-stage {
                padding: 28px 20px;
            }

            .login-frame {
                max-width: 480px;
                padding-top: 54px;
            }

            .login-outline {
                display: block;
                min-height: 0;
            }

            .hotel-illustration {
                display: none;
            }

            .login-form-panel {
                min-height: 500px;
                padding: 48px 38px;
            }

            .login-frame footer {
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .login-stage {
                padding: 18px;
            }

            .login-form-panel {
                padding: 42px 25px;
            }

            .login-form-panel h1 {
                font-size: 34px;
            }
        }
    </style>
</head>

<body>
    <main class="login-stage">
        <section class="login-frame">
            <a class="frame-brand" href="{{ route('login') }}" aria-label="HotelPath">
                Hotel<b>Path</b>
            </a>

            <div class="login-outline">
                <section class="login-form-panel">
                    <p class="form-eyebrow">HOTEL CONTRACT MANAGEMENT</p>
                    <h1>Login</h1>
                    <p class="form-intro">Access your hotel contract workspace.</p>

                    <form method="POST" action="{{ route('login.store') }}" novalidate>
                        @csrf

                        @error('username')
                            <p class="login-error" role="alert">{{ $message }}</p>
                        @enderror

                        <label for="username">Username</label>
                        <input
                            id="username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Enter your username"
                            autocomplete="username"
                            required
                            autofocus
                        >

                        <label for="password">Password</label>

                        <div class="password-field">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >

                            <button type="button" class="password-toggle" aria-label="Show password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                                    <circle cx="12" cy="12" r="2.8"/>
                                </svg>
                            </button>
                        </div>

                        <label class="remember-row">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>

                        <button class="login-button" type="submit">Login</button>
                    </form>
                </section>

                <section class="hotel-illustration" aria-label="Hotel contract illustration">
                    <img src="{{ asset('images/login-scene.svg') }}" alt="">
                </section>
            </div>

            <footer>HotelPath · Hotel contract tracking</footer>
        </section>
    </main>

    <script>
        document.querySelector('.password-toggle')?.addEventListener('click', function () {
            const password = document.getElementById('password');
            const visible = password.type === 'text';

            password.type = visible ? 'password' : 'text';
            this.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
        });
    </script>
</body>
</html>
