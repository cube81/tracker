<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 180 180'><defs><linearGradient id='g' x1='0%' y1='0%' x2='100%' y2='100%'><stop offset='0%' style='stop-color:%23667eea'/><stop offset='100%' style='stop-color:%23764ba2'/></linearGradient></defs><circle cx='90' cy='90' r='90' fill='url(%23g)'/><circle cx='90' cy='90' r='65' fill='none' stroke='white' stroke-width='4'/><line x1='90' y1='30' x2='90' y2='40' stroke='white' stroke-width='3'/><line x1='150' y1='90' x2='140' y2='90' stroke='white' stroke-width='3'/><line x1='90' y1='150' x2='90' y2='140' stroke='white' stroke-width='3'/><line x1='30' y1='90' x2='40' y2='90' stroke='white' stroke-width='3'/><line x1='90' y1='90' x2='90' y2='55' stroke='white' stroke-width='5' stroke-linecap='round'/><line x1='90' y1='90' x2='90' y2='35' stroke='white' stroke-width='3' stroke-linecap='round' opacity='0.8'/><circle cx='90' cy='90' r='6' fill='white'/></svg>">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <?php include __DIR__ . '/nav.php'; ?>
        </aside>
        <main class="main-content">
            <?= $content ?? '' ?>
        </main>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>
