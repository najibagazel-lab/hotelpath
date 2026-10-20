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

        * { box-sizing: border-box; }

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
            padding: clamp(28px, 7vh, 72px) clamp(24px, 5vw, 80px);
        }

        .login-frame {
            width: min(1320px, 100%);
            position: relative;
            padding: 0;
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

        .frame-brand b { color: #2dbe9e; }

        .login-outline {
            display: grid;
            grid-template-columns: minmax(420px, 42%) minmax(0, 58%);
            min-height: min(720px, calc(100dvh - 120px));
            overflow: hidden;
            background: #fff;
            border: 0;
            border-radius: 28px;
            box-shadow: 0 28px 65px rgba(26, 53, 81, .16);
        }

        .login-form-panel {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            order: 1;
            padding: clamp(50px, 8vh, 92px) clamp(44px, 5vw, 76px);
            background: #fff;
        }

        .agency-logo {
            display: block;
            width: 112px;
            height: auto;
            margin: 0 0 25px;
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
            font: 900 clamp(42px, 3.2vw, 52px)/1.03 "Nunito", sans-serif;
            letter-spacing: -1.8px;
        }

        .welcome-title { text-align: left; }

        .form-intro {
            margin: 12px 0 30px;
            color: var(--muted);
            font-size: 16px;
            text-align: left;
        }

        .login-form-panel form {
            display: grid;
            gap: 11px;
        }

        .login-form-panel label:not(.remember-row) {
            margin-top: 6px;
            color: var(--navy);
            font: 800 13px "Nunito", sans-serif;
        }

        .login-form-panel input[type="text"],
        .password-field input {
            width: 100%;
            height: 55px;
            padding: 0 16px;
            color: var(--navy);
            background: #f4f7fc;
            border: 1px solid #d4e0ef;
            border-radius: 12px;
            outline: none;
            font: 600 16px "Hanken Grotesk", sans-serif;
        }

        .login-form-panel input:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(27, 122, 107, .14);
        }

        .password-field { position: relative; }

        .password-field input { padding-right: 48px; }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 14px;
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
            font-size: 13px;
        }

        .remember-row input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: var(--teal);
        }

        .login-button {
            height: 56px;
            border: 0;
            border-radius: 12px;
            color: #fff;
            background: var(--teal);
            box-shadow: 0 10px 20px rgba(27, 122, 107, .24);
            font: 800 17px "Nunito", sans-serif;
            cursor: pointer;
        }

        .login-button:hover { background: var(--teal-dark); }

        .login-error {
            margin: 0;
            color: #b42318;
            font-size: 12px;
        }

        .hotel-illustration {
            position: relative;
            z-index: 1;
            display: grid;
            place-items: center;
            order: 2;
            min-width: 0;
            overflow: hidden;
            background: #dcebf1;
            pointer-events: none;
        }

        .hotel-illustration > svg {
            position: relative !important;
            display: block !important;
            width: auto !important;
            max-width: 100% !important;
            height: 100% !important;
            max-height: 100% !important;
        }

        .login-frame footer {
            display: none;
        }

        @media (min-width: 1101px) {
            html, body { height: 100%; overflow: hidden; }

            .login-stage {
                min-height: 0;
                height: 100dvh;
                padding: clamp(16px, 4vh, 56px) clamp(24px, 5vw, 80px);
            }

            .login-frame {
                height: min(720px, calc(100dvh - 32px));
            }

            .login-outline { min-height: 0; height: 100%; }
            .login-form-panel { padding: clamp(24px, 6vh, 76px) clamp(38px, 5vw, 76px); }
        }

        @media (min-width: 1101px) and (max-height: 760px) {
            .agency-logo { width: clamp(88px, 14vh, 112px); margin-bottom: clamp(12px, 2.8vh, 25px); }
            .login-form-panel h1 { font-size: clamp(34px, 5.6vh, 52px); }
            .form-intro { margin: clamp(7px, 1.8vh, 12px) 0 clamp(14px, 3.8vh, 30px); font-size: clamp(13px, 2.2vh, 16px); }
            .login-form-panel form { gap: clamp(5px, 1.5vh, 11px); }
            .login-form-panel label:not(.remember-row) { margin-top: 2px; font-size: clamp(11px, 1.8vh, 13px); }
            .login-form-panel input[type="text"], .password-field input { height: clamp(40px, 7vh, 55px); font-size: clamp(13px, 2.2vh, 16px); }
            .remember-row { margin: 1px 0; font-size: clamp(11px, 1.8vh, 13px); }
            .login-button { height: clamp(42px, 7vh, 56px); font-size: clamp(14px, 2.3vh, 17px); }
        }

        @media (max-width: 1100px) {
            .login-stage { padding: 28px 20px; }

            .login-frame {
                max-width: 560px;
                padding: 0;
            }

            .login-outline {
                display: block;
                min-height: 0;
            }

            .hotel-illustration { display: none; }

            .login-form-panel {
                min-height: min(590px, calc(100dvh - 36px));
                padding: clamp(32px, 7vh, 54px) 42px;
            }

            .agency-logo { margin-bottom: 22px; }

            .login-frame footer { text-align: center; }
        }

        @media (max-width: 480px) {
            .login-stage { padding: 18px; }
            .login-form-panel { padding: clamp(26px, 6vh, 42px) 25px; }
            .login-form-panel h1 { font-size: 38px; }
            .agency-logo { width: 104px; margin-bottom: 22px; }
        }
    </style>
</head>

<body>
    <main class="login-stage">
        <section class="login-frame">
            <div class="login-outline">
                <section class="login-form-panel">
                    <img class="agency-logo" src="{{ asset('images/beslimetravel-logo.png') }}" alt="BTT Beslime Travel">
                    <h1 class="welcome-title">Welcome</h1>
                    <p class="form-intro">Access your hotel contract workspace.</p>

                    <form method="POST" action="/connexion" novalidate>
                        @csrf

                        @error('username')
                            <p class="login-error" role="alert">{{ $message }}</p>
                        @enderror

                        <label for="username">Username</label>
                        <input id="username" type="text" name="username"
                               value="{{ old('username') }}"
                               placeholder="Enter your username"
                               autocomplete="username" required autofocus>

                        <label for="password">Password</label>

                        <div class="password-field">
                            <input id="password" type="password" name="password"
                                   placeholder="Enter your password"
                                   autocomplete="current-password" required>

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
                    @php echo file_get_contents(public_path('images/login-scene.svg')); @endphp
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
