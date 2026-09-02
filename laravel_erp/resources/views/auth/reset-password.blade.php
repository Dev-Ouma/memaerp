<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password | MEMA College</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/system/favicons/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/system/favicons/favicon-16.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --navy:#062f40; --navy-deep:#032635; --orange:#ff6845; --gold:#d7a84f; --cream:#fbfaf6; --ink:#092b3d; --muted:#68727f; --line:#d6d7d4; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; color:var(--ink); background:var(--cream); font-family:var(--font-system,'Quicksand','Nunito','Book Antiqua',sans-serif); }
        .auth-layout { min-height:100vh; display:grid; grid-template-columns:minmax(430px,.9fr) minmax(620px,1.1fr); }
        .identity { position:relative; isolation:isolate; display:flex; align-items:center; overflow:hidden; padding:clamp(54px,8vw,112px); color:#fff; background:var(--navy-deep); }
        .identity::before { content:""; position:absolute; inset:0; z-index:-2; background:linear-gradient(90deg,rgb(2 37 51 / 94%),rgb(2 45 61 / 79%)),url('https://images.unsplash.com/photo-1564981797816-1043664bf78d?auto=format&fit=crop&w=1600&q=86') center/cover; }
        .identity-content { width:min(100%,550px); }
        .crest { width:112px; height:112px; object-fit:contain; }
        .identity h1 { margin:30px 0 22px; font:500 clamp(44px,5vw,66px)/1.08 var(--font-system,'Quicksand','Nunito','Book Antiqua',sans-serif); letter-spacing:.01em; }
        .gold-rule { width:44px; height:2px; margin-bottom:24px; background:var(--gold); }
        .identity p { margin:0; color:#e1b65f; font-size:20px; }
        .auth-panel { display:grid; place-items:center; padding:64px clamp(34px,8vw,130px); background:radial-gradient(circle at 36% 18%,#fff 0,transparent 34%),linear-gradient(135deg,#fbfaf6,#f6f2e9); }
        .auth-card { width:min(100%,620px); padding:52px 66px 46px; border:1px solid rgb(215 168 79 / 55%); border-radius:20px; background:rgb(255 255 255 / 62%); box-shadow:0 22px 58px rgb(37 47 53 / 10%); backdrop-filter:blur(10px); }
        .card-accent { width:45px; height:2px; margin-bottom:20px; background:var(--gold); }
        .auth-card h2 { margin:0; font:500 clamp(42px,4vw,58px)/1.05 var(--font-system,'Quicksand','Nunito','Book Antiqua',sans-serif); }
        .subtitle { margin:14px 0 40px; color:var(--muted); font-size:19px; }
        .field { margin-bottom:26px; }
        .field label { display:block; margin-bottom:10px; font-size:15px; font-weight:650; }
        .input-shell { position:relative; }
        .input-shell input { width:100%; height:68px; padding:0 58px 0 70px; border:1px solid var(--line); border-radius:9px; outline:none; color:var(--ink); background:rgb(255 255 255 / 50%); font-family:inherit; font-size:18px; transition:border-color .18s,box-shadow .18s; }
        .input-shell input:focus { border-color:#1a778b; background:#fff; box-shadow:0 0 0 3px rgb(26 119 139 / 12%); }
        .field-icon { position:absolute; left:0; top:0; width:58px; height:68px; display:grid; place-items:center; border-right:1px solid #edf0ef; color:#7b838c; pointer-events:none; }
        .field-icon svg,.password-toggle svg { width:24px; height:24px; }
        .password-toggle { position:absolute; right:10px; top:12px; width:44px; height:44px; display:grid; place-items:center; border:0; border-radius:50%; color:#747e87; background:transparent; cursor:pointer; }
        .password-toggle:hover { color:#0b7286; background:#edf5f5; }
        .login-button { width:100%; height:64px; display:flex; justify-content:center; align-items:center; gap:16px; border:0; border-radius:8px; color:#fff; background:linear-gradient(90deg,#ff714e,#fa593b); box-shadow:0 9px 22px rgb(255 104 69 / 20%); font:500 21px/1 var(--font-system,'Quicksand','Nunito','Book Antiqua',sans-serif); cursor:pointer; transition:transform .18s,box-shadow .18s; position:relative; }
        .login-button:hover { transform:translateY(-1px); box-shadow:0 13px 28px rgb(255 104 69 / 28%); }
        .error { margin:8px 2px 0; color:#a43d35; font-size:13px; }
        .policy-info { font-size:13px; color:var(--muted); margin-bottom:20px; line-height:1.4; }
        @media(max-width:980px) { .auth-layout { grid-template-columns:1fr; } .identity { min-height:250px; padding:46px 9vw; } .auth-panel { padding:48px 24px; } }
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
        </section>

        <section class="auth-panel">
            <form class="auth-card" method="post" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="card-accent"></div>
                <h2>Create new password</h2>
                <p class="subtitle">Set your secure new account credentials</p>

                @if ($errors->any())
                    <div class="error" role="alert" style="margin-bottom:20px">{{ $errors->first() }}</div>
                @endif

                <div class="field">
                    <label for="password">New Password</label>
                    <div class="input-shell">
                        <span class="field-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
                        <input id="password" type="password" name="password" required autofocus>
                        <button class="password-toggle" type="button" aria-label="Show password" aria-controls="password" aria-pressed="false"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.3 12s3.5-6 9.7-6 9.7 6 9.7 6-3.5 6-9.7 6-9.7-6-9.7-6Z"/><circle cx="12" cy="12" r="2.8"/></svg></button>
                    </div>
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="input-shell">
                        <span class="field-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
                        <input id="password_confirmation" type="password" name="password_confirmation" required>
                    </div>
                </div>

                <div class="policy-info">
                    <strong>Password Security Policy:</strong><br>
                    • Must be at least 8 characters long.<br>
                    • Must contain at least one uppercase letter.<br>
                    • Must contain at least one numeric digit.<br>
                    • Must contain at least one special symbol.
                </div>

                <button class="login-button" type="submit">Update Password</button>
            </form>
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
