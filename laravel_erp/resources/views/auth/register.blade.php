<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Applicant Account | MEMA College &amp; University</title>
    <meta name="description" content="Register an applicant account at MEMA College & University to apply online for Certificate, Diploma, Higher Diploma and Bachelor Degree programmes.">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/system/favicons/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/system/favicons/favicon-16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/system/favicons/apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --navy:#062f40; --navy-deep:#032635; --orange:#ff6845; --gold:#d7a84f; --cream:#fbfaf6; --ink:#092b3d; --muted:#68727f; --line:#d6d7d4; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; color:var(--ink); background:var(--cream); font-family:var(--font-system,'Quicksand','Nunito','Book Antiqua',sans-serif); }
        .auth-layout { min-height:100vh; display:grid; grid-template-columns:minmax(400px,.85fr) minmax(640px,1.15fr); }
        .identity { position:relative; isolation:isolate; display:flex; align-items:center; overflow:hidden; padding:clamp(40px,6vw,90px); color:#fff; background:var(--navy-deep); }
        .identity::before { content:""; position:absolute; inset:0; z-index:-2; background:linear-gradient(90deg,rgb(2 37 51 / 94%),rgb(2 45 61 / 79%)),url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1600&q=86') center/cover; }
        .identity::after { content:""; position:absolute; inset:auto auto -210px -160px; z-index:-1; width:560px; height:320px; border:2px solid var(--gold); border-radius:50%; transform:rotate(24deg); opacity:.9; }
        .identity-content { width:min(100%,550px); }
        .crest { width:96px; height:96px; object-fit:contain; filter:drop-shadow(0 10px 22px rgb(0 0 0 / 18%)); }
        .identity h1 { margin:24px 0 16px; font:700 clamp(36px,4.5vw,52px)/1.1 var(--font-system,'Quicksand','Nunito','Book Antiqua',sans-serif); letter-spacing:.01em; }
        .gold-rule { width:44px; height:2px; margin-bottom:20px; background:var(--gold); }
        .identity p { margin:0 0 20px; color:#e1b65f; font-size:17px; letter-spacing:.01em; }
        .highlights { margin-top:28px; display:flex; flex-direction:column; gap:12px; font-size:13.5px; color:#cfdee6; }
        .highlight-item { display:flex; align-items:center; gap:10px; }
        .highlight-icon { width:20px; height:20px; color:var(--gold); flex-shrink:0; }
        .auth-panel { display:grid; place-items:center; padding:48px clamp(24px,5vw,80px); background:radial-gradient(circle at 36% 18%,#fff 0,transparent 34%),linear-gradient(135deg,#fbfaf6,#f6f2e9); }
        .auth-card { width:min(100%,680px); padding:44px 52px 38px; border:1px solid rgb(215 168 79 / 55%); border-radius:20px; background:rgb(255 255 255 / 75%); box-shadow:0 22px 58px rgb(37 47 53 / 10%); backdrop-filter:blur(10px); }
        .card-accent { width:45px; height:2px; margin-bottom:16px; background:var(--gold); }
        .auth-card h2 { margin:0; font:700 clamp(32px,3.5vw,44px)/1.1 var(--font-system,'Quicksand','Nunito','Book Antiqua',sans-serif); color:var(--ink); }
        .subtitle { margin:10px 0 28px; color:var(--muted); font-size:16px; }
        .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
        .field { margin-bottom:20px; }
        .field label { display:block; margin-bottom:7px; font-size:13.5px; font-weight:700; color:var(--ink); }
        .input-shell { position:relative; }
        .input-shell input, .input-shell select { width:100%; height:54px; padding:0 18px 0 52px; border:1px solid var(--line); border-radius:10px; outline:none; color:var(--ink); background:rgb(255 255 255 / 70%); font-family:inherit; font-size:15px; font-weight:600; line-height:1; transition:border-color .18s,box-shadow .18s,background .18s; }
        .input-shell input:focus, .input-shell select:focus { border-color:#1a778b; background:#fff; box-shadow:0 0 0 3px rgb(26 119 139 / 12%); }
        .field-icon { position:absolute; left:0; top:0; width:48px; height:54px; display:grid; place-items:center; border-right:1px solid #edf0ef; color:#7b838c; pointer-events:none; }
        .field-icon svg { width:20px; height:20px; }
        .error { margin:6px 2px 0; color:#a43d35; font-size:12px; font-weight:600; }
        .terms-row { margin:8px 0 24px; font-size:13px; color:var(--muted); }
        .terms-row label { display:flex; align-items:flex-start; gap:10px; cursor:pointer; }
        .terms-row input { margin-top:2px; width:18px; height:18px; accent-color:#08758b; }
        .terms-row a { color:#08758b; font-weight:700; text-decoration:none; }
        .terms-row a:hover { text-decoration:underline; }
        .submit-button { width:100%; height:58px; display:flex; justify-content:center; align-items:center; gap:12px; border:0; border-radius:10px; color:#fff; background:linear-gradient(90deg,#ff714e,#fa593b); box-shadow:0 9px 22px rgb(255 104 69 / 20%); font:700 18px/1 var(--font-system,'Quicksand','Nunito','Book Antiqua',sans-serif); cursor:pointer; transition:transform .18s,box-shadow .18s; }
        .submit-button:hover { transform:translateY(-1px); box-shadow:0 13px 28px rgb(255 104 69 / 28%); }
        .signin-prompt { margin-top:22px; text-align:center; font-size:14px; color:var(--muted); font-weight:600; }
        .signin-prompt a { color:#08758b; font-weight:800; text-decoration:none; }
        .signin-prompt a:hover { text-decoration:underline; color:#ff6845; }
        .trust { display:flex; align-items:center; justify-content:center; gap:10px; margin-top:26px; padding-top:22px; border-top:1px solid rgb(215 168 79 / 40%); color:#68727f; font-size:13px; }
        .trust svg { width:20px; color:#bd8d35; }
        @media(max-width:980px) { .auth-layout { grid-template-columns:1fr; } .identity { min-height:260px; padding:36px 6vw; } .auth-panel { padding:36px 16px; } .auth-card { padding:32px 20px; } .form-grid-2 { grid-template-columns:1fr; gap:0; } }
    </style>
</head>
<body>
    <main class="auth-layout">
        {{-- Left Presentation Panel --}}
        <section class="identity" aria-label="MEMA College &amp; University Admissions">
            <div class="identity-content">
                <img class="crest" src="{{ asset('images/system/logos/mema-college-mark-192.png') }}" alt="MEMA College emblem">
                <h1>MEMA College &amp; University</h1>
                <div class="gold-rule"></div>
                <p>Start Your Academic Journey Today</p>

                <div class="highlights">
                    <div class="highlight-item">
                        <svg class="highlight-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span>Direct admission into <strong>Certificates (D+)</strong> &amp; <strong>Diplomas (C-)</strong></span>
                    </div>
                    <div class="highlight-item">
                        <svg class="highlight-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span>Higher Diplomas &amp; Bachelor Degree pathways</span>
                    </div>
                    <div class="highlight-item">
                        <svg class="highlight-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span>Instant Admission Letter generation upon review</span>
                    </div>
                    <div class="highlight-item">
                        <svg class="highlight-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span>TVET CDACC, KNQA &amp; CUE Accredited</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Right Registration Form Panel --}}
        <section class="auth-panel">
            <form class="auth-card" method="post" action="{{ route('register.store') }}">
                @csrf
                <div class="card-accent"></div>
                <h2>Create Account</h2>
                <p class="subtitle">Sign up to apply and access your Admissions Portal</p>

                @if ($errors->any())
                    <div style="padding:14px;background:#fde8e8;color:#9b1c1c;border:1px solid #f8b4b4;border-radius:10px;margin-bottom:24px;font-size:13.5px;line-height:1.4;">
                        <strong>Please correct the following:</strong>
                        <ul style="margin:6px 0 0 16px;padding:0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-grid-2">
                    <div class="field">
                        <label for="first_name">First Name *</label>
                        <div class="input-shell">
                            <span class="field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                            <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required autofocus placeholder="e.g. Amina">
                        </div>
                        @error('first_name')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="last_name">Last Name / Surname *</label>
                        <div class="input-shell">
                            <span class="field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                            <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required placeholder="e.g. Njeri">
                        </div>
                        @error('last_name')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="field">
                        <label for="email">Email Address *</label>
                        <div class="input-shell">
                            <span class="field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="applicant@example.com">
                        </div>
                        @error('email')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="phone">Phone Number (M-Pesa) *</label>
                        <div class="input-shell">
                            <span class="field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
                            <input id="phone" type="tel" name="phone" value="{{ old('phone', '+254') }}" required placeholder="+254712345678">
                        </div>
                        @error('phone')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="field">
                    <label for="programme_offering_id">Programme of Interest (Optional)</label>
                    <div class="input-shell">
                        <span class="field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></span>
                        <select id="programme_offering_id" name="programme_offering_id">
                            <option value="">-- Choose Programme (or select later inside portal) --</option>
                            @foreach($offerings as $off)
                                <option value="{{ $off->id }}" {{ (string)old('programme_offering_id', $selectedOfferingId ?? '') === (string)$off->id ? 'selected' : '' }}>
                                    {{ $off->course->code ?? '' }} - {{ $off->course->name ?? '' }} ({{ $off->campus ?? 'Main' }} • {{ $off->study_mode ?? 'Full-time' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="field">
                        <label for="password">Password (min 10 characters) *</label>
                        <div class="input-shell">
                            <span class="field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
                            <input id="password" type="password" name="password" required placeholder="••••••••••" autocomplete="new-password">
                        </div>
                        @error('password')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm Password *</label>
                        <div class="input-shell">
                            <span class="field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
                            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="••••••••••" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="terms-row">
                    <label>
                        <input type="checkbox" name="terms" value="1" required checked>
                        <span>I accept the <a href="{{ route('legal.terms') }}" target="_blank">Terms of Admission</a> and acknowledge the <a href="{{ route('legal.privacy') }}" target="_blank">Privacy Policy</a>.</span>
                    </label>
                </div>

                <button class="submit-button" type="submit">
                    <span>Create Account &amp; Proceed to Application</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>

                <div class="signin-prompt">
                    Already registered? <a href="{{ route('login') }}">Sign In to Your Portal &rarr;</a>
                </div>

                <div class="trust">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 4.5 6v5.5c0 4.7 3.2 7.8 7.5 9.5 4.3-1.7 7.5-4.8 7.5-9.5V6L12 3Z"/><path d="m9 12 2 2 4-4"/></svg>
                    <span>Secure Registration • Direct Link to <code>/admissions/my-application</code></span>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
