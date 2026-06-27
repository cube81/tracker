<div class="card-header">
    <h1><?= htmlspecialchars($project->name) ?></h1>
    <div>
        <a href="/projects/<?= $project->id ?>/edit" class="btn btn-secondary">Edytuj</a>
        <a href="/projects/<?= $project->id ?>/team" class="btn btn-secondary">Zespół</a>
    </div>
</div>

<div class="card">
    <h2>Informacje o projekcie</h2>
    <dl style="margin-top: 20px;">
        <dt style="font-weight: 600; margin-top: 10px;">Zleceniodawca</dt>
        <dd><?= htmlspecialchars($project->client()->name) ?></dd>

        <dt style="font-weight: 600; margin-top: 10px;">Status</dt>
        <dd><?= $project->is_active ? '<span style="color: var(--success);">Aktywny</span>' : '<span style="color: var(--text-light);">Nieaktywny</span>' ?></dd>

        <dt style="font-weight: 600; margin-top: 10px;">Opis</dt>
        <dd><?= nl2br(htmlspecialchars($project->description)) ?></dd>
    </dl>
</div>

<?php
$members = $project->members();
if ($members): ?>
    <div class="card">
        <h2>Zespół</h2>
        <table style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Imię</th>
                    <th>Rola</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?= htmlspecialchars($member->user()->email) ?></td>
                        <td><?= htmlspecialchars($member->user()->name) ?></td>
                        <td><?= $member->role === 'pm' ? 'Project Manager' : 'Developer' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
