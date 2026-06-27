<div class="card-header">
    <h1>Zleceniodawcy</h1>
    <a href="/clients/new" class="btn btn-primary">Nowy zleceniodawca</a>
</div>

<?php if ($clients): ?>
    <table>
        <thead>
            <tr>
                <th>Nazwa</th>
                <th>Status</th>
                <th style="width: 150px;">Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($client->name) ?></strong></td>
                    <td><?= $client->is_active ? '<span style="color: var(--success);">Aktywny</span>' : '<span style="color: var(--text-light);">Nieaktywny</span>' ?></td>
                    <td>
                        <a href="/clients/<?= $client->id ?>/edit" class="btn btn-secondary" style="font-size: 12px;">Edytuj</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="card">
        <p style="text-align: center; color: var(--text-light);">Brak zleceniodawców</p>
    </div>
<?php endif; ?>
