<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Denied — Asif OFC Protection</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: radial-gradient(ellipse at center, #1a2340 0%, #0a0e1f 70%, #05070f 100%);
            color: #e8dcc0;
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
        }
        .shield {
            font-size: 80px;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 20px rgba(212, 175, 55, 0.5));
        }
        h1 {
            font-size: 28px;
            background: linear-gradient(135deg, #f6e27a, #d4af37, #f6e27a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
            font-weight: 800;
        }
        p {
            color: #a89878;
            font-size: 15px;
            margin-bottom: 30px;
            max-width: 400px;
        }
        a {
            display: inline-block;
            padding: 12px 32px;
            background: linear-gradient(135deg, #d4af37, #b8962e);
            color: #0a0e1f;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            transition: transform 0.2s;
        }
        a:hover { transform: translateY(-2px); }
        .crown { font-size: 20px; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div>
        <div class="crown">👑</div>
        <div class="shield">🛡️</div>
        <h1>Access Denied</h1>
        <p>{{ $exception->getMessage() ?: '⛔ Access denied by Asif OFC protection' }}</p>
        <a href="/">← Back to Dashboard</a>
    </div>
</body>
</html>
