<?php use App\Core\Auth; ?>

<nav class="nav">
    <div class="nav-brand">
        <h2><?= APP_NAME ?></h2>
    </div>

    <?php if (Auth::check()): ?>
        <ul class="nav-menu">
            <li><a href="/" class="nav-link">Dashboard</a></li>
            <li><a href="/tracker" class="nav-link">Tracker</a></li>
            <li><a href="/activities/manage" class="nav-link">Zarządzanie</a></li>
            <li><a href="/reports" class="nav-link">Raporty</a></li>

            <?php if (Auth::isAdmin()): ?>
                <li class="nav-section">Zarządzanie</li>
                <li><a href="/clients" class="nav-link">Zleceniodawcy</a></li>
                <li><a href="/projects" class="nav-link">Projekty</a></li>
                <li><a href="/users" class="nav-link">Użytkownicy</a></li>
            <?php elseif (Auth::isPM()): ?>
                <li class="nav-section">Projekty</li>
                <li><a href="/projects" class="nav-link">Moje projekty</a></li>
            <?php endif; ?>
        </ul>

        <div class="nav-user">
            <p class="user-name"><?= htmlspecialchars(Auth::user()->name) ?></p>
            <p class="user-email"><?= htmlspecialchars(Auth::user()->email) ?></p>
            <a href="/logout" class="btn btn-logout">Wyloguj się</a>
        </div>
    <?php endif; ?>
</nav>
