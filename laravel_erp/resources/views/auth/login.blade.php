<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | MEMA College ERP</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/system/favicons/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/system/favicons/favicon-16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/system/favicons/apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --navy: #0A3E50;
            --navy-deep: #052633;
            --forest: #1E8449;
            --amber: #E67E22;
            --gold: #d7a84f;
            --cream: #f8fafc;
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
        /* Left Brand Showcase */
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
        .identity::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 30%, rgba(230, 126, 34, 0.12) 0%, transparent 60%);
            pointer-events: none;
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
            margin: 0 0 24px;
            color: #f1f5f9;
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 0.02em;
        }
        .feature-pills {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .feature-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12.5px;
            color: #e2e8f0;
        }
        .feature-pill svg {
            width: 16px;
            height: 16px;
            color: #38bdf8;
            flex-shrink: 0;
        }

        /* Right Form Panel */
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
            margin-bottom: 16px;
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
            transition: color 0.15s, background 0.15s;
        }
        .password-toggle:hover {
            color: #0A3E50;
            background: #f1f5f9;
        }
        .error {
            margin: 4px 0 0;
            color: #dc2626;
            font-size: 12px;
            font-weight: 500;
        }
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin: 12px 0 20px;
            font-size: 13px;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            cursor: pointer;
            user-select: none;
        }
        .remember input {
            width: 16px;
            height: 16px;
            accent-color: #0A3E50;
            cursor: pointer;
        }
        .options a {
            color: #0A3E50;
            text-decoration: none;
            font-weight: 600;
        }
        .options a:hover {
            text-decoration: underline;
        }
        .login-button {
            width: 100%;
            height: 46px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            border: 0;
            border-radius: 8px;
            color: #ffffff;
            background: #0A3E50;
            font-size: 14.5px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(10, 62, 80, 0.2);
            transition: background-color 0.15s, box-shadow 0.15s;
        }
        .login-button:hover {
            background: #083241;
            box-shadow: 0 4px 12px rgba(10, 62, 80, 0.28);
        }
        .applicant-callout {
            margin-top: 18px;
            padding: 14px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .applicant-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
        }
        .applicant-row span {
            color: #64748b;
            font-weight: 500;
        }
        .applicant-row a {
            color: #E67E22;
            font-weight: 700;
            text-decoration: none;
        }
        .applicant-row a:hover {
            text-decoration: underline;
        }
        .applicant-links {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 11.5px;
            font-weight: 600;
            border-top: 1px solid #f1f5f9;
            padding-top: 8px;
        }
        .applicant-links a {
            color: #0A3E50;
            text-decoration: none;
        }
        .applicant-links a:hover {
            text-decoration: underline;
        }
        .trust {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 16px;
            color: #94a3b8;
            font-size: 11.5px;
        }
        .trust svg {
            width: 14px;
            height: 14px;
            color: #10b981;
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
            .feature-pills {
                display: none;
            }
        }
    </style>
</head>
<body>
    <main class="auth-layout">
        {{-- Left Brand Identity Showcase --}}
        <section class="identity" aria-label="MEMA College Overview">
            <div class="identity-content">
                <img class="crest" src="{{ asset('images/system/logos/mema-college-mark-192.png') }}" alt="MEMA College Emblem">
                <h1>MEMA College</h1>
                <div class="gold-rule"></div>
                <p class="motto">Learn. Lead. Transform.</p>
                
                <div class="feature-pills">
                    <div class="feature-pill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>Unified Institutional ERP Platform</span>
                    </div>
                    <div class="feature-pill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10M6 10h10"/></svg>
                        <span>Admissions, Academic Registry &amp; LMS</span>
                    </div>
                    <div class="feature-pill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        <span>Automated Billing &amp; M-Pesa Integration</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Right Authentication Panel --}}
        <section class="auth-panel">
            <div class="auth-card">
                <div class="card-header">
                    <h2>Sign In</h2>
                    <p class="subtitle">Welcome back! Enter your institutional credentials to access your portal</p>
                </div>

                @if (session('success'))
                    <div class="alert" style="padding:10px 14px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:6px;margin-bottom:16px;font-size:13px;font-weight:500;">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('info'))
                    <div class="alert" style="padding:10px 14px;background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;border-radius:6px;margin-bottom:16px;font-size:13px;font-weight:500;">
                        {{ session('info') }}
                    </div>
                @endif

                <form method="post" action="{{ route('login.store') }}" data-processing-message="Signing you in securely…">
                    @csrf

                    <div class="field">
                        <label for="email">MEMA Email or Username</label>
                        <div class="input-shell">
                            <span class="field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            </span>
                            <input id="email" type="text" name="email" value="{{ old('email', 'admin@mema.ac.ke') }}" autocomplete="username" required autofocus placeholder="name@mema.ac.ke or username">
                        </div>
                        @error('email')<div class="error" role="alert">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-shell">
                            <span class="field-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            </span>
                            <input id="password" type="password" name="password" value="password" autocomplete="current-password" required placeholder="Enter password">
                            <button class="password-toggle" type="button" aria-label="Show password" aria-controls="password" aria-pressed="false">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.3 12s3.5-6 9.7-6 9.7 6 9.7 6-3.5 6-9.7 6-9.7-6-9.7-6Z"/><circle cx="12" cy="12" r="2.8"/></svg>
                            </button>
                        </div>
                        @error('password')<div class="error" role="alert">{{ $message }}</div>@enderror
                    </div>

                    <div class="options">
                        <label class="remember">
                            <input type="checkbox" name="remember" value="1" checked>
                            <span>Remember me</span>
                        </label>
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                    </div>

                    <button class="login-button" type="submit">
                        <span>Sign In to ERP</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                </form>

                {{-- Applicant Sign Up & Admissions Hub --}}
                <div class="applicant-callout">
                    <div class="applicant-row">
                        <span>New student applicant?</span>
                        <a href="{{ route('register') }}">Create Applicant Account / Sign Up &rarr;</a>
                    </div>
                    <div class="applicant-links">
                        <a href="{{ route('admissions.catalogue') }}">Explore Programmes</a>
                        <span style="color:#cbd5e1;">•</span>
                        <a href="{{ route('admissions.brochure') }}" target="_blank">Brochure (PDF)</a>
                    </div>
                </div>

                <div class="trust">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>256-bit Encrypted Connection • MEMA College ERP</span>
                </div>
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
