<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MEMA ERP — Institutional Single Sign-On</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #090d16;
            --card-bg: rgba(17, 24, 39, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --primary-glow: rgba(59, 130, 246, 0.35);
            --accent: #f59e0b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --input-bg: rgba(15, 23, 42, 0.6);
            --input-border: rgba(255, 255, 255, 0.12);
            --danger: #ef4444;
            --success: #10b981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg-base);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 24px;
        }

        /* Ambient Glow Backgrounds */
        .ambient-glow {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            z-index: 0;
            pointer-events: none;
            opacity: 0.5;
        }

        .glow-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #1e3a8a, transparent 70%);
            top: -100px;
            left: -100px;
        }

        .glow-2 {
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, #0284c7, transparent 70%);
            bottom: -50px;
            right: -50px;
        }

        .glow-3 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, #b45309, transparent 70%);
            top: 40%;
            left: 60%;
            opacity: 0.25;
        }

        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 460px;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.25);
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #60a5fa;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .brand-badge::before {
            content: '';
            width: 8px;
            height: 8px;
            background-color: #3b82f6;
            border-radius: 50%;
            box-shadow: 0 0 8px #60a5fa;
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
        }

        .brand-subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5),
                        0 0 0 1px rgba(255, 255, 255, 0.05) inset;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            padding: 13px 16px;
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-glow);
            background: rgba(15, 23, 42, 0.9);
        }

        .form-input::placeholder {
            color: #64748b;
        }

        .form-row-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 0.875rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
        }

        .checkbox-label input[type="checkbox"] {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .forgot-link {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #93c5fd;
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            padding: 14px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 15px var(--primary-glow);
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.45);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Demo credentials quick pick */
        .demo-credentials {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--card-border);
        }

        .demo-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .demo-chip {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.82rem;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 8px;
        }

        .demo-chip:hover {
            background: rgba(59, 130, 246, 0.12);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .demo-role {
            font-weight: 600;
            color: #93c5fd;
        }

        .demo-user {
            font-family: 'JetBrains Mono', monospace;
            color: #cbd5e1;
            font-size: 0.78rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #fca5a5;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-note {
            text-align: center;
            margin-top: 24px;
            font-size: 0.8rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="ambient-glow glow-1"></div>
    <div class="ambient-glow glow-2"></div>
    <div class="ambient-glow glow-3"></div>

    <div class="login-wrapper">
        <div class="brand-header">
            <div class="brand-badge">Institutional Portal</div>
            <h1 class="brand-title">MEMA ERP</h1>
            <p class="brand-subtitle">Integrated University Information Management System</p>
        </div>

        <div class="card">
            @if ($errors->any())
                <div class="alert-error">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="/login" id="loginForm">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="login">Institutional ID or Email</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            id="login"
                            name="login"
                            class="form-input"
                            placeholder="admin@mema.ac.ke or username"
                            value="{{ old('login', 'admin@mema.ac.ke') }}"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="••••••••••••"
                            value="password123"
                            required
                        >
                    </div>
                </div>

                <div class="form-row-between">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember" value="1" checked>
                        <span>Remember credentials</span>
                    </label>
                    <a href="#" class="forgot-link" onclick="alert('Please contact ICT Service Desk at helpdesk@mema.ac.ke'); return false;">Forgot password?</a>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span>Sign In to Portal</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </button>
            </form>

            <div class="demo-credentials">
                <div class="demo-title">
                    <span>Quick Fill Credentials</span>
                    <span style="font-size: 0.68rem; color: #3b82f6;">CLICK TO LOAD</span>
                </div>
                <div class="demo-chip" onclick="fillCreds('admin@mema.ac.ke', 'password123')">
                    <div>
                        <span class="demo-role">👑 System Administrator</span>
                        <div class="demo-user">admin@mema.ac.ke</div>
                    </div>
                    <span style="color: #64748b;">→</span>
                </div>
                <div class="demo-chip" onclick="fillCreds('superadmin', 'password123')">
                    <div>
                        <span class="demo-role">⚡ Root Username</span>
                        <div class="demo-user">superadmin</div>
                    </div>
                    <span style="color: #64748b;">→</span>
                </div>
            </div>
        </div>

        <div class="footer-note">
            © {{ date('Y') }} Mema University. Protected by Enterprise RBAC & Audit Telemetry.
        </div>
    </div>

    <script>
        function fillCreds(user, pass) {
            document.getElementById('login').value = user;
            document.getElementById('password').value = pass;
        }
    </script>
</body>
</html>
