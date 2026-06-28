<?php
use App\Core\Auth;

if (!function_exists('_navActive')) {
    function _navActive(string $href): string {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $match = ($href === '/') ? ($path === '/') : str_starts_with($path, $href);
        return $match ? ' active' : '';
    }
}
?>

<nav class="nav">
    <div class="nav-brand">
        <h2><?= APP_NAME ?></h2>
    </div>

    <?php if (Auth::check()): ?>
        <ul class="nav-menu">
            <li><a href="/" class="nav-link<?= _navActive('/') ?>">Dashboard</a></li>
            <li><a href="/tracker" class="nav-link<?= _navActive('/tracker') ?>">Tracker</a></li>
            <li><a href="/activities/manage" class="nav-link<?= _navActive('/activities') ?>">Zarządzanie</a></li>
            <li><a href="/reports" class="nav-link<?= _navActive('/reports') ?>">Raporty</a></li>

            <?php if (Auth::isAdmin()): ?>
                <li class="nav-section">Zarządzanie</li>
                <li><a href="/clients" class="nav-link<?= _navActive('/clients') ?>">Zleceniodawcy</a></li>
                <li><a href="/projects" class="nav-link<?= _navActive('/projects') ?>">Projekty</a></li>
                <li><a href="/users" class="nav-link<?= _navActive('/users') ?>">Użytkownicy</a></li>
            <?php elseif (Auth::isPM()): ?>
                <li class="nav-section">Projekty</li>
                <li><a href="/projects" class="nav-link<?= _navActive('/projects') ?>">Moje projekty</a></li>
            <?php endif; ?>
        </ul>

        <div class="nav-user">
            <p class="user-name"><?= htmlspecialchars(Auth::user()->name) ?></p>
            <p class="user-email"><?= htmlspecialchars(Auth::user()->email) ?></p>
            <a href="/logout" class="btn btn-logout">Wyloguj się</a>
        </div>
    <?php endif; ?>
</nav>
