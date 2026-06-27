<h1>Edytuj aktywność</h1>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Opis</label>
            <input type="text" name="description" value="<?= htmlspecialchars($activity->description) ?>" maxlength="500" required>
        </div>

        <div class="form-group">
            <label>Projekt</label>
            <select name="project_id" disabled>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= $p->id ?>" <?= $p->id == $activity->project_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="color: var(--text-light);">Nie można zmienić projektu</small>
        </div>

        <div class="form-row">
            <div>
                <label>Data</label>
                <input type="date" name="date" value="<?= $activity->date ?>" required>
            </div>
            <div>
                <label>Od</label>
                <input type="time" name="time_from" value="<?= $activity->time_from ?>" required>
            </div>
            <div>
                <label>Do</label>
                <input type="time" name="time_to" value="<?= $activity->time_to ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_billed" <?= $activity->is_billed ? 'checked' : '' ?>>
                Rozliczona
            </label>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Zapisz</button>
            <a href="/tracker" class="btn btn-secondary">Anuluj</a>
        </div>
    </form>
</div>

<div style="margin-top: 20px;">
    <form method="POST" action="/activities/<?= $activity->id ?>" style="display: inline;">
        <input type="hidden" name="_method" value="DELETE">
        <button type="submit" class="btn btn-danger" onclick="return confirm('Jesteś pewny?')">Usuń</button>
    </form>
</div>
