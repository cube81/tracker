<h1>Zespół: <?= htmlspecialchars($project->name) ?></h1>

<div class="card">
    <form method="POST">
        <p style="margin-bottom: 20px; color: var(--text-light);">Przypisz użytkowników do projektu i ustaw ich role</p>

        <table style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th style="width: 30px;"></th>
                    <th>Email</th>
                    <th style="width: 150px;">Rola</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allUsers as $i => $user): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="user_ids[]" value="<?= $user->id ?>"
                                <?= in_array($user->id, $memberUserIds) ? 'checked' : '' ?>>
                        </td>
                        <td><?= htmlspecialchars($user->email) ?></td>
                        <td>
                            <select name="roles[]" style="width: 100%;">
                                <option value="developer" <?= in_array($user->id, $memberUserIds) ? 'selected' : '' ?>>Developer</option>
                                <option value="pm">Project Manager</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Zapisz zespół</button>
            <a href="/projects" class="btn btn-secondary">Anuluj</a>
        </div>
    </form>
</div>
