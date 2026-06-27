<style>
.btn-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 6px; border: 1px solid var(--border);
    background: var(--bg); color: var(--text-light); cursor: pointer;
    text-decoration: none; transition: all 0.15s; padding: 0;
    vertical-align: middle;
}
.btn-icon:hover { background: #f0f0f0; color: var(--text); border-color: #ccc; }
.btn-icon.active { background: var(--primary); border-color: var(--primary); color: white; }
.btn-icon.active:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
.btn-icon svg { width: 16px; height: 16px; flex-shrink: 0; }
.project-actions { display: flex; gap: 6px; align-items: center; }
</style>

<div class="card-header">
    <h1>Projekty</h1>
    <a href="/projects/new" class="btn btn-primary">Nowy projekt</a>
</div>

<?php if ($projects): ?>
    <table>
        <thead>
            <tr>
                <th>Nazwa</th>
                <th>Zleceniodawca</th>
                <th>Status</th>
                <th style="width: 120px;">Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $project): ?>
                <?php $isDefault = $defaultProjectId == $project->id; ?>
                <tr>
                    <td>
                        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?= htmlspecialchars($project->color ?? '#1e90ff') ?>;margin-right:8px;vertical-align:middle;"></span>
                        <strong><?= htmlspecialchars($project->name) ?></strong>
                        <?php if ($isDefault): ?>
                            <span style="margin-left:8px;font-size:11px;background:var(--primary);color:white;padding:2px 8px;border-radius:10px;vertical-align:middle;">domyślny</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($project->client()->name) ?></td>
                    <td><?= $project->is_active ? '<span style="color: var(--success);">Aktywny</span>' : '<span style="color: var(--text-light);">Nieaktywny</span>' ?></td>
                    <td>
                        <div class="project-actions">
                            <form method="POST" action="/projects/<?= $project->id ?>/set-default" style="display:contents;">
                                <button type="submit" class="btn-icon <?= $isDefault ? 'active' : '' ?>" title="<?= $isDefault ? 'Usuń domyślny' : 'Ustaw jako domyślny' ?>">
                                    <svg viewBox="0 0 24 24" fill="<?= $isDefault ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                </button>
                            </form>
                            <a href="/projects/<?= $project->id ?>/edit" class="btn-icon" title="Edytuj projekt">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            <a href="/projects/<?= $project->id ?>/team" class="btn-icon" title="Zarządzaj zespołem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="card">
        <p style="text-align: center; color: var(--text-light);">Brak projektów</p>
    </div>
<?php endif; ?>
