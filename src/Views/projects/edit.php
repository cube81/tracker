<h1>Edytuj projekt</h1>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Zleceniodawca</label>
            <select name="client_id" required>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= $c->id ?>" <?= $c->id == $project->client_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Nazwa</label>
            <input type="text" name="name" value="<?= htmlspecialchars($project->name) ?>" required>
        </div>

        <div class="form-group">
            <label>Opis</label>
            <textarea name="description"><?= htmlspecialchars($project->description) ?></textarea>
        </div>

        <div class="form-group">
            <label>Kolor projektu</label>
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="color" name="color" value="<?= htmlspecialchars($project->color ?? '#1e90ff') ?>" style="width: 100px; height: 40px; border: 1px solid var(--border); border-radius: 4px; cursor: pointer;">
                <span id="colorPreview" style="padding: 8px 16px; border-radius: 4px; background-color: <?= htmlspecialchars($project->color ?? '#1e90ff') ?>; color: white; font-size: 12px; font-weight: 600;">Podgląd</span>
            </div>
        </div>

        <div class="form-group">
            <label>Stawka netto za godzinę (zł)</label>
            <input type="number" name="hourly_rate" value="<?= number_format($project->hourly_rate ?? 0, 2, '.', '') ?>" step="0.01" min="0" placeholder="np. 150.00">
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" <?= $project->is_active ? 'checked' : '' ?>>
                Aktywny
            </label>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Zapisz</button>
            <a href="/projects" class="btn btn-secondary">Anuluj</a>
        </div>
    </form>
</div>

<script>
document.querySelector('input[name="color"]').addEventListener('change', e => {
    document.getElementById('colorPreview').style.backgroundColor = e.target.value;
});
</script>
