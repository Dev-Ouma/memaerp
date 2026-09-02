<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MEMA ERP - System Maintenance</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0B1E26 0%, #050d11 100%);
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .card {
            background: rgba(15, 34, 43, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(230, 126, 34, 0.25);
            border-radius: 24px;
            padding: 40px;
            max-w: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        }
        .icon-container {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: rgba(230, 126, 34, 0.15);
            border: 1px solid rgba(230, 126, 34, 0.3);
            color: #E67E22;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 10px 15px -3px rgba(230, 126, 34, 0.2);
            animation: pulse 2.5s infinite ease-in-out;
        }
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(230, 126, 34, 0.4); }
            50% { transform: scale(1.05); box-shadow: 0 0 15px 5px rgba(230, 126, 34, 0.2); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(230, 126, 34, 0.4); }
        }
        h1 {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 14px;
            color: #ffffff;
            letter-spacing: -0.02em;
            text-transform: uppercase;
        }
        p {
            font-size: 13.5px;
            line-height: 1.6;
            color: #94a3b8;
            margin: 0 0 28px;
            font-weight: 500;
        }
        .meta-tag {
            font-family: monospace;
            font-size: 11px;
            color: #475569;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-container">
            <i data-lucide="lock" style="width: 32px; height: 32px;"></i>
        </div>
        <h1>System Under Maintenance</h1>
        <p>{{ $message ?? 'MEMA ERP is currently undergoing scheduled systems upgrade and maintenance. Please check back shortly.' }}</p>
        <div class="meta-tag">
            503 - SERVICE_UNAVAILABLE (MEMA ERP Governance Suite)
        </div>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
