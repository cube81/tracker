<?php
// Load .env
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

// Environment
define('ENV', getenv('APP_ENV') ?: 'production');
define('DEBUG', ENV === 'development');

// Database
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'tracker');

// App
define('APP_URL', getenv('APP_URL') ?: 'https://projekty.pgms.pl');
define('APP_NAME', 'Tracker');

// Mail
define('MAIL_FROM', getenv('MAIL_FROM') ?: 'noreply@pgms.pl');
define('MAIL_FROM_NAME', 'Tracker');
define('SMTP_HOST', getenv('SMTP_HOST') ?: '');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');

// Sessions
ini_set('session.name', 'tracker_session');
ini_set('session.gc_maxlifetime', 86400 * 7); // 7 dni
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', ENV === 'production');
ini_set('session.cookie_samesite', 'Lax');
