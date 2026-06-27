<h1>Nowy użytkownik</h1>

<div class="card">
    <?php if ($message): ?>
        <div class="error-message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required autofocus>
        </div>

        <div class="form-group">
            <label>Nazwa</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Rola</label>
            <select name="global_role" required>
                <option value="developer">Developer</option>
                <option value="pm">Project Manager</option>
                <option value="admin">Administrator</option>
            </select>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Utwórz</button>
            <a href="/users" class="btn btn-secondary">Anuluj</a>
        </div>

        <p style="margin-top: 20px; font-size: 13px; color: var(--text-light);">
            Tymczasowe hasło zostanie wysłane na email użytkownika.
        </p>
    </form>
</div>
