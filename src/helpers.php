<?php

use App\Core\View;

function view(string $view, array $data = []): View {
    return View::make($view, $data);
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function abort(int $code): void {
    http_response_code($code);
    if ($code === 404) {
        echo "404 Not Found";
    } elseif ($code === 403) {
        echo "403 Forbidden";
    }
    exit;
}

function route(string $name, array $params = []): string {
    // TODO: implement named routes
    return '';
}

function csrf_token(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function old(string $key, $default = ''): string {
    return $_POST[$key] ?? $default;
}

function now(): string {
    return date('Y-m-d H:i:s');
}

function today(): string {
    return date('Y-m-d');
}

function minutes_to_time(int $minutes): string {
    $hours = intval($minutes / 60);
    $mins = $minutes % 60;
    return sprintf('%02d:%02d', $hours, $mins);
}

function time_to_minutes(string $from, string $to): int {
    $fromTime = DateTime::createFromFormat('H:i', $from);
    $toTime = DateTime::createFromFormat('H:i', $to);
    if ($fromTime && $toTime) {
        return (int)(($toTime->getTimestamp() - $fromTime->getTimestamp()) / 60);
    }
    return 0;
}
