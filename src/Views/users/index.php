<div class="card-header">
    <h1>Użytkownicy</h1>
    <a href="/users/new" class="btn btn-primary">Nowy użytkownik</a>
</div>

<?php if ($users): ?>
    <table>
        <thead>
            <tr>
                <th>Email</th>
                <th>Nazwa</th>
                <th>Rola</th>
                <th>Status</th>
                <th style="width: 150px;">Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user->email) ?></td>
                    <td><?= htmlspecialchars($user->name) ?></td>
                    <td>
                        <?php if ($user->isAdmin()): ?>
                            <span style="color: var(--primary);">Admin</span>
                        <?php elseif ($user->isPM()): ?>
                            <span style="color: var(--warning);">PM</span>
                        <?php else: ?>
                            <span>Developer</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $user->is_active ? '<span style="color: var(--success);">Aktywny</span>' : '<span style="color: var(--text-light);">Nieaktywny</span>' ?></td>
                    <td>
                        <a href="/users/<?= $user->id ?>/edit" class="btn btn-secondary" style="font-size: 12px;">Edytuj</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="card">
        <p style="text-align: center; color: var(--text-light);">Brak użytkowników</p>
    </div>
<?php endif; ?>
