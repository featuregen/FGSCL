<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0F172A; color: white; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { text-align: center; max-width: 500px; padding: 40px; }
        .error-code { font-size: 120px; font-weight: 800; background: linear-gradient(135deg, #F59E0B, #EF4444); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1; }
        .error-title { font-size: 24px; font-weight: 600; margin: 16px 0 12px; }
        .error-text { color: #9CA3AF; font-size: 15px; margin-bottom: 32px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: linear-gradient(135deg, #4F46E5, #7C3AED); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.3s; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(79, 70, 229, 0.4); }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">404</div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-text">The page you're looking for doesn't exist or has been moved.</p>
        <a href="javascript:history.back()" class="btn">← Go Back</a>
    </div>
</body>
</html>
