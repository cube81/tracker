<?php
// Load .env
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$name = getenv('DB_NAME') ?: 'tracker';

// Handle port in host string
if (strpos($host, ':') !== false) {
    list($host, $port) = explode(':', $host);
} else {
    $port = 3306;
}

echo "Setting up database...\n";
echo "Connecting to $host:$port as $user\n";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sql = file_get_contents(__DIR__ . '/migrations/001_initial.sql');

    // Execute each statement
    foreach (explode(';', $sql) as $statement) {
        $statement = trim($statement);
        if ($statement) {
            $pdo->exec($statement);
            echo ".";
        }
    }

    echo "\n✓ Database setup complete!\n";
    echo "✓ Default login: admin@tracker.local / admin123\n";
} catch (Exception $e) {
    die("\n✗ Error: " . $e->getMessage() . "\n");
}
