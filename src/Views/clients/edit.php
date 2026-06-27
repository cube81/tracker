<h1>Edytuj zleceniodawcę</h1>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>Nazwa</label>
            <input type="text" name="name" value="<?= htmlspecialchars($client->name) ?>" required>
        </div>

        <div class="form-group">
            <label>Opis</label>
            <textarea name="description"><?= htmlspecialchars($client->description) ?></textarea>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" <?= $client->is_active ? 'checked' : '' ?>>
                Aktywny
            </label>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Zapisz</button>
            <a href="/clients" class="btn btn-secondary">Anuluj</a>
        </div>
    </form>
</div>
