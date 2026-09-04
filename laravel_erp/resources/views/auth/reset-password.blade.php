<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password | MEMA College ERP</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/system/favicons/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/system/favicons/favicon-16.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --navy: #0A3E50;
            --navy-deep: #052633;
            --amber: #E67E22;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow-x: hidden;
            color: var(--ink);
            background: #f1f5f9;
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .auth-layout {
            min-height: 100vh;
            height: 100dvh;
            display: grid;
            grid-template-columns: minmax(360px, 0.85fr) minmax(460px, 1.15fr);
            overflow: hidden;
        }
        .identity {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: #ffffff;
            background: linear-gradient(145deg, #052633 0%, #0A3E50 65%, #0e4c61 100%);
            overflow: hidden;
        }
        .identity-content {
            width: min(100%, 420px);
            z-index: 1;
        }
        .crest {
            width: 80px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 6px 16px rgba(0, 0, 0, 0.25));
        }
        .identity h1 {
            margin: 18px 0 10px;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #ffffff;
            line-height: 1.15;
        }
        .gold-rule {
            width: 36px;
            height: 3px;
            background: var(--amber);
            border-radius: 2px;
            margin-bottom: 14px;
        }
        .identity p.motto {
            margin: 0;
            color: #f1f5f9;
            font-size: 15px;
            font-weight: 500;
        }
        .auth-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 32px;
            background: #ffffff;
            overflow-y: auto;
        }
        .auth-card {
            width: min(100%, 440px);
            padding: 10px 0;
        }
        .card-header {
            margin-bottom: 22px;
        }
        .auth-card h2 {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }
        .subtitle {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 13.5px;
        }
        .field {
            margin-bottom: 14px;
        }
        .field label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
        }
        .input-shell {
            position: relative;
        }
        .input-shell input {
            width: 100%;
            height: 44px;
            padding: 0 42px 0 42px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            outline: none;
            color: #0f172a;
            background: #ffffff;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .input-shell input:focus {
            border-color: #0A3E50;
            box-shadow: 0 0 0 3px rgba(10, 62, 80, 0.12);
        }
        .field-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 42px;
            height: 44px;
            display: grid;
            place-items: center;
            color: #94a3b8;
            pointer-events: none;
        }
        .field-icon svg, .password-toggle svg {
            width: 18px;
            height: 18px;
        }
        .password-toggle {
            position: absolute;
            right: 6px;
            top: 6px;
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 6px;
            color: #94a3b8;
            background: transparent;
            cursor: pointer;
        }
        .password-toggle:hover {
            color: #0A3E50;
            background: #f1f5f9;
        }
        .policy-info {
            font-size: 12px;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        .login-button {
            width: 100%;
            height: 46px;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 0;
            border-radius: 8px;
            color: #ffffff;
            background: #0A3E50;
            font-size: 14.5px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(10, 62, 80, 0.2);
            transition: background-color 0.15s;
        }
        .login-button:hover {
            background: #083241;
        }
        .error {
            margin: 4px 0 0;
            color: #dc2626;
            font-size: 12px;
            font-weight: 500;
        }
        @media (max-width: 900px) {
            .auth-layout {
                grid-template-columns: 1fr;
                height: auto;
                min-height: 100vh;
                overflow: auto;
            }
            .identity {
                padding: 32px 24px;
            }
            .auth-panel {
                padding: 32px 20px;
            }
        }
    </style>
</head>
<body>
    <main class="auth-layout">
        <section class="identity" aria-label="MEMA College Overview">
            <div class="identity-content">
                <img class="crest" src="{{ asset('images/system/logos/mema-college-mark-192.png') }}" alt="MEMA College Emblem">
                <h1>MEMA College</h1>
                <div class="gold-rule"></div>
                <p class="motto">Learn. Lead. Transform.</p>
            </div>
        </section>

        <section class="auth-panel">
            <div class="auth-card">
                <div class="card-header">
                    <h2>Create New Password</h2>
                    <p class="subtitle">Set your secure new account credentials</p>
                </div>

                @if ($errors->any())
                    <div class="error" role="alert" style="margin-bottom:14px">{{ $errors->first() }}</div>
                @endif

                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="field">
                        <label for="email">Email address</label>
                        <div class="input-shell">
                            <span class="field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            </span>
                            <input id="email" type="email" name="email" value="{{ old('email', $email ?? '') }}" required placeholder="you@example.com" autocomplete="username">
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">New Password</label>
                        <div class="input-shell">
                            <span class="field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            </span>
                            <input id="password" type="password" name="password" required autofocus placeholder="Enter new password">
                            <button class="password-toggle" type="button" aria-label="Show password" aria-controls="password" aria-pressed="false">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.3 12s3.5-6 9.7-6 9.7 6 9.7 6-3.5 6-9.7 6-9.7-6Z"/><circle cx="12" cy="12" r="2.8"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="input-shell">
                            <span class="field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            </span>
                            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Confirm new password">
                        </div>
                    </div>

                    <div class="policy-info">
                        <strong>Security Policy:</strong> Minimum 12 characters with uppercase, lowercase, and a number.
                    </div>

                    <button class="login-button" type="submit">Update Password</button>
                </form>
            </div>
        </section>
    </main>

    <script>
        const toggle = document.querySelector('.password-toggle');
        const password = document.querySelector('#password');
        toggle?.addEventListener('click', () => { 
            const visible = password.type === 'text'; 
            password.type = visible ? 'password' : 'text'; 
            toggle.setAttribute('aria-label', visible ? 'Show password' : 'Hide password'); 
            toggle.setAttribute('aria-pressed', String(!visible)); 
        });
    </script>
</body>
</html>
</html>
