<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MEMA ERP — Institutional Enterprise Control Centre</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #090d16;
            --sidebar-bg: #0d1322;
            --sidebar-border: rgba(255, 255, 255, 0.07);
            --card-bg: rgba(17, 24, 39, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --primary-glow: rgba(59, 130, 246, 0.25);
            --accent: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --sidebar-width: 280px;
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
            overflow-x: hidden;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            height: 100vh;
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            z-index: 50;
        }

        .sidebar-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--sidebar-border);
        }

        .brand-logo-sq {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.3rem;
            color: #fff;
            box-shadow: 0 0 20px rgba(37, 99, 235, 0.4);
            letter-spacing: -0.05em;
        }

        .brand-info h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .brand-info span {
            font-size: 0.72rem;
            color: #60a5fa;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .nav-section {
            padding: 16px 12px 8px 12px;
        }

        .nav-section-title {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            font-weight: 700;
            padding: 0 12px 8px 12px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.15s ease;
            margin-bottom: 2px;
            cursor: pointer;
            border-left: 3px solid transparent;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
        }

        .nav-item.active {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            font-weight: 600;
            border-left-color: #3b82f6;
        }

        .nav-item-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-icon {
            width: 18px;
            height: 18px;
            opacity: 0.75;
        }

        .nav-item.active .nav-icon {
            opacity: 1;
            color: #3b82f6;
        }

        .nav-badge {
            font-size: 0.68rem;
            padding: 2px 7px;
            border-radius: 9999px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.08);
            color: #94a3b8;
        }

        .nav-badge.active {
            background: rgba(59, 130, 246, 0.25);
            color: #93c5fd;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px;
            border-top: 1px solid var(--sidebar-border);
            background: rgba(0, 0, 0, 0.2);
        }

        .user-card-mini {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: #fff;
        }

        .user-meta-name {
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 130px;
        }

        .user-meta-role {
            font-size: 0.72rem;
            color: #94a3b8;
        }

        /* ── MAIN CONTENT AREA ── */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-y: auto;
        }

        .top-navbar {
            height: 68px;
            background: rgba(13, 19, 34, 0.85);
            border-bottom: 1px solid var(--card-border);
            backdrop-filter: blur(16px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .search-container {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--card-border);
            padding: 8px 16px;
            border-radius: 10px;
            width: 380px;
        }

        .search-container input {
            background: transparent;
            border: none;
            outline: none;
            color: #fff;
            font-size: 0.88rem;
            width: 100%;
        }

        .search-kbd {
            font-size: 0.7rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 2px 6px;
            color: #94a3b8;
            font-family: 'JetBrains Mono', monospace;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .term-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.25);
            color: #fbbf24;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .term-badge::before {
            content: '';
            width: 7px;
            height: 7px;
            background-color: #f59e0b;
            border-radius: 50%;
            box-shadow: 0 0 6px #f59e0b;
        }

        .btn-logout {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #f87171;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.25);
        }

        /* ── CONTENT BODY ── */
        .content-body {
            padding: 32px;
            max-width: 1400px;
            width: 100%;
        }

        .hero-banner {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.25) 0%, rgba(15, 23, 42, 0.85) 100%);
            border: 1px solid rgba(59, 130, 246, 0.35);
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .hero-banner::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.3), transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .hero-sub {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 14px var(--primary-glow);
            transition: all 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(59, 130, 246, 0.4);
        }

        .grid-kpi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .kpi-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 22px;
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .kpi-card:hover {
            border-color: rgba(59, 130, 246, 0.4);
            transform: translateY(-2px);
        }

        .kpi-label {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .kpi-value {
            font-family: 'Outfit', sans-serif;
            font-size: 1.9rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
        }

        .kpi-sub {
            font-size: 0.76rem;
            color: #10b981;
            font-weight: 600;
        }

        .grid-2col {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--card-border);
        }

        .card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .perm-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .badge-perm {
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.25);
            color: #93c5fd;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
        }

        .badge-status {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-status.success {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-status.warning {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-status.info {
            background: rgba(59, 130, 246, 0.15);
            color: #93c5fd;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        /* ── TABLES ── */
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.86rem;
        }

        .table-custom th {
            text-align: left;
            padding: 10px 14px;
            color: #64748b;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .table-custom td {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: #e2e8f0;
        }

        .table-custom tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .code-pill {
            font-family: 'JetBrains Mono', monospace;
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .telemetry-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            font-size: 0.86rem;
        }

        .telemetry-row:last-child {
            border-bottom: none;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 8px #10b981;
            display: inline-block;
            margin-right: 6px;
        }

        /* ── SPA VIEW PANELS ── */
        .spa-view {
            display: none;
        }

        .spa-view.active-view {
            display: block;
            animation: fadeIn 0.2s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <!-- ── 1. ENTERPRISE ERP SIDEBAR ── -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo-sq">M</div>
            <div class="brand-info">
                <h2>MEMA ERP</h2>
                <span>Enterprise Core</span>
            </div>
        </div>

        <!-- Section: Core Platform -->
        <div class="nav-section">
            <div class="nav-section-title">Core Platform</div>
            <div class="nav-item active" onclick="switchView('overview', this)">
                <div class="nav-item-left">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>Control Centre</span>
                </div>
                <span class="nav-badge active">LIVE</span>
            </div>
            <div class="nav-item" onclick="switchView('iam', this)">
                <div class="nav-item-left">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <span>IAM & RBAC</span>
                </div>
                <span class="nav-badge">{{ $stats['roleCount'] }} Roles</span>
            </div>
            <div class="nav-item" onclick="switchView('institution', this)">
                <div class="nav-item-left">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    </svg>
                    <span>Institution Setup</span>
                </div>
            </div>
            <div class="nav-item" onclick="switchView('audit', this)">
                <div class="nav-item-left">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                    <span>Audit Telemetry</span>
                </div>
                <span class="nav-badge">LIVE LOGS</span>
            </div>
        </div>

        <!-- Section: Academic Lifecycle -->
        <div class="nav-section">
            <div class="nav-section-title">Academic Lifecycle</div>
            <div class="nav-item" onclick="switchView('admissions', this)">
                <div class="nav-item-left">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <polyline points="17 11 19 13 23 9"></polyline>
                    </svg>
                    <span>Admissions</span>
                </div>
                <span class="nav-badge" style="color: #fbbf24;">{{ $stats['applicantCount'] }} Apps</span>
            </div>
            <div class="nav-item" onclick="switchView('students', this)">
                <div class="nav-item-left">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                    <span>Student Lifecycle</span>
                </div>
            </div>
            <div class="nav-item" onclick="switchView('programmes', this)">
                <div class="nav-item-left">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    <span>Programmes</span>
                </div>
                <span class="nav-badge" style="color: #60a5fa;">{{ $stats['programmeCount'] }}</span>
            </div>
            <div class="nav-item" onclick="switchView('courses', this)">
                <div class="nav-item-left">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                    <span>Course Catalogue</span>
                </div>
                <span class="nav-badge" style="color: #60a5fa;">{{ $stats['courseCount'] }}</span>
            </div>
            <div class="nav-item" onclick="switchView('offerings', this)">
                <div class="nav-item-left">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>Semester Offerings</span>
                </div>
                <span class="nav-badge" style="color: #34d399;">{{ $stats['offeringCount'] }} Active</span>
            </div>
        </div>

        <!-- Section: Finance & Infrastructure -->
        <div class="nav-section">
            <div class="nav-section-title">Finance & Infrastructure</div>
            <div class="nav-item" onclick="switchView('finance', this)">
                <div class="nav-item-left">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    <span>Student Billing & POS</span>
                </div>
                <span class="nav-badge" style="color: #34d399;">KES {{ number_format($stats['revenueCollected']) }}</span>
            </div>
            <a href="/horizon" target="_blank" class="nav-item">
                <div class="nav-item-left">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polygon points="10 8 16 12 10 16 10 8"></polygon>
                    </svg>
                    <span>Horizon Queues</span>
                </div>
                <span class="nav-badge" style="color: #60a5fa;">↗</span>
            </a>
        </div>

        <!-- Sidebar User Footer -->
        <div class="sidebar-footer">
            <div class="user-card-mini">
                <div class="user-avatar">
                    {{ substr(auth()->user()->username ?? 'A', 0, 1) }}
                </div>
                <div>
                    <div class="user-meta-name">{{ auth()->user()->person?->full_name ?? auth()->user()->username }}</div>
                    <div class="user-meta-role">{{ auth()->user()->email }}</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- ── 2. MAIN APPLICATION CANVAS ── -->
    <div class="main-wrapper">

        <!-- Top Header Navigation -->
        <header class="top-navbar">
            <div class="search-container">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Search programmes, courses, applicants, invoices...">
                <span class="search-kbd">⌘K</span>
            </div>

            <div class="top-actions">
                <div class="term-badge">
                    <span>2026/2027 • Semester 1</span>
                </div>

                <div style="font-size: 0.8rem; color: #10b981; font-weight: 600; display: flex; align-items: center;">
                    <span class="status-dot"></span> System Nominal
                </div>

                <form method="POST" action="/logout" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-logout">Sign Out</button>
                </form>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="content-body">

            <!-- ── TAB 1: OVERVIEW CONTROL CENTRE ── -->
            <div id="view-overview" class="spa-view active-view">
                <div class="hero-banner">
                    <div>
                        <h1 class="hero-title">Welcome, {{ auth()->user()->person?->given_name ?? 'Administrator' }} 👋</h1>
                        <p class="hero-sub">
                            {{ auth()->user()->institution->name ?? 'Mema University' }} — Enterprise Platform Control Active
                        </p>
                    </div>
                    <div class="hero-actions">
                        <a href="/horizon" target="_blank" class="btn-primary">
                            <span>Horizon Queues</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="7" y1="17" x2="17" y2="7"></line>
                                <polyline points="7 7 17 7 17 17"></polyline>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- KPI Cards Grid -->
                <div class="grid-kpi">
                    <div class="kpi-card" onclick="switchView('programmes')">
                        <div class="kpi-label">Programmes Active</div>
                        <div class="kpi-value">{{ $stats['programmeCount'] }}</div>
                        <div class="kpi-sub">● Senate Approved</div>
                    </div>

                    <div class="kpi-card" onclick="switchView('courses')">
                        <div class="kpi-label">Courses in Catalogue</div>
                        <div class="kpi-value">{{ $stats['courseCount'] }}</div>
                        <div class="kpi-sub">All Levels Mapped</div>
                    </div>

                    <div class="kpi-card" onclick="switchView('offerings')">
                        <div class="kpi-label">Semester Offerings</div>
                        <div class="kpi-value">{{ $stats['offeringCount'] }}</div>
                        <div class="kpi-sub">● Registration Open</div>
                    </div>

                    <div class="kpi-card" onclick="switchView('admissions')">
                        <div class="kpi-label">Total Applicants</div>
                        <div class="kpi-value">{{ $stats['applicantCount'] }}</div>
                        <div class="kpi-sub">{{ $stats['admittedCount'] }} Admitted</div>
                    </div>
                </div>

                <!-- 2 Column Overview -->
                <div class="grid-2col">
                    <div>
                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">Active Semester Course Offerings</span>
                                <span class="badge-perm" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">MOD-01-04</span>
                            </div>

                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Title</th>
                                        <th>Section</th>
                                        <th>Capacity</th>
                                        <th>Mode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($offerings as $off)
                                        <tr>
                                            <td><span class="code-pill">{{ $off->course->code }}</span></td>
                                            <td style="font-weight: 600;">{{ $off->course->title }}</td>
                                            <td>Sec {{ $off->section_code }}</td>
                                            <td>{{ $off->enrolled_count }} / {{ $off->max_capacity }}</td>
                                            <td><span style="color: #60a5fa; font-size: 0.78rem;">{{ $off->delivery_mode }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">Recent Applicant Pipeline</span>
                                <span class="badge-perm" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">MOD-01-05</span>
                            </div>

                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>App #</th>
                                        <th>Candidate</th>
                                        <th>Programme</th>
                                        <th>KCSE</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($applications->take(3) as $app)
                                        <tr>
                                            <td><span class="code-pill">{{ $app->application_number }}</span></td>
                                            <td style="font-weight: 600;">{{ $app->person->full_name }}</td>
                                            <td>{{ $app->programme->code }}</td>
                                            <td style="color: #fbbf24; font-weight: 700;">{{ $app->mean_grade }}</td>
                                            <td>
                                                <span class="badge-status {{ $app->status === 'ACCEPTED' ? 'success' : ($app->status === 'ADMITTED' ? 'info' : 'warning') }}">
                                                    {{ $app->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Right Column: Infrastructure Telemetry -->
                    <div>
                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">Infrastructure Health</span>
                                <span style="color: #10b981; font-size: 0.78rem; font-weight: 700;">● ALL SYSTEMS ONLINE</span>
                            </div>

                            <div class="telemetry-row">
                                <span style="color: var(--text-muted);">PostgreSQL Database:</span>
                                <span style="color: #34d399; font-weight: 600; font-family: 'JetBrains Mono';">Port 5433 (OK)</span>
                            </div>

                            <div class="telemetry-row">
                                <span style="color: var(--text-muted);">Redis Cache / Queue:</span>
                                <span style="color: #34d399; font-weight: 600; font-family: 'JetBrains Mono';">Port 6380 (OK)</span>
                            </div>

                            <div class="telemetry-row">
                                <span style="color: var(--text-muted);">MinIO S3 Storage:</span>
                                <span style="color: #34d399; font-weight: 600; font-family: 'JetBrains Mono';">Port 9000 (OK)</span>
                            </div>

                            <div class="telemetry-row">
                                <span style="color: var(--text-muted);">Mailpit Inbox:</span>
                                <span style="color: #34d399; font-weight: 600; font-family: 'JetBrains Mono';">Port 8025 (OK)</span>
                            </div>

                            <div class="telemetry-row">
                                <span style="color: var(--text-muted);">Multi-Schema DB:</span>
                                <span style="color: #60a5fa; font-weight: 600;">iam, student, finance</span>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <span class="card-title">Fee Revenue Reconciled</span>
                                <span class="badge-status success">KES {{ number_format($stats['revenueCollected']) }}</span>
                            </div>
                            <div style="font-size: 0.84rem; color: var(--text-muted); line-height: 1.6;">
                                Direct M-Pesa automated reconciliation active via C2B callback simulation. Instant receipt generation.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── TAB 2: IAM & RBAC ── -->
            <div id="view-iam" class="spa-view">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Identity, Roles & Permissions Directory</h2>
                            <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">11 Normalized Role Families across the 55 university operational domains.</p>
                        </div>
                        <span class="badge-perm">{{ $roles->count() }} System Roles</span>
                    </div>

                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Role Code</th>
                                <th>Role Name</th>
                                <th>Role Family</th>
                                <th>System Protected</th>
                                <th>Permissions Attached</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $r)
                                <tr>
                                    <td><span class="code-pill">{{ $r->code }}</span></td>
                                    <td style="font-weight: 700; color: #fff;">{{ $r->name }}</td>
                                    <td><span class="badge-perm" style="color: #f59e0b; border-color: rgba(245, 158, 11, 0.3);">{{ strtoupper($r->family) }}</span></td>
                                    <td><span style="color: #34d399; font-weight: 600;">{{ $r->is_system ? 'YES (Immutable)' : 'NO' }}</span></td>
                                    <td>
                                        <span style="font-size: 0.8rem; color: #60a5fa; font-weight: 600;">
                                            {{ $r->permissions->count() }} Permissions
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── TAB 3: INSTITUTION SETUP ── -->
            <div id="view-institution" class="spa-view">
                <div class="grid-2col">
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title">Faculties & Academic Departments</span>
                            <span class="badge-perm">{{ $faculties->count() }} Faculties</span>
                        </div>
                        @foreach ($faculties as $fac)
                            <div style="padding: 16px; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--card-border); border-radius: 12px; margin-bottom: 12px;">
                                <div style="font-weight: 700; color: #60a5fa; font-size: 1rem; margin-bottom: 4px;">{{ $fac->name }} ({{ $fac->code }})</div>
                                <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 10px;">Campus: Main Campus • Cost Centre: CC-SCI-01</div>
                                <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Constituent Departments:</div>
                                @foreach ($fac->departments as $d)
                                    <div style="padding: 6px 10px; background: rgba(255, 255, 255, 0.04); border-radius: 6px; margin-top: 6px; font-size: 0.85rem; display: flex; justify-content: space-between;">
                                        <span>● {{ $d->name }}</span>
                                        <span class="code-pill">{{ $d->code }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <span class="card-title">Campuses & Academic Calendar</span>
                            <span class="badge-perm">Calendar</span>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Active Campuses:</div>
                            @foreach ($campuses as $camp)
                                <div class="telemetry-row">
                                    <span style="font-weight: 600;">{{ $camp->name }} ({{ $camp->code }})</span>
                                    <span style="color: #34d399; font-size: 0.8rem;">● Town: {{ $camp->town }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div>
                            <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Academic Terms & Windows:</div>
                            @foreach ($terms as $t)
                                <div style="padding: 12px; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--card-border); border-radius: 10px; margin-bottom: 8px;">
                                    <div style="font-weight: 700; color: #fbbf24;">{{ $t->name }}</div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                        Dates: {{ $t->starts_on }} to {{ $t->ends_on }} • <span style="color: #34d399;">Registration Open</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── TAB 4: PROGRAMMES & CURRICULUM ── -->
            <div id="view-programmes" class="spa-view">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Approved Degree Programmes & Curriculum Versions</h2>
                            <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">Senate approved programme structures, duration, and credit rules.</p>
                        </div>
                        <span class="badge-perm">{{ $programmes->count() }} Active</span>
                    </div>

                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Programme Name</th>
                                <th>Award Level</th>
                                <th>Department</th>
                                <th>Duration</th>
                                <th>Credits Required</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($programmes as $prog)
                                <tr>
                                    <td><span class="code-pill">{{ $prog->code }}</span></td>
                                    <td style="font-weight: 700; color: #fff;">{{ $prog->name }}</td>
                                    <td><span class="badge-status info">{{ $prog->award_level }}</span></td>
                                    <td>{{ $prog->department->name }}</td>
                                    <td>{{ $prog->duration_years }} Years</td>
                                    <td style="color: #fbbf24; font-weight: 700;">{{ $prog->total_credits_required }} Credits</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── TAB 5: COURSE CATALOGUE ── -->
            <div id="view-courses" class="spa-view">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Master Course Catalogue</h2>
                            <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">University syllabus catalogue with credit weights, instructional hours, and prerequisite dependencies.</p>
                        </div>
                        <span class="badge-perm">{{ $courses->count() }} Courses</span>
                    </div>

                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Title</th>
                                <th>Credits</th>
                                <th>Lecture / Lab Hours</th>
                                <th>Prerequisites</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($courses as $c)
                                <tr>
                                    <td><span class="code-pill">{{ $c->code }}</span></td>
                                    <td style="font-weight: 700; color: #fff;">{{ $c->title }}</td>
                                    <td style="font-weight: 700; color: #fbbf24;">{{ $c->credits }} CR</td>
                                    <td style="color: var(--text-muted);">{{ $c->lecture_hours }}h Lecture / {{ $c->lab_hours }}h Lab</td>
                                    <td>
                                        @forelse ($c->prerequisites as $p)
                                            <span class="badge-perm" style="color: #f87171; border-color: rgba(239, 68, 68, 0.3); font-size: 0.72rem;">
                                                Req: {{ $p->prerequisiteCourse->code }}
                                            </span>
                                        @empty
                                            <span style="color: #64748b; font-size: 0.78rem;">None</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── TAB 6: SEMESTER OFFERINGS ── -->
            <div id="view-offerings" class="spa-view">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Active Semester Course Offerings & Sections</h2>
                            <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">Term-specific course availability, campus allocation, and section capacity gates.</p>
                        </div>
                        <span class="badge-status success">{{ $offerings->count() }} Sections Open</span>
                    </div>

                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Title</th>
                                <th>Section</th>
                                <th>Campus</th>
                                <th>Instructor</th>
                                <th>Seat Capacity</th>
                                <th>Delivery Mode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($offerings as $off)
                                <tr>
                                    <td><span class="code-pill">{{ $off->course->code }}</span></td>
                                    <td style="font-weight: 700; color: #fff;">{{ $off->course->title }}</td>
                                    <td><span class="badge-perm" style="color: #fbbf24;">Section {{ $off->section_code }}</span></td>
                                    <td>{{ $off->campus->name }}</td>
                                    <td>{{ $off->lecturer?->person?->full_name ?? 'Allocated to Faculty' }}</td>
                                    <td>{{ $off->enrolled_count }} / {{ $off->max_capacity }}</td>
                                    <td><span style="color: #60a5fa; font-weight: 600;">{{ $off->delivery_mode }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── TAB 7: ADMISSIONS PIPELINE ── -->
            <div id="view-admissions" class="spa-view">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Student Recruitment & Admissions Pipeline</h2>
                            <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">Applicant screening, KCSE points evaluation, and digital offer letters.</p>
                        </div>
                        <span class="badge-perm">{{ $applications->count() }} Candidates</span>
                    </div>

                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Application #</th>
                                <th>Applicant Name</th>
                                <th>Target Programme</th>
                                <th>Prior High School</th>
                                <th>Mean Grade</th>
                                <th>Score</th>
                                <th>Admission Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($applications as $app)
                                <tr>
                                    <td><span class="code-pill">{{ $app->application_number }}</span></td>
                                    <td style="font-weight: 700; color: #fff;">{{ $app->person->full_name }}</td>
                                    <td>{{ $app->programme->name }}</td>
                                    <td style="color: var(--text-muted);">{{ $app->secondary_school_name }}</td>
                                    <td style="font-weight: 700; color: #fbbf24;">{{ $app->mean_grade }}</td>
                                    <td>{{ $app->qualification_score }} pts</td>
                                    <td>
                                        <span class="badge-status {{ $app->status === 'ACCEPTED' ? 'success' : ($app->status === 'ADMITTED' ? 'info' : 'warning') }}">
                                            {{ $app->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── TAB 8: STUDENT BILLING & FINANCE ── -->
            <div id="view-finance" class="spa-view">
                <div class="grid-2col">
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title">Programme Fee Structures</span>
                            <span class="badge-perm">Billing</span>
                        </div>
                        @foreach ($feeStructures as $fee)
                            <div style="padding: 16px; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--card-border); border-radius: 12px; margin-bottom: 12px;">
                                <div style="font-weight: 700; color: #fff; font-size: 1rem; margin-bottom: 4px;">{{ $fee->name }}</div>
                                <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 10px;">Programme: {{ $fee->programme->name }} (Year {{ $fee->year_level }} Sem {{ $fee->semester }})</div>
                                <div class="telemetry-row">
                                    <span>Tuition Fee:</span>
                                    <span style="font-weight: 600;">KES {{ number_format((float)$fee->tuition_fee, 2) }}</span>
                                </div>
                                <div class="telemetry-row">
                                    <span>Statutory & Lab Fees:</span>
                                    <span style="font-weight: 600;">KES {{ number_format((float)$fee->statutory_fees, 2) }}</span>
                                </div>
                                <div class="telemetry-row" style="border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: 6px; padding-top: 8px;">
                                    <span style="font-weight: 700; color: #fbbf24;">Total Term Fee:</span>
                                    <span style="font-weight: 800; color: #fbbf24; font-size: 1.05rem;">KES {{ number_format((float)$fee->total_amount, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <span class="card-title">M-Pesa & Bank Payment Receipts</span>
                            <span class="badge-status success">KES {{ number_format($stats['revenueCollected']) }}</span>
                        </div>

                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Student</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payments as $pay)
                                    <tr>
                                        <td><span class="code-pill">{{ $pay->receipt_number }}</span></td>
                                        <td style="font-weight: 600;">{{ $pay->person->full_name }}</td>
                                        <td><span class="badge-perm" style="color: #34d399;">{{ $pay->payment_method }}</span></td>
                                        <td style="font-weight: 700; color: #fbbf24;">KES {{ number_format((float)$pay->amount, 2) }}</td>
                                        <td><span class="badge-status success">{{ $pay->status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── TAB 9: AUDIT TELEMETRY ── -->
            <div id="view-audit" class="spa-view">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Security & Login Audit Telemetry</h2>
                            <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">Immutable telemetry trail capturing authentication attempts, IP addresses, and outcomes.</p>
                        </div>
                        <span class="badge-status info">Active Telemetry</span>
                    </div>

                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Identifier (Email / Username)</th>
                                <th>Outcome</th>
                                <th>Client IP</th>
                                <th>Failure Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($loginAttempts as $log)
                                <tr>
                                    <td style="font-family: 'JetBrains Mono'; font-size: 0.78rem; color: var(--text-muted);">{{ $log->attempted_at }}</td>
                                    <td style="font-weight: 700; color: #fff;">{{ $log->email }}</td>
                                    <td>
                                        <span class="badge-status {{ $log->succeeded ? 'success' : 'warning' }}">
                                            {{ $log->succeeded ? 'SUCCESS' : 'FAILED' }}
                                        </span>
                                    </td>
                                    <td style="font-family: 'JetBrains Mono'; font-size: 0.78rem;">{{ $log->ip_address }}</td>
                                    <td style="color: #f87171; font-size: 0.78rem;">{{ $log->failure_reason ?? 'None (Authorized)' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted);">No audit telemetry captured yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Interactive SPA Tab Switcher -->
    <script>
        function switchView(viewName, el) {
            // 1. Hide all views
            document.querySelectorAll('.spa-view').forEach(v => v.classList.remove('active-view'));

            // 2. Show selected view
            const target = document.getElementById('view-' + viewName);
            if (target) {
                target.classList.add('active-view');
            }

            // 3. Update sidebar active item
            if (el) {
                document.querySelectorAll('.sidebar .nav-item').forEach(i => i.classList.remove('active'));
                el.classList.add('active');
            }

            // 4. Update hash in URL
            window.location.hash = viewName;
        }

        // Handle direct URL hash links on load
        window.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                const targetView = document.getElementById('view-' + hash);
                if (targetView) {
                    const navItems = Array.from(document.querySelectorAll('.sidebar .nav-item'));
                    const matchedNav = navItems.find(i => i.getAttribute('onclick')?.includes(`'${hash}'`));
                    switchView(hash, matchedNav);
                }
            }
        });
    </script>
</body>
</html>
