<?php
// Load .env
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $k => $v) {
        putenv("$k=$v");
    }
}

$host = getenv('DB_HOST');
$parts = explode(':', $host);
$h = $parts[0];
$p = $parts[1] ?? 3306;

try {
    $pdo = new PDO(
        'mysql:host=' . $h . ';port=' . $p . ';dbname=' . getenv('DB_NAME') . ';charset=utf8mb4',
        getenv('DB_USER'),
        getenv('DB_PASS'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Kolory dla każdego projektu
    $colors = ['#1e90ff', '#27ae60', '#e74c3c', '#f39c12', '#9b59b6', '#16a085', '#2980b9', '#d35400'];

    $projects = $pdo->query('SELECT id FROM projects ORDER BY id')->fetchAll();

    foreach ($projects as $i => $p) {
        $color = $colors[$i % count($colors)];
        $pdo->exec("UPDATE projects SET color = '" . $color . "' WHERE id = " . $p['id']);
        echo "✓ Projekt ID " . $p['id'] . " → " . $color . "\n";
    }

    echo "\n✓ Wszystkie projekty mają kolory!\n";
} catch (Exception $e) {
    echo "✗ Błąd: " . $e->getMessage() . "\n";
}
