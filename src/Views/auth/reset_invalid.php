<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — Link wygasł</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 180 180'><defs><linearGradient id='g' x1='0%' y1='0%' x2='100%' y2='100%'><stop offset='0%' style='stop-color:%23667eea'/><stop offset='100%' style='stop-color:%23764ba2'/></linearGradient></defs><circle cx='90' cy='90' r='90' fill='url(%23g)'/><circle cx='90' cy='90' r='65' fill='none' stroke='white' stroke-width='4'/><line x1='90' y1='30' x2='90' y2='40' stroke='white' stroke-width='3'/><line x1='150' y1='90' x2='140' y2='90' stroke='white' stroke-width='3'/><line x1='90' y1='150' x2='90' y2='140' stroke='white' stroke-width='3'/><line x1='30' y1='90' x2='40' y2='90' stroke='white' stroke-width='3'/><line x1='90' y1='90' x2='90' y2='55' stroke='white' stroke-width='5' stroke-linecap='round'/><line x1='90' y1='90' x2='90' y2='35' stroke='white' stroke-width='3' stroke-linecap='round' opacity='0.8'/><circle cx='90' cy='90' r='6' fill='white'/></svg>">
    <style>
* {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
}

.auth-login-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.auth-login-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
    width: 100%;
    max-width: 420px;
    overflow: hidden;
}

.auth-login-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 30px;
    text-align: center;
}

.auth-login-header-icon {
    width: 70px;
    height: 70px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 35px;
}

.auth-login-header h1 {
    margin: 0;
    font-size: 32px;
    font-weight: 700;
}

.auth-login-header p {
    margin: 8px 0 0 0;
    font-size: 14px;
    opacity: 0.9;
}

.auth-login-header.error {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.auth-login-body {
    padding: 40px 30px;
}

.error-message {
    background-color: #fdeaea;
    color: #c0392b;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
    border-left: 4px solid #c0392b;
}

.auth-login-form .btn {
    display: block;
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s;
    box-sizing: border-box;
}

.auth-login-form .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.auth-login-footer {
    padding: 0 30px 30px;
    text-align: center;
    border-top: 1px solid #e0e0e0;
    margin-top: 0;
}

.auth-login-footer p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.auth-login-footer a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s;
}

.auth-login-footer a:hover {
    color: #764ba2;
}
    </style>
</head>
<body>

<div class="auth-login-bg">
    <div class="auth-login-card">
        <div class="auth-login-header error">
            <div class="auth-login-header-icon">⚠️</div>
            <h1>Link wygasł</h1>
            <p>Spróbuj ponownie</p>
        </div>

        <div class="auth-login-body">
            <div class="error-message">
                Link resetujący jest nieprawidłowy lub wygasł. Linki są ważne 24 godziny.
            </div>

            <a href="/reset-password" class="auth-login-form btn">
                Wyślij nowy link
            </a>
        </div>

        <div class="auth-login-footer">
            <p><a href="/login">← Wróć do logowania</a></p>
        </div>
    </div>
</div>

</body>
</html>
