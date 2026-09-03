<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | MEMA ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* ── Skeleton shimmer (reusable in views) ──── */
        @keyframes mema-skeleton-sweep {
            0%   { background-position: -400px 0; }
            100% { background-position:  400px 0; }
        }
        .skel {
            background: linear-gradient(90deg,
                #f0f4f3 25%,
                #e2ebe7 50%,
                #f0f4f3 75%
            );
            background-size: 800px 100%;
            animation: mema-skeleton-sweep 1.5s ease-in-out infinite;
            border-radius: 5px;
        }
        .skel-row {
            display: grid;
            grid-template-columns: 2fr 3fr 2fr 1fr 1fr 80px;
            gap: 12px;
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f3;
            align-items: center;
        }
        .skel-row > span {
            height: 13px;
            border-radius: 4px;
        }
        .skel-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .skel-card .skel-h { height: 16px; width: 60%; }
        .skel-card .skel-p { height: 11px; }
        .skel-card .skel-p.w-80 { width: 80%; }
        .skel-card .skel-p.w-60 { width: 60%; }
        .skel-card .skel-p.w-40 { width: 40%; }

        :root {
            --primary: #0A3E50;
            --primary-dark: #072c39;
            --secondary: #1E8449;
            --accent: #E67E22;
            --accent-light: #fbeee4;
            --teal-theme: #007A8C;
            --white: #FFFFFF;
            --ink: #17211d;
            --muted: #64748b;
            --line: #e2e8f0;
            --paper: #f8fafc;
            --green: #1E8449;
            --green2: #e6f4ea;
            --gold: #E67E22;
            --red: #dc2626;
            --font-system: 'Quicksand', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        * {
            box-sizing: border-box;
            letter-spacing: 0;
            font-family: var(--font-system);
        }

        body {
            margin: 0;
            font-family: var(--font-system);
            font-size: 14px;
            line-height: 1.45;
            color: var(--ink);
            background: var(--paper);
        }

        .font-quicksand {
            font-family: var(--font-system);
        }

        /* Topbar styling matching screenshots */
        .topbar {
            height: 60px;
            background: #ffffff;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .topbar-collapse-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            color: var(--accent);
            border-radius: 4px;
            cursor: pointer;
            border: none;
            background: transparent;
            font-size: 18px;
            font-weight: bold;
        }

        .topbar-institution-title {
            color: var(--accent);
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.2px;
            text-align: center;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .font-resizers {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-text-size {
            background: none;
            border: none;
            cursor: pointer;
            color: #64748b;
            font-size: 11px;
            padding: 2px 4px;
            border-radius: 3px;
        }

        .btn-text-size:hover {
            color: var(--primary);
            background: #f1f5f9;
        }

        .role-dropdown-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .user-profile-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .user-profile-badge .avatar-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-profile-badge .avatar-fallback {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #0A3E50;
            color: #ffffff;
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 12px;
        }

        .user-profile-badge .user-details {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
            text-align: left;
        }

        .user-profile-badge .user-name {
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.3px;
        }

        .user-profile-badge .user-role {
            font-size: 10.5px;
            color: #94a3b8;
            font-weight: 600;
        }

        /* MEMA Dashboard Styles */
        .mema-dashboard-container,
        .ouk-dashboard-container {
            width: 100%;
            max-width: 1560px;
            margin: 0 auto;
            padding: 12px 16px;
        }

        .ouk-section {
            margin-bottom: 24px;
        }

        .ouk-section-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .ouk-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 20px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
        }

        .ouk-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        @media (max-width: 1024px) {
            .ouk-kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .ouk-kpi-grid {
                grid-template-columns: 1fr;
            }
        }

        .ouk-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
            min-height: 148px;
        }

        .kpi-header {
            margin-bottom: 4px;
        }

        .kpi-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }

        .kpi-alumni-tag {
            font-size: 11px;
            font-weight: 700;
            color: #2563eb;
        }

        .kpi-metric-row {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 4px;
        }

        .kpi-big-number {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .kpi-sub-pill {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .kpi-subtitle {
            font-size: 11px;
            color: #94a3b8;
            margin-bottom: 8px;
        }

        .kpi-badge-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .kpi-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 10.5px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 12px;
            width: fit-content;
        }

        .kpi-badge-blue {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #dbeafe;
        }

        .kpi-badge-red {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fee2e2;
        }

        .kpi-badge-green {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #dcfce7;
        }

        .kpi-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 6px;
        }

        .kpi-eye-btn {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 2px;
            border-radius: 4px;
            transition: color 0.15s ease;
        }

        .kpi-eye-btn:hover {
            color: var(--primary);
        }

        /* Admissions and Registration grid */
        .ouk-adm-reg-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 16px;
        }

        @media (max-width: 900px) {
            .ouk-adm-reg-grid {
                grid-template-columns: 1fr;
            }
        }

        .ouk-stats-summary-panel {
            display: flex;
            flex-direction: column;
            gap: 16px;
            background: #ffffff;
        }

        .stat-block {
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 12px;
        }

        .stat-block:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .stat-title-caps {
            font-size: 11px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }

        .stat-title-regular {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 2px;
        }

        .stat-val-large {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
        }

        .stat-val-medium {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .stat-note {
            font-size: 10.5px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .stat-rate-row {
            display: flex;
            align-items: baseline;
            gap: 8px;
        }

        .stat-rate-val {
            font-size: 20px;
            font-weight: 800;
        }

        .stat-accepted-tag {
            font-size: 11px;
            font-weight: 600;
            color: #16a34a;
        }

        .source-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 4px;
        }

        .source-item {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #334155;
        }

        .panel-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .panel-chart-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .panel-chart-subtitle {
            font-size: 11px;
            color: #64748b;
            margin: 2px 0 0;
        }

        .ouk-select {
            font-size: 11.5px;
            font-weight: 600;
            color: #334155;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            padding: 4px 8px;
            cursor: pointer;
            outline: none;
        }

        .ouk-btn-export {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11.5px;
            font-weight: 700;
            color: var(--accent);
            background: #ffffff;
            border: 1px solid #fed7aa;
            border-radius: 5px;
            padding: 4px 10px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .ouk-btn-export:hover {
            background: #fff7ed;
        }

        /* Programmes stat strip */
        .programmes-stat-strip {
            display: flex;
            align-items: center;
            gap: 32px;
            padding: 8px 16px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            flex-wrap: wrap;
        }

        .strip-item {
            display: flex;
            flex-direction: column;
        }

        .strip-headline {
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .strip-sub {
            font-size: 10.5px;
            color: #64748b;
        }

        /* Schools grid */
        .schools-grid {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 20px;
        }

        @media (max-width: 900px) {
            .schools-grid {
                grid-template-columns: 1fr;
            }
        }

        .geo-tab-btn {
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 4px;
            border: 1px solid transparent;
            background: transparent;
            color: #64748b;
            cursor: pointer;
        }

        .geo-tab-btn.active {
            color: var(--accent);
            border-color: var(--accent);
            background: #fffaf5;
        }
            z-index: 15
        }

        .topbar h1 {
            font-size: 18px;
            margin: 0
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 9px
        }

        .role {
            background: var(--green2);
            color: var(--green);
            font-size: 11px;
            font-weight: 700;
            padding: 5px 8px;
            border-radius: 4px;
            text-transform: uppercase
        }

        .content {
            padding: 28px 32px;
            max-width: 1440px
        }

        .eyebrow {
            color: var(--green);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px
        }

        .heading {
            font-size: 27px;
            margin: 4px 0 5px
        }

        .sub {
            color: var(--muted);
            margin: 0 0 24px
        }

        .grid4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px
        }

        .stat,
        .panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 7px
        }

        .stat {
            padding: 18px
        }

        .stat-head {
            display: flex;
            justify-content: space-between;
            color: var(--muted)
        }

        .stat svg {
            width: 18px;
            color: var(--green)
        }

        .stat b {
            display: block;
            font-size: 28px;
            margin-top: 10px
        }

        .stat b.compact-value {
            font-size: 16px;
            min-height: 42px
        }

        .stat small {
            color: var(--muted)
        }

        .cols {
            display: grid;
            grid-template-columns: 1.45fr .8fr;
            gap: 18px;
            margin-top: 18px
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 17px 19px;
            border-bottom: 1px solid var(--line)
        }

        .panel-head h2 {
            font-size: 15px;
            margin: 0
        }

        .panel-body {
            padding: 18px
        }

        .table-wrap {
            overflow: auto
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th {
            text-align: left;
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            padding: 11px 14px;
            border-bottom: 1px solid var(--line)
        }

        thead tr.text-white th,
        tr.text-white th,
        thead.text-white th,
        th.text-white,
        th.text-white span,
        th.text-white i {
            color: #ffffff !important;
        }

        td {
            padding: 13px 14px;
            border-bottom: 1px solid #edf0ee
        }

        tr:last-child td {
            border-bottom: 0
        }

        .person {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .person .avatar {
            width: 30px;
            height: 30px;
            font-size: 11px;
            background: #dce9e3
        }

        .badge {
            font-size: 11px;
            background: #edf2ef;
            color: #466057;
            padding: 4px 7px;
            border-radius: 4px
        }

        .btn {
            border: 0;
            background: var(--primary);
            color: #fff;
            padding: 9px 13px;
            border-radius: 5px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px
        }

        .btn svg {
            width: 15px
        }

        .btn-secondary {
            background: #fff;
            color: var(--ink);
            border: 1px solid var(--line)
        }

        .btn-danger {
            background: none;
            color: var(--red);
            padding: 5px
        }

        .page-head {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            align-items: end;
            margin-bottom: 22px
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 13px
        }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 5px
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            border: 1px solid #cfd7d2;
            background: white;
            padding: 10px;
            border-radius: 5px;
            font: inherit
        }

        .field.full {
            grid-column: 1/-1
        }

        .alert {
            padding: 11px 14px;
            border-radius: 5px;
            margin-bottom: 16px;
            background: var(--green2);
            color: var(--green)
        }

        .alert-impersonating {
            background: #fff1d9;
            color: #714710;
            border: 1px solid #e9c98d;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px
        }

        .error {
            background: #fbeaea;
            color: #922
        }

        .empty {
            padding: 32px;
            text-align: center;
            color: var(--muted)
        }

        .score {
            font-weight: 800
        }

        .score.good {
            color: var(--green)
        }

        .pagination {
            padding: 15px
        }

        .progress {
            height: 8px;
            background: #e7ece9;
            border-radius: 4px
        }

        .progress span {
            display: block;
            height: 100%;
            background: var(--gold);
            border-radius: 4px
        }

        .result-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 11px 0;
            border-bottom: 1px solid var(--line)
        }

        .result-line span small {
            display: block;
            color: var(--muted)
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: #0008;
            z-index: 40;
            align-items: center;
            justify-content: center
        }

        .modal.open {
            display: flex
        }

        .modal-card {
            background: white;
            width: min(560px, 92vw);
            border-radius: 7px
        }

        .modal-card .panel-body {
            max-height: 75vh;
            overflow: auto
        }

        /* Menu Search Modal styling matching screenshots with MEMA system colors */
        .menu-search-card {
            background: var(--white);
            width: min(640px, 94vw);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
            font-family: var(--font-system);
        }

        .menu-search-head {
            background: var(--primary);
            color: var(--white);
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .menu-search-head h2 {
            color: var(--white);
            font-size: 15px;
            font-weight: 600;
            margin: 0;
            letter-spacing: -0.01em;
            font-family: var(--font-system);
        }

        .menu-search-head .close-btn {
            background: transparent;
            border: none;
            color: var(--white);
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.85;
            transition: opacity 0.15s;
        }

        .menu-search-head .close-btn:hover {
            opacity: 1;
        }

        .menu-search-body {
            padding: 20px;
            background: var(--white);
            font-family: var(--font-system);
        }

        .menu-search-inset-box {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 22px;
            background: var(--white);
        }

        .menu-search-field-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 6px;
            font-family: var(--font-system);
        }

        .menu-search-trigger {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 9px 14px;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13.5px;
            color: var(--ink);
            cursor: pointer;
            transition: border-color 0.15s, box-shadow 0.15s;
            font-family: var(--font-system);
        }

        .menu-search-trigger:hover,
        .menu-search-trigger.active {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(10, 62, 80, 0.12);
        }

        .menu-search-dropdown {
            margin-top: 6px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: var(--white);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .menu-search-input-wrap {
            padding: 8px 12px;
            border-bottom: 1px solid var(--line);
        }

        .menu-search-input-wrap input {
            width: 100%;
            border: none;
            outline: none;
            font-size: 13.5px;
            color: var(--ink);
            padding: 4px 0;
            background: transparent;
            font-family: var(--font-system);
        }

        .menu-search-input-wrap input::placeholder {
            color: var(--muted);
            font-family: var(--font-system);
        }

        .menu-search-list {
            max-height: 250px;
            overflow-y: auto;
            list-style: none;
            margin: 0;
            padding: 4px 0;
        }

        .menu-search-list::-webkit-scrollbar {
            width: 6px;
        }

        .menu-search-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .menu-search-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 16px;
            cursor: pointer;
            font-size: 13.5px;
            color: var(--ink);
            transition: background-color 0.12s;
            user-select: none;
            text-decoration: none;
            font-family: var(--font-system);
        }

        .menu-search-item:hover,
        .menu-search-item:focus {
            background: rgba(10, 62, 80, 0.05);
        }

        .menu-search-item .checkbox-box {
            width: 17px;
            height: 17px;
            border: 2px solid var(--primary);
            border-radius: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: var(--white);
            transition: all 0.12s;
        }

        .menu-search-item.checked .checkbox-box {
            background: var(--primary);
            border-color: var(--primary);
        }

        .menu-search-item.checked .checkbox-box::after {
            content: '';
            width: 5px;
            height: 9px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            margin-bottom: 2px;
        }

        .menu-search-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 18px;
        }

        .btn-close-red {
            background: var(--red, #dc2626);
            color: var(--white);
            border: none;
            border-radius: 6px;
            padding: 7px 18px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
            font-family: var(--font-system);
        }

        .btn-close-red:hover {
            background: #b91c1c;
        }

        /* Dynamic Coming Soon Modal Styling */
        .coming-soon-card {
            background: var(--white);
            width: min(620px, 94vw);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 25px 35px -5px rgba(0, 0, 0, 0.25), 0 15px 15px -5px rgba(0, 0, 0, 0.1);
            font-family: var(--font-system);
            border: 1px solid var(--line);
            animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.96) translateY(8px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .coming-soon-header-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 20px 24px;
            position: relative;
        }

        .coming-soon-badge-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .coming-soon-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .coming-soon-hero {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .coming-soon-icon-box {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .coming-soon-title {
            font-size: 19px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 3px 0;
            line-height: 1.25;
        }

        .coming-soon-desc {
            font-size: 12.5px;
            color: rgba(255, 255, 255, 0.88);
            margin: 0;
            line-height: 1.4;
        }

        .coming-soon-body {
            padding: 22px 24px;
            background: var(--white);
        }

        .coming-soon-progress-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 20px;
        }

        .coming-soon-progress-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .coming-soon-progress-bar {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 9999px;
            overflow: hidden;
            position: relative;
        }

        .coming-soon-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #007A8C, #1E8449);
            border-radius: 9999px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .coming-soon-feature-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            margin-bottom: 20px;
        }

        @media(min-width: 500px) {
            .coming-soon-feature-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .coming-soon-feature-item {
            display: flex;
            align-items: start;
            gap: 10px;
            padding: 10px 12px;
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            font-size: 12px;
            color: #334155;
            font-weight: 500;
        }

        .coming-soon-feature-item i {
            color: #10B981;
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .coming-soon-notify-box {
            display: flex;
            gap: 8px;
            background: #f1f5f9;
            padding: 6px;
            border-radius: 8px;
        }

        .coming-soon-notify-box input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            font-size: 12.5px;
            color: #1e293b;
            padding: 6px 10px;
            font-family: var(--font-system);
        }

        .coming-soon-notify-btn {
            background: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s;
            font-family: var(--font-system);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .coming-soon-notify-btn:hover {
            background: #007A8C;
        }

        .coming-soon-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
        }

        .user-menu {
            position: relative
        }

        .user-menu summary {
            list-style: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            min-height: 44px;
            border-radius: 50%;
            padding: 2px;
            outline: none
        }

        .user-menu summary::-webkit-details-marker {
            display: none
        }

        .user-menu summary:hover,
        .user-menu summary:focus-visible {
            box-shadow: 0 0 0 2px var(--green)
        }

        .user-menu .avatar {
            width: 40px;
            height: 40px
        }

        .menu-popover {
            position: absolute;
            right: 0;
            top: 50px;
            width: 280px;
            padding: 8px;
            background: white;
            border: 1px solid #0000001a;
            border-radius: 8px;
            box-shadow: 0 4px 12px #00000026;
            z-index: 30;
            animation: menu-open .15s ease-out
        }

        .menu-identity {
            padding: 8px 12px
        }

        .menu-identity strong,
        .menu-identity small {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        .menu-identity small {
            color: var(--muted)
        }

        .menu-item {
            width: 100%;
            min-height: 40px;
            padding: 0 12px;
            border: 0;
            border-radius: 5px;
            background: none;
            color: #1a1a1a;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font: inherit;
            cursor: pointer;
            text-align: left
        }

        .menu-item:hover,
        .menu-item:focus-visible {
            background: #f5f5f5;
            outline: 2px solid transparent
        }

        .menu-item svg {
            width: 18px;
            height: 18px;
            color: var(--muted)
        }

        .menu-item.danger {
            color: #9b2929
        }

        .menu-divider {
            height: 1px;
            background: #e0e0e0;
            margin: 8px
        }

        .profile-summary {
            display: flex;
            align-items: center;
            gap: 15px
        }

        .profile-summary h2,
        .profile-summary p {
            margin: 0
        }

        .profile-summary p {
            color: var(--muted)
        }

        .profile-avatar {
            width: 56px;
            height: 56px
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-top: 25px
        }

        .detail-grid dt {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--muted)
        }

        .detail-grid dd {
            margin: 4px 0 0;
            font-weight: 650
        }

        .account-link {
            text-decoration: none;
            color: inherit
        }

        .preference-tabs {
            display: flex;
            gap: 5px;
            border-bottom: 1px solid var(--line);
            margin-bottom: 16px
        }

        .preference-tab {
            border: 0;
            background: none;
            padding: 10px 8px;
            font-weight: 650;
            cursor: pointer
        }

        .preference-tab.active {
            color: var(--green);
            border-bottom: 2px solid var(--green)
        }

        .preference-panel {
            display: none
        }

        .preference-panel.active {
            display: block
        }

        .check-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 45px;
            border-bottom: 1px solid var(--line)
        }

        @keyframes menu-open {
            from {
                opacity: 0;
                transform: translateY(-8px) scale(.98)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @media(max-width:900px) {

            .grid4 {
                grid-template-columns: repeat(2, 1fr)
            }

            .cols {
                grid-template-columns: 1fr
            }

            .content {
                padding: 22px 18px
            }

            .topbar {
                padding: 0 18px
            }
        }

        @media(max-width:560px) {

            .grid4 {
                grid-template-columns: 1fr 1fr
            }

            .stat {
                padding: 14px
            }

            .page-head {
                align-items: start
            }

            .form-grid,
            .detail-grid {
                grid-template-columns: 1fr
            }

            .field.full {
                grid-column: auto
            }

            .topbar {
                height: 58px
            }

            .alert-impersonating {
                align-items: start;
                flex-direction: column
            }

            .menu-popover {
                position: fixed;
                left: 0;
                right: 0;
                top: auto;
                bottom: 0;
                width: 100%;
                border-radius: 12px 12px 0 0;
                padding: 12px 12px 18px
            }

            .top-actions>.role {
                display: none
            }
        }
    </style>

</head>

<body class="admissions-page">
    <a class="skip-link" href="#main-content">Skip to main content</a>
    {{-- The collapsed preference is echoed from a cookie so the sidebar paints at its
         persisted width instead of flashing open while sidebar.js boots. --}}
    <div class="shell{{ request()->cookie('sidebar_collapsed') === 'true' ? ' sidebar-collapsed' : '' }}"
        id="app-shell">
        <aside class="sidebar" id="app-sidebar">
            <div class="sidebar-header">
                <a class="brand" href="{{ route('dashboard') }}">
                    <span class="mark" aria-hidden="true">
                        <img src="{{ asset('images/system/logos/mema-college-mark-192.png') }}" alt="MEMA" style="width:28px;height:28px;object-fit:contain;border-radius:4px;">
                    </span>
                    <div><strong>MEMA</strong><small>ERP Portal</small></div>
                </a>
                <button class="sidebar-collapse-toggle" type="button" aria-controls="app-sidebar" aria-expanded="true"
                    aria-label="Collapse sidebar"><i data-lucide="panel-left-close" aria-hidden="true"></i></button>
                <button class="sidebar-close" type="button" aria-controls="app-sidebar"
                    aria-label="Close navigation"><i data-lucide="x" aria-hidden="true"></i></button>
            </div>

            @php($current = fn (bool $active) => $active ? 'class="active" aria-current="page"' : '')
            <nav class="nav" aria-label="Primary">
                <ul aria-label="Main Navigation">
                    <li><a href="{{ route('dashboard') }}" {!! $current(request()->routeIs('dashboard')) !!}><i
                                data-lucide="layout-grid"></i><span>Dashboard</span></a></li>
                    <li><a href="#menu-search" data-modal-open="menu-search-modal" id="sidebar-menu-search"><i data-lucide="search"></i><span>Menu Search</span></a></li>

                    {{-- 1. ACADEMIC ARCHITECTURE & PLANNING --}}
                    <li>
                        <details class="nav-group" data-nav-group="curriculum" {{ request()->routeIs('curriculum.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="list-tree"></i>
                                <span>Curriculum Setup</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('curriculum.school') }}" {!! $current(request()->routeIs('curriculum.school') || request()->routeIs('curriculum.index')) !!}>School</a>
                                <a href="{{ route('curriculum.department') }}" {!! $current(request()->routeIs('curriculum.department')) !!}>Department</a>
                                <a href="{{ route('curriculum.program-type') }}" {!! $current(request()->routeIs('curriculum.program-type')) !!}>Program Type</a>
                                <a href="{{ route('curriculum.programme') }}" {!! $current(request()->routeIs('curriculum.programme')) !!}>Programme</a>
                                <a href="{{ route('curriculum.programme-curriculum') }}" {!! $current(request()->routeIs('curriculum.programme-curriculum')) !!}>Programme Curriculum</a>
                                <a href="{{ route('curriculum.course-unit') }}" {!! $current(request()->routeIs('curriculum.course-unit')) !!}>Course Unit</a>
                                <a href="{{ route('curriculum.specialisation') }}" {!! $current(request()->routeIs('curriculum.specialisation')) !!}>Specialisation</a>
                                <a href="{{ route('curriculum.student-specialization-mapping') }}" {!! $current(request()->routeIs('curriculum.student-specialization-mapping')) !!}>Student Specialization Mapping</a>
                                <a href="{{ route('curriculum.instructor-mapping') }}" {!! $current(request()->routeIs('curriculum.instructor-mapping')) !!}>Instructor Mapping</a>
                                <a href="{{ route('curriculum.cluster-subjects') }}" {!! $current(request()->routeIs('curriculum.cluster-subjects')) !!}>Cluster Subjects</a>
                                <a href="{{ route('curriculum.program-cluster-mapping') }}" {!! $current(request()->routeIs('curriculum.program-cluster-mapping')) !!}>Program Cluster Mapping</a>
                                <a href="{{ route('curriculum.progression-criteria') }}" {!! $current(request()->routeIs('curriculum.progression-criteria')) !!}>Progression Criteria</a>
                                <a href="{{ route('curriculum.short-course-creation') }}" {!! $current(request()->routeIs('curriculum.short-course-creation')) !!}>Short Course Creation</a>
                            </div>
                        </details>
                    </li>
                    <li>
                        <details class="nav-group" data-nav-group="cohort" {{ request()->routeIs('cohort.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="layers"></i>
                                <span>Cohort Setup</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('cohort.academic-year') }}" {!! $current(request()->routeIs('cohort.academic-year') || request()->routeIs('cohort.index')) !!}>Academic Year</a>
                                <a href="{{ route('cohort.cohort-creation') }}" {!! $current(request()->routeIs('cohort.cohort-creation')) !!}>Cohort Creation</a>
                                <a href="{{ route('cohort.programme-cohort-mapping') }}" {!! $current(request()->routeIs('cohort.programme-cohort-mapping')) !!}>Programme Cohort Mapping List</a>
                                <a href="{{ route('cohort.publish-finance') }}" {!! $current(request()->routeIs('cohort.publish-finance')) !!}>Programme Cohort Publish - Finance</a>
                                <a href="{{ route('cohort.cohort-transfer') }}" {!! $current(request()->routeIs('cohort.cohort-transfer')) !!}>Cohort Transfer</a>
                            </div>
                        </details>
                    </li>

                    {{-- 2. STUDENT INTAKE & ADMISSIONS LIFECYCLE --}}
                    <li>
                        <details class="nav-group" data-nav-group="admissions" {{ request()->routeIs('admissions.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="clipboard-list"></i>
                                <span>Admissions</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                @if (in_array(auth()->user()->role, ['admin', 'staff'], true))
                                <a href="{{ route('admissions.workspace.dashboard') }}" {!! $current(request()->routeIs('admissions.workspace.dashboard')) !!}>Admissions Dashboard</a>
                                <a href="{{ route('admissions.index') }}" {!! $current(request()->routeIs('admissions.index') || request()->routeIs('admissions.show')) !!}>Applications &amp; Review</a>
                                <a href="{{ route('admissions.workspace.document-verification') }}" {!! $current(request()->routeIs('admissions.workspace.document-verification')) !!}>Document Verification</a>
                                <a href="{{ route('admissions.workspace.offers') }}" {!! $current(request()->routeIs('admissions.workspace.offers')) !!}>Offers &amp; Letters</a>
                                <a href="{{ route('admissions.conversions') }}" {!! $current(request()->routeIs('admissions.conversions')) !!}>Student Enrolment</a>
                                <a href="{{ route('admissions.setups.index') }}" {!! $current(request()->routeIs('admissions.setups.*')) !!}>Programme &amp; Intake Setups</a>
                                <a href="{{ route('admissions.reports') }}" {!! $current(request()->routeIs('admissions.reports') || request()->routeIs('admissions.analytics')) !!}>Reports &amp; Analytics</a>
                                <a href="{{ route('admissions.catalogue') }}" target="_blank" rel="noopener" {!! $current(false) !!}>Public Catalogue <i data-lucide="external-link" class="w-3 h-3 inline ml-1 opacity-60"></i></a>
                                @endif
                                <a href="{{ route('admissions.portal') }}" {!! $current(request()->routeIs('admissions.portal')) !!}>Applicant Portal</a>
                            </div>
                        </details>
                    </li>
                    <li>
                        <details class="nav-group" data-nav-group="registration" {{ request()->routeIs('registration.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="user-check"></i>
                                <span>Registration</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('registration.application-verification') }}" {!! $current(request()->routeIs('registration.application-verification') || request()->routeIs('registration.index')) !!}>Application Verification</a>
                                <a href="{{ route('registration.application-approval') }}" {!! $current(request()->routeIs('registration.application-approval')) !!}>Application Approval</a>
                                <a href="{{ route('registration.rejected-list') }}" {!! $current(request()->routeIs('registration.rejected-list')) !!}>Rejected List</a>
                                <a href="{{ route('registration.kuccps-registration') }}" {!! $current(request()->routeIs('registration.kuccps-registration')) !!}>KUCCPS Student Registration</a>
                                <a href="{{ route('registration.student-registrations') }}" {!! $current(request()->routeIs('registration.student-registrations')) !!}>Student Registrations</a>
                                <a href="{{ route('registration.course-registration-periods') }}" {!! $current(request()->routeIs('registration.course-registration-periods')) !!}>Course Registration and Confirmation Periods</a>
                                <a href="{{ route('registration.promotions') }}" {!! $current(request()->routeIs('registration.promotions')) !!}>Promotions</a>
                                <a href="{{ route('registration.professional-development-users') }}" {!! $current(request()->routeIs('registration.professional-development-users')) !!}>Professional Development Courses User List</a>
                                <a href="{{ route('registration.moodle-sync') }}" {!! $current(request()->routeIs('registration.moodle-sync')) !!}>ERP-Moodle Course Unit Sync</a>
                                <a href="{{ route('registration.student-info-update') }}" {!! $current(request()->routeIs('registration.student-info-update')) !!}>Student Information Update</a>
                                <a href="{{ route('registration.reminders') }}" {!! $current(request()->routeIs('registration.reminders')) !!}>Reminder</a>
                                <a href="{{ route('registration.user-registration') }}" {!! $current(request()->routeIs('registration.user-registration')) !!}>User Registration</a>
                                <a href="{{ route('registration.student-password') }}" {!! $current(request()->routeIs('registration.student-password')) !!}>Student Password</a>
                                <a href="{{ route('registration.staff-password') }}" {!! $current(request()->routeIs('registration.staff-password')) !!}>Staff Password</a>
                                <a href="{{ route('registration.password-reset') }}" {!! $current(request()->routeIs('registration.password-reset')) !!}>Password reset</a>
                            </div>
                        </details>
                    </li>
                    <li>
                        <details class="nav-group" data-nav-group="transfers" {{ request()->routeIs('transfers.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="arrow-left-right"></i>
                                <span>Student Transfers</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('transfers.dates-setup') }}" {!! $current(request()->routeIs('transfers.dates-setup') || request()->routeIs('transfers.index')) !!}>Transfer Dates Setup</a>
                                <a href="{{ route('transfers.inter-intra') }}" {!! $current(request()->routeIs('transfers.inter-intra')) !!}>Inter/Intra Faculty Transfers</a>
                                <a href="{{ route('transfers.credit-transfers') }}" {!! $current(request()->routeIs('transfers.credit-transfers')) !!}>Credit Transfers</a>
                                <a href="{{ route('transfers.exemptions') }}" {!! $current(request()->routeIs('transfers.exemptions')) !!}>Exemptions</a>
                            </div>
                        </details>
                    </li>

                    {{-- 3. ACADEMIC DELIVERY, EXAMS & RESEARCH --}}
                    <li>
                        <details class="nav-group" data-nav-group="lms" {{ request()->routeIs('lms.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="laptop"></i>
                                <span>LMS</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('lms.course-shells') }}" {!! $current(request()->routeIs('lms.course-shells') || request()->routeIs('lms.index')) !!}>Virtual Classrooms</a>
                                <a href="{{ route('lms.lecturer-assignments') }}" {!! $current(request()->routeIs('lms.lecturer-assignments')) !!}>Faculty Assignments</a>
                                <a href="{{ route('lms.live-lectures') }}" {!! $current(request()->routeIs('lms.live-lectures')) !!}>Live Virtual Lectures</a>
                                <a href="{{ route('lms.e-resources') }}" {!! $current(request()->routeIs('lms.e-resources')) !!}>Learning Materials</a>
                                <a href="{{ route('lms.assignments') }}" {!! $current(request()->routeIs('lms.assignments')) !!}>Continuous Assessment</a>
                                <a href="{{ route('lms.student-analytics') }}" {!! $current(request()->routeIs('lms.student-analytics')) !!}>Engagement Analytics</a>
                                <a href="{{ route('lms.discussion-forums') }}" {!! $current(request()->routeIs('lms.discussion-forums')) !!}>Discussion Forums</a>
                                <a href="{{ route('lms.online-quizzes') }}" {!! $current(request()->routeIs('lms.online-quizzes')) !!}>Online Quizzes</a>
                                <a href="{{ route('lms.gradebook-sync') }}" {!! $current(request()->routeIs('lms.gradebook-sync')) !!}>Gradebook & Marks Sync</a>
                            </div>
                        </details>
                    </li>
                    <li>
                        <details class="nav-group" data-nav-group="examination" {{ request()->routeIs('examination.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="file-signature"></i>
                                <span>Examination</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('examination.exam-center') }}" {!! $current(request()->routeIs('examination.exam-center') || request()->routeIs('examination.index')) !!}>Exam Center</a>
                                <a href="{{ route('examination.exam-session') }}" {!! $current(request()->routeIs('examination.exam-session')) !!}>Exam Session</a>
                                <a href="{{ route('examination.exam-schedule') }}" {!! $current(request()->routeIs('examination.exam-schedule')) !!}>Exam Schedule</a>
                                <a href="{{ route('examination.marks-capture') }}" {!! $current(request()->routeIs('examination.marks-capture')) !!}>Marks Capture</a>
                                <a href="{{ route('examination.marks-submission') }}" {!! $current(request()->routeIs('examination.marks-submission')) !!}>Marks Submission</a>
                                <a href="{{ route('examination.marks-approval') }}" {!! $current(request()->routeIs('examination.marks-approval')) !!}>Exam Marks Approval</a>
                                <a href="{{ route('examination.marks-publish') }}" {!! $current(request()->routeIs('examination.marks-publish')) !!}>Exam Marks Publish</a>
                                <a href="{{ route('examination.scores-analysis') }}" {!! $current(request()->routeIs('examination.scores-analysis')) !!}>Class Scores Analysis</a>
                                <a href="{{ route('examination.summary-results') }}" {!! $current(request()->routeIs('examination.summary-results')) !!}>Summary of Results</a>
                                <a href="{{ route('examination.grades-config') }}" {!! $current(request()->routeIs('examination.grades-config')) !!}>Grades & Scale Config</a>
                                <a href="{{ route('examination.pass-list') }}" {!! $current(request()->routeIs('examination.pass-list')) !!}>Pass List</a>
                                <a href="{{ route('examination.progression-list') }}" {!! $current(request()->routeIs('examination.progression-list')) !!}>Progression List</a>
                                <a href="{{ route('examination.fail-list') }}" {!! $current(request()->routeIs('examination.fail-list')) !!}>Fail List</a>
                                <a href="{{ route('examination.consolidated-marksheets') }}" {!! $current(request()->routeIs('examination.consolidated-marksheets')) !!}>Consolidated Marksheets</a>
                                <a href="{{ route('examination.senate-reports') }}" {!! $current(request()->routeIs('examination.senate-reports')) !!}>Exam Senate Reports</a>
                                
                                <span class="px-3 py-1 text-[10px] uppercase font-bold text-slate-400 block tracking-wider mt-2 border-t border-slate-700/20 pt-2">Transcripts</span>
                                <a href="{{ route('examination.provisional-transcript') }}" {!! $current(request()->routeIs('examination.provisional-transcript')) !!}>Provisional Transcript</a>
                                <a href="{{ route('examination.academic-transcript') }}" {!! $current(request()->routeIs('examination.academic-transcript')) !!}>Academic Transcript</a>
                                <a href="{{ route('examination.transcript-requests') }}" {!! $current(request()->routeIs('examination.transcript-requests')) !!}>Transcript Requests</a>
                            </div>
                        </details>
                    </li>
                    <li>
                        <details class="nav-group" data-nav-group="pg-research" {{ request()->routeIs('pg-research.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="graduation-cap"></i>
                                <span>PG Research Management</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('pg-research.supervisor-roles') }}" {!! $current(request()->routeIs('pg-research.supervisor-roles') || request()->routeIs('pg-research.index')) !!}>Supervisor Role Configuration</a>
                                <a href="{{ route('pg-research.eligibility-gating') }}" {!! $current(request()->routeIs('pg-research.eligibility-gating')) !!}>Research Eligibility & Gating</a>
                                <a href="{{ route('pg-research.supervisor-allocation') }}" {!! $current(request()->routeIs('pg-research.supervisor-allocation')) !!}>Supervisor Workload Allocation</a>
                                <a href="{{ route('pg-research.proposal-reader-review') }}" {!! $current(request()->routeIs('pg-research.proposal-reader-review')) !!}>Proposal Reader Review</a>
                                <a href="{{ route('pg-research.seminar-presentations') }}" {!! $current(request()->routeIs('pg-research.seminar-presentations')) !!}>Seminar Presentations Tracking</a>
                                <a href="{{ route('pg-research.progress-reports') }}" {!! $current(request()->routeIs('pg-research.progress-reports')) !!}>Research Progress Reports</a>
                                <a href="{{ route('pg-research.plagiarism-checker') }}" {!! $current(request()->routeIs('pg-research.plagiarism-checker')) !!}>Plagiarism & AI Similarity Index</a>
                                <a href="{{ route('pg-research.defence-request-approval') }}" {!! $current(request()->routeIs('pg-research.defence-request-approval')) !!}>Defence Request Approval</a>
                                <a href="{{ route('pg-research.examiner-dashboard') }}" {!! $current(request()->routeIs('pg-research.examiner-dashboard')) !!}>Examiner Dashboard</a>
                                <a href="{{ route('pg-research.viva-examination') }}" {!! $current(request()->routeIs('pg-research.viva-examination')) !!}>Graduate Level Viva Examination</a>
                                <a href="{{ route('pg-research.thesis-marks-approval') }}" {!! $current(request()->routeIs('pg-research.thesis-marks-approval')) !!}>Thesis Marks Approval</a>
                                <a href="{{ route('pg-research.thesis-resubmission') }}" {!! $current(request()->routeIs('pg-research.thesis-resubmission')) !!}>Final Thesis Resubmission Review</a>
                                <a href="{{ route('pg-research.publications-review') }}" {!! $current(request()->routeIs('pg-research.publications-review')) !!}>Research Publications Review</a>
                                <a href="{{ route('pg-research.legacy-migration') }}" {!! $current(request()->routeIs('pg-research.legacy-migration')) !!}>Legacy Projects & Migration</a>
                                <a href="{{ route('pg-research.appeal-period-setup') }}" {!! $current(request()->routeIs('pg-research.appeal-period-setup')) !!}>PG Appeal Period Setup</a>
                                <a href="{{ route('pg-research.appeal-category') }}" {!! $current(request()->routeIs('pg-research.appeal-category')) !!}>PG Appeal Category</a>
                            </div>
                        </details>
                    </li>

                    {{-- 4. STUDENT AFFAIRS & CAMPUS LIFE --}}
                    <li>
                        <details class="nav-group" data-nav-group="student-affairs" {{ request()->routeIs('work-study.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="heart-handshake"></i>
                                <span>Student Affairs</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('work-study.period-setup') }}" {!! $current(request()->routeIs('work-study.period-setup') || request()->routeIs('work-study.index')) !!}>Work Study: Period Setup</a>
                                <a href="{{ route('work-study.positions') }}" {!! $current(request()->routeIs('work-study.positions')) !!}>Work Study: Positions</a>
                                <a href="{{ route('work-study.applications') }}" {!! $current(request()->routeIs('work-study.applications')) !!}>Work Study: Applications</a>
                                <a href="{{ route('work-study.allocations') }}" {!! $current(request()->routeIs('work-study.allocations')) !!}>Work Study: Allocations</a>
                                <a href="{{ route('work-study.timesheets') }}" {!! $current(request()->routeIs('work-study.timesheets')) !!}>Work Study: Timesheets</a>
                                <a href="{{ route('work-study.claims') }}" {!! $current(request()->routeIs('work-study.claims')) !!}>Work Study: Claims & Payroll</a>
                                <a href="#student-disciplinary">Student Disciplinary</a>
                                <a href="#clubs-societies">Clubs & Societies</a>
                                <a href="#election-management">Election Management</a>
                            </div>
                        </details>
                    </li>

                    {{-- 5. GRADUATION & ALUMNI --}}
                    <li>
                        <details class="nav-group" data-nav-group="graduation" {{ request()->routeIs('graduation.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="award"></i>
                                <span>Graduation</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('graduation.criteria') }}" {!! $current(request()->routeIs('graduation.criteria') || request()->routeIs('graduation.index')) !!}>Graduation Criteria</a>
                                <a href="{{ route('graduation.clearance-checklist') }}" {!! $current(request()->routeIs('graduation.clearance-checklist')) !!}>Clearance Checklist</a>
                                <a href="{{ route('graduation.finance-clearance') }}" {!! $current(request()->routeIs('graduation.finance-clearance')) !!}>Finance Clearance</a>
                                <a href="{{ route('graduation.grade-list') }}" {!! $current(request()->routeIs('graduation.grade-list')) !!}>Graduation Grade List</a>
                                <a href="{{ route('graduation.generate-list') }}" {!! $current(request()->routeIs('graduation.generate-list')) !!}>Graduation List Generation</a>
                                <a href="{{ route('graduation.validate-list') }}" {!! $current(request()->routeIs('graduation.validate-list')) !!}>Validate Graduation List</a>
                                <a href="{{ route('graduation.publish-list') }}" {!! $current(request()->routeIs('graduation.publish-list')) !!}>Publish Graduation List</a>
                                <a href="{{ route('graduation.list-report') }}" {!! $current(request()->routeIs('graduation.list-report')) !!}>Graduation List Report</a>
                                <a href="{{ route('graduation.summary-list') }}" {!! $current(request()->routeIs('graduation.summary-list')) !!}>Graduation Summary List</a>
                                <a href="{{ route('graduation.certification-setup') }}" {!! $current(request()->routeIs('graduation.certification-setup')) !!}>Progressive Certification Setup</a>
                                <a href="{{ route('graduation.alumni-list') }}" {!! $current(request()->routeIs('graduation.alumni-list')) !!}>Alumni Student List</a>
                                <a href="{{ route('graduation.ceremony') }}" {!! $current(request()->routeIs('graduation.ceremony')) !!}>Graduation Ceremony</a>
                                <a href="{{ route('graduation.ceremony-report') }}" {!! $current(request()->routeIs('graduation.ceremony-report')) !!}>Graduation Ceremony Report</a>
                            </div>
                        </details>
                    </li>

                    {{-- 6. INSTITUTIONAL FINANCE & OPERATIONS --}}
                    <li>
                        <details class="nav-group" data-nav-group="fees" {{ request()->routeIs('fees.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="credit-card"></i>
                                <span>Fees</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('fees.payment-accounts') }}" {!! $current(request()->routeIs('fees.payment-accounts') || request()->routeIs('fees.index')) !!}>Payment Accounts</a>
                                <a href="{{ route('fees.payment-types') }}" {!! $current(request()->routeIs('fees.payment-types')) !!}>Payment Types</a>
                                <a href="{{ route('fees.payment-source') }}" {!! $current(request()->routeIs('fees.payment-source')) !!}>Payment Source</a>
                                <a href="{{ route('fees.fee-setup') }}" {!! $current(request()->routeIs('fees.fee-setup')) !!}>Fee Setup</a>
                                <a href="{{ route('fees.fee-payables') }}" {!! $current(request()->routeIs('fees.fee-payables')) !!}>Fee Payables</a>
                                <a href="{{ route('fees.pending-payments') }}" {!! $current(request()->routeIs('fees.pending-payments')) !!}>Pending Payment Confirmation</a>
                                <a href="{{ route('fees.payment-receipt') }}" {!! $current(request()->routeIs('fees.payment-receipt')) !!}>Payment Receipt</a>
                            </div>
                        </details>
                    </li>
                    <li>
                        <details class="nav-group" data-nav-group="budgeting" {{ request()->routeIs('budgeting.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="pie-chart"></i>
                                <span>Budgeting and Planning</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('budgeting.permissions') }}" {!! $current(request()->routeIs('budgeting.permissions') || request()->routeIs('budgeting.index')) !!}>Permission</a>
                                <a href="{{ route('budgeting.proposals') }}" {!! $current(request()->routeIs('budgeting.proposals')) !!}>Budget Proposals</a>
                            </div>
                        </details>
                    </li>
                    <li>
                        <details class="nav-group" data-nav-group="imprest" {{ request()->routeIs('imprest.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="wallet"></i>
                                <span>Imprest Management</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('imprest.permissions') }}" {!! $current(request()->routeIs('imprest.permissions') || request()->routeIs('imprest.index')) !!}>Imprest Permissions</a>
                                <a href="{{ route('imprest.claim-approvals') }}" {!! $current(request()->routeIs('imprest.claim-approvals')) !!}>Claim Approval Permission</a>
                                <a href="{{ route('imprest.surrender-permissions') }}" {!! $current(request()->routeIs('imprest.surrender-permissions')) !!}>Imprest Surrender Permission</a>
                                <a href="{{ route('imprest.requisitions') }}" {!! $current(request()->routeIs('imprest.requisitions')) !!}>Imprest Requisitions</a>
                                <a href="{{ route('imprest.surrenders') }}" {!! $current(request()->routeIs('imprest.surrenders')) !!}>Imprest Surrenders</a>
                                <a href="{{ route('imprest.audit-ledger') }}" {!! $current(request()->routeIs('imprest.audit-ledger')) !!}>Imprest Audit Ledger</a>
                            </div>
                        </details>
                    </li>
                    <li>
                        <details class="nav-group" data-nav-group="service-providers" {{ request()->routeIs('service-providers.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="building-2"></i>
                                <span>Service Providers</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('service-providers.taxes') }}" {!! $current(request()->routeIs('service-providers.taxes')) !!}>Taxes</a>
                                <a href="{{ route('service-providers.items') }}" {!! $current(request()->routeIs('service-providers.items')) !!}>Items</a>
                                <a href="{{ route('service-providers.provider-groups') }}" {!! $current(request()->routeIs('service-providers.provider-groups')) !!}>Provider Groups</a>
                                <a href="{{ route('service-providers.providers') }}" {!! $current(request()->routeIs('service-providers.providers') || request()->routeIs('service-providers.index')) !!}>Providers</a>
                                <a href="{{ route('service-providers.vendor-approval') }}" {!! $current(request()->routeIs('service-providers.vendor-approval')) !!}>vendor Reg Approval</a>
                                <a href="{{ route('service-providers.invoice-permissions') }}" {!! $current(request()->routeIs('service-providers.invoice-permissions')) !!}>Supplier Invoice Permission</a>
                                <a href="{{ route('service-providers.bills') }}" {!! $current(request()->routeIs('service-providers.bills')) !!}>Bills</a>
                                <a href="{{ route('service-providers.payment-permissions') }}" {!! $current(request()->routeIs('service-providers.payment-permissions')) !!}>Supplier Payment Permission</a>
                                <a href="{{ route('service-providers.payments') }}" {!! $current(request()->routeIs('service-providers.payments')) !!}>Payments</a>
                                <a href="{{ route('service-providers.debit-notes') }}" {!! $current(request()->routeIs('service-providers.debit-notes')) !!}>Debit Notes</a>
                                <a href="{{ route('service-providers.credit-notes') }}" {!! $current(request()->routeIs('service-providers.credit-notes')) !!}>Credit Notes</a>
                            </div>
                        </details>
                    </li>

                    {{-- 7. HUMAN CAPITAL MANAGEMENT (STAFF & HR) --}}
                    <li>
                        <details class="nav-group" data-nav-group="smhr" {{ request()->routeIs('smhr.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="users"></i>
                                <span>SMHR — Staff &amp; HR</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('smhr.dashboard') }}" {!! $current(request()->routeIs('smhr.dashboard') || request()->routeIs('smhr.index')) !!}>SMHR Dashboard</a>
                                <a href="{{ route('smhr.staff-directory') }}" {!! $current(request()->routeIs('smhr.staff-directory')) !!}>Staff Directory &amp; Profiles</a>
                                <a href="{{ route('smhr.onboarding') }}" {!! $current(request()->routeIs('smhr.onboarding')) !!}>Staff Onboarding &amp; Induction</a>
                                <a href="{{ route('smhr.leave-management') }}" {!! $current(request()->routeIs('smhr.leave-management')) !!}>Leave &amp; Approvals</a>
                                <a href="{{ route('smhr.workload-allocation') }}" {!! $current(request()->routeIs('smhr.workload-allocation')) !!}>Teaching Workload Allocation</a>
                                <a href="{{ route('smhr.performance-appraisals') }}" {!! $current(request()->routeIs('smhr.performance-appraisals')) !!}>Performance Appraisals</a>
                                <a href="{{ route('smhr.payroll-register') }}" {!! $current(request()->routeIs('smhr.payroll-register')) !!}>Payroll &amp; Compensation</a>
                                <a href="{{ route('smhr.payslip') }}" {!! $current(request()->routeIs('smhr.payslip')) !!}>Staff Payslips</a>
                                <a href="{{ route('smhr.p9-form') }}" {!! $current(request()->routeIs('smhr.p9-form')) !!}>KRA Form P9A (Tax Cards)</a>
                                <a href="{{ route('smhr.reports') }}" {!! $current(request()->routeIs('smhr.reports')) !!}>SMHR Reports &amp; Returns</a>
                                <a href="{{ route('smhr.disciplinary-records') }}" {!! $current(request()->routeIs('smhr.disciplinary-records')) !!}>Disciplinary &amp; Governance</a>
                            </div>
                        </details>
                    </li>

                    {{-- 8. OPERATIONS, INTELLIGENCE & PLATFORM GOVERNANCE --}}
                    <li>
                        <details class="nav-group" data-nav-group="task-management" {{ request()->routeIs('task-management.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="clipboard-check"></i>
                                <span>Task Management</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('task-management.roles') }}" {!! $current(request()->routeIs('task-management.roles') || request()->routeIs('task-management.index')) !!}>Role</a>
                                <a href="{{ route('task-management.task-roles') }}" {!! $current(request()->routeIs('task-management.task-roles')) !!}>Task in Roles</a>
                                <a href="{{ route('task-management.task-manager') }}" {!! $current(request()->routeIs('task-management.task-manager')) !!}>Task Manager</a>
                            </div>
                        </details>
                    </li>
                    <li>
                        <details class="nav-group" data-nav-group="reports" {{ request()->routeIs('reports.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="bar-chart-3"></i>
                                <span>Reports</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('reports.advanced-analytics') }}" {!! $current(request()->routeIs('reports.advanced-analytics') || request()->routeIs('reports.index')) !!} style="border-left: 2px solid #E67E22; font-weight: bold; color: #E67E22 !important;">Advanced Analytics & Insights</a>
                                
                                <span class="px-3 py-1 text-[9px] uppercase font-bold text-slate-400 block tracking-wider mt-2 border-t border-slate-700/20 pt-2">Admission & Applicants</span>
                                <a href="{{ route('reports.application-status') }}" {!! $current(request()->routeIs('reports.application-status')) !!}>Application Status Report</a>
                                <a href="{{ route('reports.programme-applicants') }}" {!! $current(request()->routeIs('reports.programme-applicants')) !!}>Programme Wise Applicants</a>
                                <a href="{{ route('reports.gender-wise-list') }}" {!! $current(request()->routeIs('reports.gender-wise-list')) !!}>Gender Wise List</a>
                                <a href="{{ route('reports.kuccps-students') }}" {!! $current(request()->routeIs('reports.kuccps-students')) !!}>KUCCPS Students</a>
                                
                                <span class="px-3 py-1 text-[9px] uppercase font-bold text-slate-400 block tracking-wider mt-2 border-t border-slate-700/20 pt-2">Registration & Nominal Roll</span>
                                <a href="{{ route('reports.registration-report') }}" {!! $current(request()->routeIs('reports.registration-report')) !!}>Registration Report</a>
                                <a href="{{ route('reports.nominal-roll') }}" {!! $current(request()->routeIs('reports.nominal-roll')) !!}>Nominal Roll</a>
                                <a href="{{ route('reports.student-registration-details') }}" {!! $current(request()->routeIs('reports.student-registration-details')) !!}>Student Registration Details</a>
                                <a href="{{ route('reports.course-registration') }}" {!! $current(request()->routeIs('reports.course-registration')) !!}>Course Registration Report</a>
                                
                                <span class="px-3 py-1 text-[9px] uppercase font-bold text-slate-400 block tracking-wider mt-2 border-t border-slate-700/20 pt-2">Academic & Progression</span>
                                <a href="{{ route('reports.student-progression') }}" {!! $current(request()->routeIs('reports.student-progression')) !!}>Student Progression Report</a>
                                <a href="{{ route('reports.exemption-report') }}" {!! $current(request()->routeIs('reports.exemption-report')) !!}>Exemption Report</a>
                                <a href="{{ route('reports.reattempt-report') }}" {!! $current(request()->routeIs('reports.reattempt-report')) !!}>Re-Attempt(s) Report</a>
                                <a href="{{ route('reports.cohort-curriculum-mapping') }}" {!! $current(request()->routeIs('reports.cohort-curriculum-mapping')) !!}>Cohort Curriculum Mapping Report</a>
                                <a href="{{ route('reports.student-provisional-transcripts') }}" {!! $current(request()->routeIs('reports.student-provisional-transcripts')) !!}>Student Provisional Transcripts</a>
                                
                                <span class="px-3 py-1 text-[9px] uppercase font-bold text-slate-400 block tracking-wider mt-2 border-t border-slate-700/20 pt-2">Fees & Financials</span>
                                <a href="{{ route('reports.student-fee-statement') }}" {!! $current(request()->routeIs('reports.student-fee-statement')) !!}>Student Fee Statement</a>
                                <a href="{{ route('reports.dynamic-payment') }}" {!! $current(request()->routeIs('reports.dynamic-payment')) !!}>Dynamic Payment Report</a>
                                <a href="{{ route('reports.report-by-source') }}" {!! $current(request()->routeIs('reports.report-by-source')) !!}>Report By Source</a>
                                <a href="{{ route('reports.fee-movement') }}" {!! $current(request()->routeIs('reports.fee-movement')) !!}>Fee Movement</a>
                                <a href="{{ route('reports.debtors-report') }}" {!! $current(request()->routeIs('reports.debtors-report')) !!}>Debtors Report</a>
                                <a href="{{ route('reports.fee-overpayment') }}" {!! $current(request()->routeIs('reports.fee-overpayment')) !!}>Student Fee Overpayment Report</a>
                                <a href="{{ route('reports.fees-collection') }}" {!! $current(request()->routeIs('reports.fees-collection')) !!}>Fees Collection Report</a>
                                <a href="{{ route('reports.fee-summary') }}" {!! $current(request()->routeIs('reports.fee-summary')) !!}>Fee Summary Report</a>
                                <a href="{{ route('reports.student-invoices') }}" {!! $current(request()->routeIs('reports.student-invoices')) !!}>Student Invoices</a>
                                <a href="{{ route('reports.debtors-ageing-analysis') }}" {!! $current(request()->routeIs('reports.debtors-ageing-analysis')) !!}>Debtors Ageing Analysis Report</a>
                                
                                <span class="px-3 py-1 text-[9px] uppercase font-bold text-slate-400 block tracking-wider mt-2 border-t border-slate-700/20 pt-2">Registry & Activity Audit</span>
                                <a href="{{ route('reports.search-student-short-courses') }}" {!! $current(request()->routeIs('reports.search-student-short-courses')) !!}>Search By Student for Short Courses</a>
                                <a href="{{ route('reports.search-payment-source') }}" {!! $current(request()->routeIs('reports.search-payment-source')) !!}>Search By Payment Source</a>
                                <a href="{{ route('reports.search-transaction-id') }}" {!! $current(request()->routeIs('reports.search-transaction-id')) !!}>Search By Transaction ID</a>
                                <a href="{{ route('reports.user-details') }}" {!! $current(request()->routeIs('reports.user-details')) !!}>User Details</a>
                                <a href="{{ route('reports.audit-trail-user') }}" {!! $current(request()->routeIs('reports.audit-trail-user')) !!}>Audit Trail by User</a>
                                <a href="{{ route('reports.dynamic-report') }}" {!! $current(request()->routeIs('reports.dynamic-report')) !!}>Dynamic Report</a>
                            </div>
                        </details>
                    </li>
                    <li>
                        <details class="nav-group" data-nav-group="admin-setups" {{ request()->routeIs('admin.setups.*') && !request()->routeIs('admin.setups.recycle-bin.*') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="settings"></i>
                                <span>Admin Setups</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('admin.setups.index') }}" {!! $current(request()->routeIs('admin.setups.index') && !request()->routeIs('admin.setups.accounting') && !request()->routeIs('admin.setups.bank') && !request()->routeIs('admin.setups.invoicing') && !request()->routeIs('admin.setups.payment') && !request()->routeIs('admin.setups.module-manager')) !!}>Platform Setup Hub</a>
                                <a href="{{ route('admin.setups.accounting') }}" {!! $current(request()->routeIs('admin.setups.accounting')) !!}>Accounting</a>
                                <a href="{{ route('admin.setups.bank') }}" {!! $current(request()->routeIs('admin.setups.bank')) !!}>Bank</a>
                                <a href="{{ route('admin.setups.invoicing') }}" {!! $current(request()->routeIs('admin.setups.invoicing')) !!}>Invoicing</a>
                                <a href="{{ route('admin.setups.payment') }}" {!! $current(request()->routeIs('admin.setups.payment')) !!}>Payment</a>
                                <a href="{{ route('admin.setups.module-manager') }}" {!! $current(request()->routeIs('admin.setups.module-manager')) !!} style="border-left: 2px solid #E67E22; font-weight: bold; color: #E67E22 !important;">Module Manager (Active/Deactivate)</a>
                                @can('platform.role.manage')
                                    <a href="{{ route('admin.setups.access.index') }}" {!! $current(request()->routeIs('admin.setups.access.*')) !!}>Access Control</a>
                                @endcan
                                @can('platform.audit.view')
                                    <a href="{{ route('admin.setups.governance.index') }}" {!! $current(request()->routeIs('admin.setups.governance.*')) !!}>Data Governance</a>
                                @endcan
                                @if(auth()->user()?->isAdmin())
                                    <a href="{{ route('admin.setups.load-balancer') }}" {!! $current(request()->routeIs('admin.setups.load-balancer*')) !!} style="border-left: 2px solid #1E8449; font-weight: bold; color: #1E8449 !important;">Load Balancer &amp; Queuing Strategy</a>
                                    <a href="{{ route('admin.setups.system-maintenance.index') }}" {!! $current(request()->routeIs('admin.setups.system-maintenance.*')) !!} style="border-left: 2px solid #E67E22; font-weight: bold; color: #E67E22 !important;">System Maintenance &amp; Upgrades</a>
                                @endif
                            </div>
                        </details>
                    </li>
                    @can('platform.audit.view')
                    <li>
                        <details class="nav-group" data-nav-group="recycle-bin" {{ request()->routeIs('admin.setups.recycle-bin.*') || request()->routeIs('recycle-bin') ? 'open' : '' }}>
                            <summary>
                                <i data-lucide="trash-2"></i>
                                <span>Recycle Bin</span>
                                <i data-lucide="chevron-right" class="chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="nav-children">
                                <a href="{{ route('admin.setups.recycle-bin.index') }}" {!! $current(request()->routeIs('admin.setups.recycle-bin.*') && empty(request()->query('type'))) !!}>All Trashed Records</a>
                                <a href="{{ route('admin.setups.recycle-bin.index', ['type' => 'school']) }}" {!! $current(request()->query('type') === 'school') !!}>Academic Schools</a>
                                <a href="{{ route('admin.setups.recycle-bin.index', ['type' => 'department']) }}" {!! $current(request()->query('type') === 'department') !!}>Departments</a>
                                <a href="{{ route('admin.setups.recycle-bin.index', ['type' => 'programme']) }}" {!! $current(request()->query('type') === 'programme') !!}>Degree Programmes</a>
                                <a href="{{ route('admin.setups.recycle-bin.index', ['type' => 'course_unit']) }}" {!! $current(request()->query('type') === 'course_unit') !!}>Course Units</a>
                                <a href="{{ route('admin.setups.recycle-bin.index', ['type' => 'cohort_year']) }}" {!! $current(request()->query('type') === 'cohort_year') !!}>Academic Years</a>
                            </div>
                        </details>
                    </li>
                    @endcan
                </ul>
            </nav>

            <div class="sidebar-footer">
                <button class="sidebar-profile" type="button" data-sidebar-label="Account menu"
                    aria-label="Open account menu">
                    <span class="avatar" style="background:#E67E22;color:#0A3E50;">{{ strtoupper(substr(auth()->user()->first_name ?: auth()->user()->name, 0, 1)) }}</span>
                    <div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->roleLabel() }}</small></div>
                </button>
            </div>
        </aside>

        <main class="main" id="main-content" tabindex="-1">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="mobile-sidebar-toggle" type="button" aria-controls="app-sidebar" aria-expanded="false"
                        aria-label="Open navigation"><i data-lucide="menu" aria-hidden="true"></i></button>
                    <button class="topbar-collapse-arrow" type="button" title="Toggle navigation" aria-label="Toggle menu">
                        <i data-lucide="chevron-left" class="w-5 h-5 text-orange-500"></i>
                    </button>
                </div>

                <div class="topbar-institution-title">
                    MEMA ERP
                </div>

                <div class="top-actions">
                    <div class="font-resizers" aria-label="Adjust font size">
                        <button type="button" class="btn-text-size" data-size="sm" title="Decrease font size">A-</button>
                        <button type="button" class="btn-text-size" data-size="base" title="Standard font size">A</button>
                        <button type="button" class="btn-text-size" data-size="lg" title="Increase font size">A+</button>
                    </div>

                    <div class="role-selector">
                        <button type="button" class="role-dropdown-btn">
                            <span>Admin</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <details class="user-menu">
                        <summary aria-label="Open account menu for {{ auth()->user()->name }}">
                            <div class="user-profile-badge">
                                <div class="avatar-fallback">{{ strtoupper(substr(auth()->user()->first_name ?: auth()->user()->name, 0, 1) . substr(auth()->user()->last_name, 0, 1)) }}</div>
                                <div class="user-details">
                                    <span class="user-name">{{ strtoupper(auth()->user()->name) }}</span>
                                    <span class="user-role">{{ auth()->user()->roleLabel() }}</span>
                                </div>
                            </div>
                        </summary>
                        <div class="menu-popover">
                            <div class="menu-identity">
                                <strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small>
                            </div>
                            <a class="menu-item" href="{{ route('account.show', 'overview') }}"><i data-lucide="user-round"></i>Profile Overview</a>
                            <a class="menu-item" href="{{ route('account.show', 'edit') }}"><i data-lucide="user-cog"></i>Edit Profile</a>
                            <a class="menu-item" href="{{ route('account.show', 'activity') }}"><i data-lucide="activity"></i>Activity</a>
                            <a class="menu-item" href="{{ route('account.show', 'calendar') }}"><i data-lucide="calendar-days"></i>Calendar</a>
                            <a class="menu-item" href="{{ route('account.show', 'files') }}"><i data-lucide="folder-lock"></i>My Files</a>
                            <a class="menu-item" href="{{ route('account.show', 'reports') }}"><i data-lucide="file-text"></i>My Reports</a>
                            <a class="menu-item" href="{{ route('account.show', 'messages') }}"><i data-lucide="message-square"></i>Messages</a>
                            <a class="menu-item" href="{{ route('account.show', 'notifications') }}"><i data-lucide="bell"></i>Notifications</a>
                            <a class="menu-item" href="{{ route('account.show', 'preferences') }}"><i data-lucide="sliders"></i>Preferences</a>
                            <a class="menu-item" href="{{ route('account.show', 'security') }}"><i data-lucide="shield-check"></i>Security</a>
                            <a class="menu-item" href="{{ route('account.show', 'support') }}"><i data-lucide="help-circle"></i>Help and Support</a>
                            @if (auth()->user()->isAdmin())
                                <button class="menu-item" type="button" data-modal-open="stakeholder-login-modal"><i
                                        data-lucide="scan-face"></i>Log in as a stakeholder...</button>
                            @endif
                            @if (auth()->user()->stakeholderTypes()->where('is_active', true)->count() > 1)
                                <button class="menu-item" type="button" data-modal-open="role-modal"><i
                                        data-lucide="users-round"></i>Switch role to...</button>
                            @endif
                            <div class="menu-divider"></div>
                            <form method="post" action="{{ route('logout') }}">@csrf<button
                                    class="menu-item danger"><i data-lucide="log-out"></i>Log out</button></form>
                        </div>
                    </details>
                </div>
            </header>
            <div class="content">
                @if (session()->has('impersonator_id'))
                    <div class="alert alert-impersonating"><span><strong>Viewing as
                                {{ auth()->user()->name }}</strong><br>You are impersonating this account. Actions use
                            their permissions.</span>
                        <form method="post" action="{{ route('impersonate.stop') }}">@csrf<button
                                class="btn btn-secondary"><i data-lucide="undo-2"></i>Return to admin</button></form>
                    </div>
                @endif 
                @if (session('success'))
                    <div class="alert">{{ session('success') }}</div>
                @endif 
                @if (session('error'))
                    <div class="alert error">{{ session('error') }}</div>
                @endif 
                @if (session('info'))
                    <div class="alert">{{ session('info') }}</div>
                @endif 
                @if ($errors->any())
                    <div class="alert error">{{ $errors->first() }}</div>
                @endif 
                @yield('content')
            </div>
        </main>
        <div class="sidebar-backdrop" aria-hidden="true"></div>
    </div>
    @php($preferences = auth()->user()->preference ?? new \App\Models\UserPreference(['language' => 'en', 'timezone' => 'Africa/Nairobi', 'email_notifications' => true, 'browser_notifications' => true, 'profile_discoverable' => false, 'theme' => 'system']))
    <div class="modal" id="preferences-modal" role="dialog" aria-modal="true" aria-labelledby="preferences-title">
        <div class="modal-card">
            <div class="panel-head">
                <div>
                    <h2 id="preferences-title">Preferences</h2><small style="color:var(--muted)">Account,
                        notifications, privacy and appearance.</small>
                </div><button class="btn btn-secondary" type="button" data-modal-close aria-label="Close"><i
                        data-lucide="x"></i></button>
            </div>
            <form class="panel-body" method="post" action="{{ route('account.preferences') }}">@csrf
                @method('PUT')<div class="preference-tabs">
                    @foreach (['account' => 'Account', 'notifications' => 'Notifications', 'privacy' => 'Privacy', 'theme' => 'Theme'] as $tab => $label)
                        <button type="button" class="preference-tab {{ $loop->first ? 'active' : '' }}"
                            data-preference-tab="{{ $tab }}">{{ $label }}</button>
                    @endforeach
                </div>
                <div class="preference-panel active" data-preference-panel="account">
                    <div class="form-grid">
                        <div class="field"><label>Language</label><select name="language">
                                <option value="en" @selected($preferences->language === 'en')>English</option>
                                <option value="sw" @selected($preferences->language === 'sw')>Kiswahili</option>
                            </select></div>
                        <div class="field"><label>Timezone</label><select name="timezone">
                                <option value="Africa/Nairobi">Africa/Nairobi</option>
                                <option value="UTC" @selected($preferences->timezone === 'UTC')>UTC</option>
                            </select></div>
                    </div>
                </div>
                <div class="preference-panel" data-preference-panel="notifications"><label class="check-row">Email
                        notifications<input type="checkbox" name="email_notifications" value="1"
                            @checked($preferences->email_notifications)></label><label class="check-row">Browser notifications<input
                            type="checkbox" name="browser_notifications" value="1"
                            @checked($preferences->browser_notifications)></label></div>
                <div class="preference-panel" data-preference-panel="privacy"><label class="check-row">Allow
                        colleagues to discover my profile<input type="checkbox" name="profile_discoverable"
                            value="1" @checked($preferences->profile_discoverable)></label></div>
                <div class="preference-panel" data-preference-panel="theme">
                    <div class="field"><label>Theme</label><select name="theme">
                            <option value="system" @selected($preferences->theme === 'system')>Use device setting</option>
                            <option value="light" @selected($preferences->theme === 'light')>Light</option>
                            <option value="dark" @selected($preferences->theme === 'dark')>Dark</option>
                        </select></div>
                </div><button class="btn" style="margin-top:18px"><i data-lucide="save"></i>Save
                    preferences</button>
            </form>
        </div>
    </div>
    @if (auth()->user()->stakeholderTypes()->where('is_active', true)->count() > 1)
        <div class="modal" id="role-modal" role="dialog" aria-modal="true" aria-labelledby="role-title">
            <div class="modal-card">
                <div class="panel-head">
                    <h2 id="role-title">Switch role</h2><button class="btn btn-secondary" type="button"
                        data-modal-close aria-label="Close"><i data-lucide="x"></i></button>
                </div>
                <div class="panel-body">
                    @foreach (auth()->user()->stakeholderTypes()->where('is_active', true)->get() as $stakeholder)
                        <form method="post" action="{{ route('account.switch-role') }}">@csrf<input type="hidden"
                                name="stakeholder_type" value="{{ $stakeholder->stakeholder_type }}"><button
                                class="menu-item" style="border:1px solid var(--line);margin-bottom:8px"><i
                                    data-lucide="user-round"></i>{{ ucfirst(str_replace('_', ' ', $stakeholder->stakeholder_type)) }}
                                @if (auth()->user()->activeRole() === $stakeholder->stakeholder_type)
                                    <i data-lucide="check" style="margin-left:auto;color:var(--green)"></i>
                                @endif
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    @if(auth()->user()->isAdmin())
        @php($allStakeholders = \App\Models\User::where('role', '!=', 'admin')->where('is_active', true)->orderBy('role')->orderBy('name')->get())
        <div class="modal" id="stakeholder-login-modal" role="dialog" aria-modal="true" aria-labelledby="stakeholder-modal-title">
            <div class="modal-card" style="width:min(680px, 94vw);">
                <div class="panel-head">
                    <div>
                        <h2 id="stakeholder-modal-title" class="text-sm font-bold text-gray-900">Log in as a stakeholder</h2>
                        <small style="color:var(--muted)">Preview the exact dashboard and permissions for another account.</small>
                    </div>
                    <button class="btn btn-secondary" type="button" data-modal-close aria-label="Close"><i data-lucide="x"></i></button>
                </div>
                <div class="panel-body" style="max-height:70vh;overflow-y:auto;padding:0;">
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr style="background:#f8fafc;">
                                    <th style="font-size:11px;padding:10px 16px;">ACCOUNT</th>
                                    <th style="font-size:11px;padding:10px 16px;">ROLE</th>
                                    <th style="font-size:11px;padding:10px 16px;text-align:right;">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allStakeholders as $person)
                                    <tr>
                                        <td style="padding:12px 16px;">
                                            <div class="person">
                                                <span class="avatar" style="background:#e6f1eb;color:#0A3E50;border-radius:6px;width:34px;height:34px;font-weight:700;">{{ strtoupper(substr($person->first_name ?: $person->name, 0, 1)) }}</span>
                                                <div>
                                                    <strong style="font-size:13px;color:#1e293b;">{{ $person->name }}</strong><br>
                                                    <small style="color:#64748b;font-size:11.5px;">{{ $person->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding:12px 16px;">
                                            <span class="badge" style="background:#f1f5f9;color:#334155;font-weight:600;font-size:11px;padding:3px 8px;border-radius:4px;">{{ $person->roleLabel() }}</span>
                                        </td>
                                        <td style="padding:12px 16px;text-align:right;">
                                            <form method="post" action="{{ route('impersonate.start', $person) }}">
                                                @csrf
                                                <button class="btn btn-secondary" style="font-size:11.5px;padding:5px 10px;display:inline-flex;align-items:center;gap:5px;border-radius:5px;border:1px solid #cbd5e1;">
                                                    <i data-lucide="log-in" class="w-3.5 h-3.5"></i>Log in as
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="empty">No stakeholder accounts found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MENU SEARCH MODAL (Matching Screenshots) --}}
    <div class="modal" id="menu-search-modal" role="dialog" aria-modal="true" aria-labelledby="menu-search-title">
        <div class="menu-search-card">
            <div class="menu-search-head">
                <h2 id="menu-search-title">Menu Search</h2>
                <button type="button" class="close-btn" data-modal-close aria-label="Close menu search">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            
            <div class="menu-search-body">
                <div class="menu-search-inset-box">
                    <label class="menu-search-field-label">Menu Search</label>
                    
                    {{-- Dropdown Trigger --}}
                    <div class="menu-search-trigger active" id="menu-search-trigger-btn" tabindex="0" role="button" aria-expanded="true">
                        <span id="menu-search-selected-label">Select Menu</span>
                        <i data-lucide="chevron-up" id="menu-search-arrow-icon" class="w-4 h-4 text-slate-500"></i>
                    </div>

                    {{-- Searchable Dropdown Popup --}}
                    <div class="menu-search-dropdown" id="menu-search-dropdown-box">
                        <div class="menu-search-input-wrap">
                            <input type="text" id="menu-search-input" placeholder="Search" autocomplete="off" spellcheck="false">
                        </div>

                        <ul class="menu-search-list" id="menu-search-list-items">
                            <?php
                                $menuSearchModules = [
                                    ['name' => 'Transfer Dates Setup', 'url' => route('transfers.dates-setup')],
                                    ['name' => 'Inter/Intra Faculty Transfers', 'url' => route('transfers.inter-intra')],
                                    ['name' => 'Credit Transfers', 'url' => route('transfers.credit-transfers')],
                                    ['name' => 'Exemptions', 'url' => route('transfers.exemptions')],
                                    ['name' => 'Supervisor Role Configuration', 'url' => route('pg-research.supervisor-roles')],
                                    ['name' => 'Research Eligibility & Gating', 'url' => route('pg-research.eligibility-gating')],
                                    ['name' => 'Supervisor Workload Allocation', 'url' => route('pg-research.supervisor-allocation')],
                                    ['name' => 'Proposal Reader Review', 'url' => route('pg-research.proposal-reader-review')],
                                    ['name' => 'Seminar Presentations Tracking', 'url' => route('pg-research.seminar-presentations')],
                                    ['name' => 'Research Progress Reports', 'url' => route('pg-research.progress-reports')],
                                    ['name' => 'Plagiarism & AI Similarity Index', 'url' => route('pg-research.plagiarism-checker')],
                                    ['name' => 'Defence Request Approval', 'url' => route('pg-research.defence-request-approval')],
                                    ['name' => 'Examiner Dashboard', 'url' => route('pg-research.examiner-dashboard')],
                                    ['name' => 'Graduate Level Viva Examination', 'url' => route('pg-research.viva-examination')],
                                    ['name' => 'Thesis Marks Approval', 'url' => route('pg-research.thesis-marks-approval')],
                                    ['name' => 'Final Thesis Resubmission Review', 'url' => route('pg-research.thesis-resubmission')],
                                    ['name' => 'Research Publications Review', 'url' => route('pg-research.publications-review')],
                                    ['name' => 'Legacy Projects & Migration', 'url' => route('pg-research.legacy-migration')],
                                    ['name' => 'PG Appeal Period Setup', 'url' => route('pg-research.appeal-period-setup')],
                                    ['name' => 'PG Appeal Category', 'url' => route('pg-research.appeal-category')],
                                    ['name' => 'School', 'url' => route('curriculum.school')],
                                    ['name' => 'Department', 'url' => route('curriculum.department')],
                                    ['name' => 'Program Type', 'url' => route('curriculum.program-type')],
                                    ['name' => 'Programme', 'url' => route('curriculum.programme')],
                                    ['name' => 'Programme Curriculum', 'url' => route('curriculum.programme-curriculum')],
                                    ['name' => 'Course Unit', 'url' => route('curriculum.course-unit')],
                                    ['name' => 'Specialisation', 'url' => route('curriculum.specialisation')],
                                    ['name' => 'Student Specialization Mapping', 'url' => route('curriculum.student-specialization-mapping')],
                                    ['name' => 'Instructor Mapping', 'url' => route('curriculum.instructor-mapping')],
                                    ['name' => 'Cluster Subjects', 'url' => route('curriculum.cluster-subjects')],
                                    ['name' => 'Program Cluster Mapping', 'url' => route('curriculum.program-cluster-mapping')],
                                    ['name' => 'Progression Criteria', 'url' => route('curriculum.progression-criteria')],
                                    ['name' => 'Short Course Creation', 'url' => route('curriculum.short-course-creation')],
                                    ['name' => 'Work Study: Period Setup', 'url' => route('work-study.period-setup')],
                                    ['name' => 'Work Study: Positions', 'url' => route('work-study.positions')],
                                    ['name' => 'Work Study: Applications', 'url' => route('work-study.applications')],
                                    ['name' => 'Work Study: Allocations', 'url' => route('work-study.allocations')],
                                    ['name' => 'Work Study: Timesheets', 'url' => route('work-study.timesheets')],
                                    ['name' => 'Work Study: Claims & Payroll', 'url' => route('work-study.claims')],
                                    ['name' => 'Imprest Permissions', 'url' => route('imprest.permissions')],
                                    ['name' => 'Claim Approval Permission', 'url' => route('imprest.claim-approvals')],
                                    ['name' => 'Imprest Surrender Permission', 'url' => route('imprest.surrender-permissions')],
                                    ['name' => 'Imprest Requisitions', 'url' => route('imprest.requisitions')],
                                    ['name' => 'Imprest Surrenders', 'url' => route('imprest.surrenders')],
                                    ['name' => 'Imprest Audit Ledger', 'url' => route('imprest.audit-ledger')],
                                    ['name' => 'Admissions Applications & Shortlists', 'url' => route('admissions.index')],
                                    ['name' => 'Document Verification Portal', 'url' => route('admissions.index')],
                                    ['name' => 'Student Conversion Ledger', 'url' => route('admissions.conversions')],
                                    ['name' => 'Student Directory & Profiles', 'url' => route('students.index')],
                                    ['name' => 'Courses & Programme Catalogue', 'url' => route('courses.index')],
                                    ['name' => 'Exam Center Configuration', 'url' => route('examination.exam-center')],
                                    ['name' => 'Exam Session Setup', 'url' => route('examination.exam-session')],
                                    ['name' => 'Exam Schedule & Timetable', 'url' => route('examination.exam-schedule')],
                                    ['name' => 'Marks Capture Sheets', 'url' => route('examination.marks-capture')],
                                    ['name' => 'Marks Submission Portal', 'url' => route('examination.marks-submission')],
                                    ['name' => 'Exam Marks Approval Desk', 'url' => route('examination.marks-approval')],
                                    ['name' => 'Exam Marks Publish Gate', 'url' => route('examination.marks-publish')],
                                    ['name' => 'Class Scores Analysis Dashboard', 'url' => route('examination.scores-analysis')],
                                    ['name' => 'Summary of Examination Results', 'url' => route('examination.summary-results')],
                                    ['name' => 'Grades & Scale Policy Config', 'url' => route('examination.grades-config')],
                                    ['name' => 'Graduation Pass List Vetting', 'url' => route('examination.pass-list')],
                                    ['name' => 'Academic Progression Promotion Register', 'url' => route('examination.progression-list')],
                                    ['name' => 'Academic Fail & Supplementary Register', 'url' => route('examination.fail-list')],
                                    ['name' => 'Provisional Transcript Generation', 'url' => route('examination.provisional-transcript')],
                                    ['name' => 'Official Academic Transcript Registry', 'url' => route('examination.academic-transcript')],
                                    ['name' => 'Transcript Requests & Dispatch Logs', 'url' => route('examination.transcript-requests')],
                                    ['name' => 'Exam Senate Reports Portfolio', 'url' => route('examination.senate-reports')],
                                    ['name' => 'Consolidated Class Marksheets', 'url' => route('examination.consolidated-marksheets')],
                                    ['name' => 'Payment Accounts Configuration', 'url' => route('fees.payment-accounts')],
                                    ['name' => 'Payment Types & Chart of Accounts', 'url' => route('fees.payment-types')],
                                    ['name' => 'Payment Source & Sponsorship', 'url' => route('fees.payment-source')],
                                    ['name' => 'Trimester Fee Setup Structure', 'url' => route('fees.fee-setup')],
                                    ['name' => 'Student Fee Payables Ledger', 'url' => route('fees.fee-payables')],
                                    ['name' => 'Pending Payment Confirmation Queue', 'url' => route('fees.pending-payments')],
                                    ['name' => 'Student Tuition Statement Receipt', 'url' => route('fees.payment-receipt')],
                                    ['name' => 'Graduation Criteria Configuration', 'url' => route('graduation.criteria')],
                                    ['name' => 'Clearance Checklist Setup', 'url' => route('graduation.clearance-checklist')],
                                    ['name' => 'Finance Clearance Ledger', 'url' => route('graduation.finance-clearance')],
                                    ['name' => 'Graduation Honors Grade List', 'url' => route('graduation.grade-list')],
                                    ['name' => 'Graduation List Compiler', 'url' => route('graduation.generate-list')],
                                    ['name' => 'Dean Graduation List Validation', 'url' => route('graduation.validate-list')],
                                    ['name' => 'Publish Graduation Pass List', 'url' => route('graduation.publish-list')],
                                    ['name' => 'Graduation List Report Sheet', 'url' => route('graduation.list-report')],
                                    ['name' => 'Graduation Summary List Statistics', 'url' => route('graduation.summary-list')],
                                    ['name' => 'Progressive Certification & Templates', 'url' => route('graduation.certification-setup')],
                                    ['name' => 'Alumni Student Directory', 'url' => route('graduation.alumni-list')],
                                    ['name' => 'Graduation Ceremony Logistics & Gowns', 'url' => route('graduation.ceremony')],
                                    ['name' => 'Graduation Ceremony Report Summary', 'url' => route('graduation.ceremony-report')],
                                    ['name' => 'Task Role Administrative Mapping', 'url' => route('task-management.roles')],
                                    ['name' => 'Task Mapped in Roles Bindings', 'url' => route('task-management.task-roles')],
                                    ['name' => 'Task Manager Action Tickets', 'url' => route('task-management.task-manager')],
                                    ['name' => 'Admissions & Registry Intelligence Reports', 'url' => route('reports.advanced-analytics')],
                                    ['name' => 'Academic & Progression Intelligence Reports', 'url' => route('reports.advanced-analytics')],
                                    ['name' => 'Fees & Financial Intelligence Reports', 'url' => route('reports.advanced-analytics')],
                                    ['name' => 'Registry & Short Course Query Search', 'url' => route('reports.advanced-analytics')],
                                    ['name' => 'Security Audits & Activity Trails', 'url' => route('reports.advanced-analytics')],
                                    ['name' => 'LMS Virtual Classrooms', 'url' => route('lms.course-shells')],
                                    ['name' => 'LMS Faculty Assignments', 'url' => route('lms.lecturer-assignments')],
                                    ['name' => 'LMS Live Virtual Lectures', 'url' => route('lms.live-lectures')],
                                    ['name' => 'LMS Learning Materials', 'url' => route('lms.e-resources')],
                                    ['name' => 'LMS Continuous Assessment', 'url' => route('lms.assignments')],
                                    ['name' => 'LMS Engagement Analytics', 'url' => route('lms.student-analytics')],
                                    ['name' => 'LMS Discussion Forums', 'url' => route('lms.discussion-forums')],
                                    ['name' => 'LMS Online Quizzes', 'url' => route('lms.online-quizzes')],
                                    ['name' => 'LMS Gradebook & Marks Sync', 'url' => route('lms.gradebook-sync')],
                                    ['name' => 'SMHR Dashboard', 'url' => route('smhr.dashboard')],
                                    ['name' => 'SMHR Staff Directory & Profiles', 'url' => route('smhr.staff-directory')],
                                    ['name' => 'SMHR Leave Management & Approvals', 'url' => route('smhr.leave-management')],
                                    ['name' => 'SMHR Teaching Workload Allocation', 'url' => route('smhr.workload-allocation')],
                                    ['name' => 'SMHR Performance Appraisals & KPIs', 'url' => route('smhr.performance-appraisals')],
                                    ['name' => 'SMHR Payroll Register & Payslips', 'url' => route('smhr.payroll-register')],
                                    ['name' => 'SMHR Disciplinary & Governance Ledger', 'url' => route('smhr.disciplinary-records')],
                                    ['name' => 'Academic Year', 'url' => route('cohort.academic-year')],
                                    ['name' => 'Cohort Creation', 'url' => route('cohort.cohort-creation')],
                                    ['name' => 'Programme Cohort Mapping List', 'url' => route('cohort.programme-cohort-mapping')],
                                    ['name' => 'Programme Cohort Publish - Finance', 'url' => route('cohort.publish-finance')],
                                    ['name' => 'Cohort Transfer', 'url' => route('cohort.cohort-transfer')],
                                    ['name' => 'Application Verification', 'url' => route('registration.application-verification')],
                                    ['name' => 'Application Approval', 'url' => route('registration.application-approval')],
                                    ['name' => 'Rejected List', 'url' => route('registration.rejected-list')],
                                    ['name' => 'KUCCPS Student Registration', 'url' => route('registration.kuccps-registration')],
                                    ['name' => 'Student Registrations', 'url' => route('registration.student-registrations')],
                                    ['name' => 'Course Registration and Confirmation Periods', 'url' => route('registration.course-registration-periods')],
                                    ['name' => 'Promotions', 'url' => route('registration.promotions')],
                                    ['name' => 'Professional Development Courses User List', 'url' => route('registration.professional-development-users')],
                                    ['name' => 'ERP-Moodle Course Unit Sync', 'url' => route('registration.moodle-sync')],
                                    ['name' => 'Student Information Update', 'url' => route('registration.student-info-update')],
                                    ['name' => 'Reminder', 'url' => route('registration.reminders')],
                                    ['name' => 'User Registration', 'url' => route('registration.user-registration')],
                                    ['name' => 'Student Password', 'url' => route('registration.student-password')],
                                    ['name' => 'Staff Password', 'url' => route('registration.staff-password')],
                                    ['name' => 'Password reset', 'url' => route('registration.password-reset')],
                                    ['name' => 'Student Affairs & Housing', 'url' => '#student-affairs'],
                                    ['name' => 'Service Providers & Contracts', 'url' => route('service-providers.index')],
                                    ['name' => 'Budgeting and Planning', 'url' => route('budgeting.index')],
                                    ['name' => 'Admin Platform Setup Hub', 'url' => route('admin.setups.index')],
                                    ['name' => 'Load Balancer & Queuing Strategy', 'url' => route('admin.setups.load-balancer')],
                                    ['name' => 'Admissions Reports & Exports', 'url' => route('admissions.reports')],
                                ];
                            ?>

                            @foreach($menuSearchModules as $mod)
                                <li class="menu-search-item" data-name="{{ strtolower($mod['name']) }}" data-url="{{ $mod['url'] }}">
                                    <span class="checkbox-box" aria-hidden="true"></span>
                                    <span class="module-title">{{ $mod['name'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>

                <div class="menu-search-footer">
                    <button type="button" class="btn-close-red" data-modal-close>Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- DYNAMIC COMING SOON MODAL --}}
    <div class="modal" id="coming-soon-modal" role="dialog" aria-modal="true" aria-labelledby="cs-title">
        <div class="coming-soon-card">
            <div class="coming-soon-header-banner">
                <div class="coming-soon-badge-strip">
                    <span class="coming-soon-pill"><i data-lucide="sparkles" class="w-3.5 h-3.5"></i><span id="cs-pill-text">In Development</span></span>
                    <button type="button" class="close-btn" data-modal-close aria-label="Close modal" style="background:transparent;border:none;color:#fff;cursor:pointer;opacity:0.85;padding:4px;">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                
                <div class="coming-soon-hero">
                    <div class="coming-soon-icon-box" id="cs-icon-container">
                        <i data-lucide="users" class="w-7 h-7 text-white"></i>
                    </div>
                    <div>
                        <h2 class="coming-soon-title" id="cs-title">SMHR - Staff Management & HR</h2>
                        <p class="coming-soon-desc" id="cs-desc">Faculty dossiers, academic workload balancing, leave matrix & adjunct contract management.</p>
                    </div>
                </div>
            </div>

            <div class="coming-soon-body">
                {{-- Progress Meter --}}
                <div class="coming-soon-progress-box">
                    <div class="coming-soon-progress-head">
                        <span class="font-bold text-slate-700 flex items-center gap-1.5"><i data-lucide="cpu" class="w-3.5 h-3.5 text-teal-700"></i> Engineering Roadmap</span>
                        <span class="font-extrabold text-teal-800" id="cs-progress-text">84% Completed</span>
                    </div>
                    <div class="coming-soon-progress-bar">
                        <div class="coming-soon-progress-fill" id="cs-progress-bar" style="width: 84%;"></div>
                    </div>
                    <div class="flex justify-between items-center text-[10.5px] text-slate-400 mt-2">
                        <span>Sprint Phase: Production Integration</span>
                        <span class="font-semibold text-emerald-700" id="cs-release-text">Target: Q4 2026</span>
                    </div>
                </div>

                {{-- Key Capabilities Preview --}}
                <div class="mb-2">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wide block mb-2">Module Capabilities & Features</span>
                    <div class="coming-soon-feature-grid" id="cs-features-container">
                        <!-- Dynamically injected -->
                    </div>
                </div>

                {{-- Early Access Notification Form --}}
                <div class="mt-4">
                    <label class="text-xs font-semibold text-slate-600 block mb-1.5">Get early access & launch updates</label>
                    <form id="cs-notify-form" onsubmit="event.preventDefault(); document.getElementById('cs-notify-success').classList.remove('hidden'); this.classList.add('hidden');">
                        <div class="coming-soon-notify-box">
                            <input type="email" placeholder="Enter your staff email..." required value="{{ auth()->user()->email ?? '' }}">
                            <button type="submit" class="coming-soon-notify-btn">
                                <i data-lucide="bell-ring" class="w-3.5 h-3.5"></i>Notify Me
                            </button>
                        </div>
                    </form>
                    <div id="cs-notify-success" class="hidden p-2.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg text-xs font-semibold flex items-center gap-2 mt-1.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                        <span>You're on the priority list! We'll notify you as soon as this module deploys.</span>
                    </div>
                </div>

                <div class="coming-soon-footer">
                    <a href="{{ route('admin.setups.index') }}" class="text-xs font-bold text-teal-800 hover:underline flex items-center gap-1">
                        <i data-lucide="settings-2" class="w-3.5 h-3.5"></i>Open Platform Setups
                    </a>
                    <button type="button" class="btn btn-secondary text-xs py-1.5 px-3" data-modal-close>
                        Return to Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Module catalogue metadata for dynamic Coming Soon showcase
        const moduleCatalogue = {
            '#smhr': {
                title: 'SMHR - Staff & Human Resources',
                desc: 'Comprehensive faculty dossier lifecycle, workload balancing, payroll sync, promotion matrix & contracts.',
                icon: 'users',
                progress: 84,
                release: 'Q4 2026 Release',
                features: [
                    'Faculty Dossiers & Biometrics Archive',
                    'Academic Workload & Lecture Balancing',
                    'Sabbatical, Medical & Leave Approvals',
                    'Payroll & Direct Bank/M-Pesa Sync',
                    'Adjunct Contract & Rating Matrix',
                    'Senate Staff Committee Portals'
                ]
            },
            '#student-affairs': {
                title: 'Student Affairs & Welfare Management',
                desc: 'Student leadership council, hostel allocation, sports, bursaries and disciplinary registry.',
                icon: 'heart-handshake',
                progress: 72,
                release: 'Q4 2026 Release',
                features: [
                    'Hostel Room & Hall Reservation',
                    'Bursary & Emergency Aid Allocation',
                    'Clubs, Societies & Election Matrix',
                    'Disciplinary Hearing Records'
                ]
            },
            '#service-providers': {
                title: 'Service Providers & Procurement Registry',
                desc: 'Vendor SLAs, RFP evaluations, campus security contracts and facility maintenance.',
                icon: 'building-2',
                progress: 78,
                release: 'Q4 2026 Release',
                features: [
                    'Approved Vendor Dossiers',
                    'Contract & SLA Expiry Alarms',
                    'Procurement Purchase Orders',
                    'Service Rating & Performance Audits'
                ]
            },
            '#budgeting': {
                title: 'Budgeting & Capital Planning Hub',
                desc: 'Departmental fiscal forecasts, vote head allocations and VC approval workflow.',
                icon: 'pie-chart',
                progress: 82,
                release: 'Q4 2026 Release',
                features: [
                    'Departmental Vote Head Budgeting',
                    'Variance & Burn Rate Tracking',
                    'Procurement Requisition Verification',
                    'Senate Finance Committee Dashboards'
                ]
            }
        };

        function showComingSoon(hash) {
            const data = moduleCatalogue[hash] || {
                title: 'Module in Active Development',
                desc: 'This enterprise ERP module is currently in sprint engineering for the MEMA ERP platform.',
                icon: 'sparkles',
                progress: 85,
                release: 'Q4 2026 Release',
                features: [
                    'Enterprise Role Permissions',
                    'Audit Log & Tracking Engine',
                    'Real-Time Telemetry & Reports',
                    'API & Webhook Integrations'
                ]
            };

            const modal = document.getElementById('coming-soon-modal');
            if (!modal) return;

            document.getElementById('cs-title').textContent = data.title;
            document.getElementById('cs-desc').textContent = data.desc;
            document.getElementById('cs-progress-text').textContent = data.progress + '% Completed';
            document.getElementById('cs-progress-bar').style.width = data.progress + '%';
            document.getElementById('cs-release-text').textContent = 'Target: ' + data.release;
            
            // Icon
            const iconContainer = document.getElementById('cs-icon-container');
            iconContainer.innerHTML = `<i data-lucide="${data.icon}" class="w-7 h-7 text-white"></i>`;

            // Features
            const featContainer = document.getElementById('cs-features-container');
            featContainer.innerHTML = data.features.map(f => `
                <div class="coming-soon-feature-item">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                    <span>${f}</span>
                </div>
            `).join('');

            // Reset notify form
            const notifyForm = document.getElementById('cs-notify-form');
            const notifySuccess = document.getElementById('cs-notify-success');
            if (notifyForm) notifyForm.classList.remove('hidden');
            if (notifySuccess) notifySuccess.classList.add('hidden');

            modal.classList.add('open');
            lucide.createIcons();
        }

        // Intercept hash navigation on links
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            const href = a.getAttribute('href');
            if (href && href !== '#' && href !== '#main-content' && !a.hasAttribute('data-modal-open')) {
                a.addEventListener('click', (e) => {
                    if (moduleCatalogue[href]) {
                        e.preventDefault();
                        history.pushState(null, '', href);
                        showComingSoon(href);
                    }
                });
            }
        });

        // Check hash on page load (e.g. /dashboard#smhr)
        window.addEventListener('load', () => {
            const currentHash = window.location.hash;
            if (currentHash && moduleCatalogue[currentHash]) {
                showComingSoon(currentHash);
            }
        });

        window.addEventListener('hashchange', () => {
            const currentHash = window.location.hash;
            if (currentHash && moduleCatalogue[currentHash]) {
                showComingSoon(currentHash);
            }
        });

        document.querySelectorAll('[data-modal-open]').forEach(b => b.onclick = (e) => {
            e.preventDefault();
            const modalId = b.dataset.modalOpen;
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('open');
                if (modalId === 'menu-search-modal') {
                    setTimeout(() => {
                        const input = document.getElementById('menu-search-input');
                        if (input) input.focus();
                    }, 50);
                }
            }
            b.closest('details')?.removeAttribute('open');
        });

        document.querySelectorAll('[data-modal-close]').forEach(b => b.onclick = () => {
            b.closest('.modal').classList.remove('open');
            if (window.location.hash && moduleCatalogue[window.location.hash]) {
                history.pushState(null, '', window.location.pathname);
            }
        });

        document.querySelectorAll('[data-preference-tab]').forEach(b => b.onclick = () => {
            document.querySelectorAll('.preference-tab,.preference-panel').forEach(e => e.classList.remove(
                'active'));
            b.classList.add('active');
            document.querySelector(`[data-preference-panel="${b.dataset.preferenceTab}"]`).classList.add('active')
        });

        document.addEventListener('click', e => {
            document.querySelectorAll('.user-menu[open]').forEach(menu => {
                if (!menu.contains(e.target)) menu.removeAttribute('open');
            });
        });

        // Menu Search functionality
        const menuSearchInput = document.getElementById('menu-search-input');
        const menuSearchList = document.getElementById('menu-search-list-items');
        const menuSearchTrigger = document.getElementById('menu-search-trigger-btn');
        const menuSearchDropdown = document.getElementById('menu-search-dropdown-box');
        const menuSearchArrow = document.getElementById('menu-search-arrow-icon');
        const menuSearchSelectedLabel = document.getElementById('menu-search-selected-label');

        if (menuSearchTrigger && menuSearchDropdown) {
            menuSearchTrigger.addEventListener('click', () => {
                const isOpen = menuSearchDropdown.style.display !== 'none';
                menuSearchDropdown.style.display = isOpen ? 'none' : 'block';
                menuSearchTrigger.classList.toggle('active', !isOpen);
                if (menuSearchArrow) {
                    menuSearchArrow.setAttribute('data-lucide', isOpen ? 'chevron-down' : 'chevron-up');
                    lucide.createIcons();
                }
            });
        }

        if (menuSearchInput && menuSearchList) {
            menuSearchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                const items = menuSearchList.querySelectorAll('.menu-search-item');
                items.forEach(item => {
                    const name = item.dataset.name || '';
                    if (!query || name.includes(query)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }

        if (menuSearchList) {
            menuSearchList.addEventListener('click', (e) => {
                const item = e.target.closest('.menu-search-item');
                if (item) {
                    item.classList.toggle('checked');
                    const title = item.querySelector('.module-title')?.textContent || '';
                    if (menuSearchSelectedLabel) {
                        const checkedCount = menuSearchList.querySelectorAll('.menu-search-item.checked').length;
                        menuSearchSelectedLabel.textContent = checkedCount === 1 ? title : (checkedCount > 1 ? `${checkedCount} items selected` : 'Select Menu');
                    }
                    const url = item.dataset.url;
                    if (url && url !== '#' && !url.startsWith('#')) {
                        // Direct navigation to live route
                        window.location.href = url;
                    } else if (url && url.startsWith('#') && moduleCatalogue[url]) {
                        // Open Coming Soon for in-progress module
                        document.getElementById('menu-search-modal').classList.remove('open');
                        showComingSoon(url);
                    }
                }
            });
        }

        // Global hotkey (Cmd+K / Ctrl+K) to open Menu Search
        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                const menuModal = document.getElementById('menu-search-modal');
                if (menuModal) {
                    menuModal.classList.add('open');
                    setTimeout(() => {
                        const input = document.getElementById('menu-search-input');
                        if (input) input.focus();
                    }, 50);
                }
            } else if (e.key === 'Escape') {
                document.querySelectorAll('.modal.open').forEach(m => m.classList.remove('open'));
            }
        });
    </script>
    @include('components.cookie-consent')
</body>

</html>
