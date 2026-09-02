<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | MEMA College</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/system/favicons/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/system/favicons/favicon-16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/system/favicons/apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --navy:#062f40; --navy-deep:#032635; --orange:#ff6845; --gold:#d7a84f; --cream:#fbfaf6; --ink:#092b3d; --muted:#68727f; --line:#d6d7d4; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; color:var(--ink); background:var(--cream); font-family:var(--font-system,'Quicksand','Nunito','Book Antiqua',sans-serif); }
        .auth-layout { min-height:100vh; display:grid; grid-template-columns:minmax(430px,.9fr) minmax(620px,1.1fr); }
        .identity { position:relative; isolation:isolate; display:flex; align-items:center; overflow:hidden; padding:clamp(54px,8vw,112px); color:#fff; background:var(--navy-deep); }
        .identity::before { content:""; position:absolute; inset:0; z-index:-2; background:linear-gradient(90deg,rgb(2 37 51 / 94%),rgb(2 45 61 / 79%)),url('https://images.unsplash.com/photo-1564981797816-1043664bf78d?auto=format&fit=crop&w=1600&q=86') center/cover; }
        .identity::after { content:""; position:absolute; inset:auto auto -210px -160px; z-index:-1; width:560px; height:320px; border:2px solid var(--gold); border-radius:50%; transform:rotate(24deg); opacity:.9; }
        .identity-content { width:min(100%,550px); }
        .crest { width:112px; height:112px; object-fit:contain; filter:drop-shadow(0 10px 22px rgb(0 0 0 / 18%)); }
        .identity h1 { margin:30px 0 22px; font:500 clamp(44px,5vw,66px)/1.08 var(--font-system,'Quicksand','Nunito','Book Antiqua',sans-serif); letter-spacing:.01em; }
        .gold-rule { width:44px; height:2px; margin-bottom:24px; background:var(--gold); }
        .identity p { margin:0; color:#e1b65f; font-size:20px; letter-spacing:.01em; }
        .spark { position:absolute; left:20%; bottom:9%; width:8px; height:8px; background:var(--orange); transform:rotate(45deg); }
        .spark::before,.spark::after { content:""; position:absolute; inset:50% auto auto 50%; width:42px; height:1px; background:var(--orange); transform:translate(-50%,-50%); }
        .spark::after { transform:translate(-50%,-50%) rotate(90deg); }
        .auth-panel { display:grid; place-items:center; padding:64px clamp(34px,8vw,130px); background:radial-gradient(circle at 36% 18%,#fff 0,transparent 34%),linear-gradient(135deg,#fbfaf6,#f6f2e9); }
        .auth-card { width:min(100%,620px); padding:52px 66px 46px; border:1px solid rgb(215 168 79 / 55%); border-radius:20px; background:rgb(255 255 255 / 62%); box-shadow:0 22px 58px rgb(37 47 53 / 10%); backdrop-filter:blur(10px); }
        .card-accent { width:45px; height:2px; margin-bottom:20px; background:var(--gold); }
        .auth-card h2 { margin:0; font:500 clamp(42px,4vw,58px)/1.05 var(--font-system,'Quicksand','Nunito','Book Antiqua',sans-serif); }
        .subtitle { margin:14px 0 40px; color:var(--muted); font-size:19px; }
        .field { margin-bottom:26px; }
        .field label { display:block; margin-bottom:10px; font-size:15px; font-weight:650; }
        .input-shell { position:relative; }
        .input-shell input { width:100%; height:68px; padding:0 58px 0 70px; border:1px solid var(--line); border-radius:9px; outline:none; color:var(--ink); background:rgb(255 255 255 / 50%); font-family:inherit; font-size:18px; font-weight:500; line-height:1; transition:border-color .18s,box-shadow .18s,background .18s; }
        .input-shell input:focus { border-color:#1a778b; background:#fff; box-shadow:0 0 0 3px rgb(26 119 139 / 12%); }
        .field-icon { position:absolute; left:0; top:0; width:58px; height:68px; display:grid; place-items:center; border-right:1px solid #edf0ef; color:#7b838c; pointer-events:none; }
        .field-icon svg,.password-toggle svg { width:24px; height:24px; }
        .password-toggle { position:absolute; right:10px; top:12px; width:44px; height:44px; display:grid; place-items:center; border:0; border-radius:50%; color:#747e87; background:transparent; cursor:pointer; }
        .password-toggle:hover,.password-toggle:focus-visible { color:#0b7286; background:#edf5f5; }
        .error { margin:8px 2px 0; color:#a43d35; font-size:13px; }
        .options { display:flex; justify-content:space-between; align-items:center; gap:24px; margin:2px 0 32px; font-size:15px; }
        .remember { display:flex; align-items:center; gap:10px; cursor:pointer; }
        .remember input { width:21px; height:21px; accent-color:#08758b; }
        .options a { color:#08758b; text-decoration:none; }
        .options a:hover { text-decoration:underline; text-underline-offset:3px; }
        .login-button { width:100%; height:64px; display:flex; justify-content:center; align-items:center; gap:16px; border:0; border-radius:8px; color:#fff; background:linear-gradient(90deg,#ff714e,#fa593b); box-shadow:0 9px 22px rgb(255 104 69 / 20%); font:500 21px/1 var(--font-system,'Quicksand','Nunito','Book Antiqua',sans-serif); cursor:pointer; transition:transform .18s,box-shadow .18s; }
        .login-button:hover { transform:translateY(-1px); box-shadow:0 13px 28px rgb(255 104 69 / 28%); }
        .login-button svg { position:absolute; right:24px; width:22px; }
        .login-button { position:relative; }
        .trust { display:flex; align-items:center; justify-content:center; gap:13px; margin-top:30px; padding-top:28px; border-top:1px solid rgb(215 168 79 / 55%); color:#68727f; font-size:14px; }
        .trust svg { width:23px; color:#bd8d35; }
        @media(max-width:980px) { .auth-layout { grid-template-columns:1fr; } .identity { min-height:300px; padding:46px 9vw; } .identity h1 { margin:18px 0 14px; font-size:44px; } .identity p { font-size:17px; } .crest { width:82px; height:82px; } .auth-panel { padding:48px 24px; } }
        @media(max-width:580px) { .identity { min-height:235px; align-items:flex-end; } .identity h1 { font-size:36px; } .identity p { font-size:15px; } .auth-panel { padding:28px 16px; } .auth-card { padding:34px 22px 30px; border-radius:15px; } .auth-card h2 { font-size:40px; } .subtitle { margin-bottom:30px; } .options { align-items:flex-start; } .input-shell input { font-size:16px; } }
    </style>
</head>
<body>
    <main class="auth-layout">
        <section class="identity" aria-label="MEMA College">
            <div class="identity-content">
                <img class="crest" src="{{ asset('images/system/logos/mema-college-mark-192.png') }}" alt="MEMA College emblem">
                <h1>MEMA College</h1>
                <div class="gold-rule"></div>
                <p>Learn. Lead. Transform.</p>
            </div>
            <span class="spark" aria-hidden="true"></span>
        </section>

        <section class="auth-panel">
            <form class="auth-card" method="post" action="{{ route('login.store') }}" data-processing-message="Signing you in securely…">
                @csrf
                <div class="card-accent"></div>
                <h2>Welcome back</h2>
                <p class="subtitle">Sign in to access your portal</p>

                @if (session('success'))
                    <div class="alert" style="padding:12px;background:#e6f4ea;color:#137333;border:1px solid #c2e7cd;border-radius:6px;margin-bottom:20px;font-size:14px;">{{ session('success') }}</div>
                @endif
                @if (session('info'))
                    <div class="alert" style="padding:12px;background:#e8f0fe;color:#1a73e8;border:1px solid #d2e3fc;border-radius:6px;margin-bottom:20px;font-size:14px;">{{ session('info') }}</div>
                @endif

                <div class="field">
                    <label for="email">MEMA Email or Username</label>
                    <div class="input-shell">
                        <span class="field-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
                        <input id="email" type="text" name="email" value="{{ old('email', 'admin@mema.ac.ke') }}" autocomplete="username" required autofocus placeholder="Email or Username">
                    </div>
                    @error('email')<div class="error" role="alert">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-shell">
                        <span class="field-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
                        <input id="password" type="password" name="password" value="password" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" aria-label="Show password" aria-controls="password" aria-pressed="false"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.3 12s3.5-6 9.7-6 9.7 6 9.7 6-3.5 6-9.7 6-9.7-6-9.7-6Z"/><circle cx="12" cy="12" r="2.8"/></svg></button>
                    </div>
                </div>

                <div class="options">
                    <label class="remember"><input type="checkbox" name="remember" value="1" checked> Remember me</label>
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                </div>

                <button class="login-button" type="submit" data-processing-message="Signing you in securely…">Login securely <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M14 7l5 5-5 5"/></svg></button>
                <div class="trust"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 4.5 6v5.5c0 4.7 3.2 7.8 7.5 9.5 4.3-1.7 7.5-4.8 7.5-9.5V6L12 3Z"/><path d="m9 12 2 2 4-4"/></svg><span>Protected access&nbsp; • &nbsp;MEMA College ERP</span></div>
            </form>
        </section>
    </main>
    <script>
        const toggle = document.querySelector('.password-toggle');
        const password = document.querySelector('#password');
        toggle?.addEventListener('click', () => { const visible = password.type === 'text'; password.type = visible ? 'password' : 'text'; toggle.setAttribute('aria-label', visible ? 'Show password' : 'Hide password'); toggle.setAttribute('aria-pressed', String(!visible)); });
    </script>
</body>
</html>
