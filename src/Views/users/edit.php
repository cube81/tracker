<h1>Edytuj użytkownika</h1>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" value="<?= htmlspecialchars($user->email) ?>" disabled>
            <small style="color: var(--text-light);">Nie można zmienić emaila</small>
        </div>

        <div class="form-group">
            <label>Nazwa</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user->name) ?>" required>
        </div>

        <div class="form-group">
            <label>Rola</label>
            <select name="global_role" required>
                <option value="developer" <?= $user->global_role === 'developer' ? 'selected' : '' ?>>Developer</option>
                <option value="pm" <?= $user->global_role === 'pm' ? 'selected' : '' ?>>Project Manager</option>
                <option value="admin" <?= $user->global_role === 'admin' ? 'selected' : '' ?>>Administrator</option>
            </select>
        </div>

        <div class="form-group">
            <label>Nowe hasło (pozostaw puste by nie zmieniać)</label>
            <input type="password" name="password" placeholder="••••••••">
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" <?= $user->is_active ? 'checked' : '' ?>>
                Aktywny
            </label>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Zapisz</button>
            <a href="/users" class="btn btn-secondary">Anuluj</a>
        </div>
    </form>
</div>

<div style="margin-top: 20px;">
    <form method="POST" action="/users/<?= $user->id ?>" style="display: inline;">
        <button type="submit" class="btn btn-danger" onclick="return confirm('Jesteś pewny?')">Usuń użytkownika</button>
    </form>
</div>
