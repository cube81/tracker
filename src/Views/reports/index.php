<?php use App\Core\Auth; ?>

<div class="card-header">
    <h1>Raporty</h1>
</div>

<div class="filters">
    <form method="GET" id="filterForm">
        <div class="filters-row">
            <div>
                <label>Od</label>
                <input type="date" name="date_from" value="<?= $dateFrom ?>">
            </div>
            <div>
                <label>Do</label>
                <input type="date" name="date_to" value="<?= $dateTo ?>">
            </div>
            <div>
                <label>Status</label>
                <select name="status">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Wszystkie</option>
                    <option value="billed" <?= $status === 'billed' ? 'selected' : '' ?>>Rozliczone</option>
                    <option value="unbilled" <?= $status === 'unbilled' ? 'selected' : '' ?>>Nierozliczone</option>
                </select>
            </div>

            <?php if ($user->isAdmin()): ?>
                <div>
                    <label>Projekt</label>
                    <select name="project_id">
                        <option value="">-- Wszystkie --</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= $p->id ?>" <?= $project_id == $p->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>

        <div class="filters-actions">
            <button type="submit" class="btn btn-primary">Filtruj</button>
            <a href="/reports" class="btn btn-secondary">Wyczyść</a>
            <a href="/reports/export?date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>&status=<?= $status ?><?= $project_id ? '&project_id=' . $project_id : '' ?>" class="btn btn-primary">Eksportuj PDF</a>
        </div>
    </form>
</div>

<div class="summary-stats">
    <div class="stat-box">
        <div class="stat-value"><?= minutes_to_time($totalMinutes) ?></div>
        <div class="stat-label">Razem</div>
    </div>
    <div class="stat-box">
        <div class="stat-value"><?= minutes_to_time($billedMinutes) ?></div>
        <div class="stat-label">Rozliczone</div>
    </div>
    <div class="stat-box">
        <div class="stat-value"><?= minutes_to_time($totalMinutes - $billedMinutes) ?></div>
        <div class="stat-label">Nierozliczone</div>
    </div>
</div>

<?php if ($activities): ?>
    <div class="card">
        <h2 style="margin-bottom: 20px;">Projekty</h2>
        <table>
            <thead>
                <tr>
                    <th>Projekt</th>
                    <th style="width: 100px; text-align: right;">Czas</th>
                    <th style="width: 80px; text-align: right;">%</th>
                    <th style="width: 200px; text-align: right;">Do rozliczenia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($byProject as $pid => $acts): ?>
                    <?php
                    $project = \App\Models\Project::find($pid);
                    $projectMinutes = array_sum(array_column($acts, 'duration_minutes'));
                    $percentage = $totalMinutes ? round(($projectMinutes / $totalMinutes) * 100, 1) : 0;
                    $color = htmlspecialchars($project->color ?? '#1e90ff');

                    // Calculate unbilled amount
                    $unbilledMinutes = $projectFinances[$pid]['unbilled_minutes'] ?? 0;
                    $hourlyRate = $projectFinances[$pid]['rate'] ?? 0;
                    $unbilledHours = $unbilledMinutes / 60;
                    $unbilledAmount = $unbilledHours * $hourlyRate;
                    ?>
                    <tr>
                        <td>
                            <span style="display: inline-block; width: 12px; height: 12px; background-color: <?= $color ?>; border-radius: 50%; margin-right: 8px; vertical-align: middle;"></span>
                            <strong><?= htmlspecialchars($project->name) ?></strong>
                        </td>
                        <td style="text-align: right;"><?= minutes_to_time($projectMinutes) ?></td>
                        <td style="text-align: right;"><?= $percentage ?>%</td>
                        <td style="text-align: right; font-size: 12px; color: var(--text-light);">
                            <?php if ($unbilledMinutes > 0): ?>
                                <strong style="color: var(--warning); font-size: 13px;">
                                    <?php if ($hourlyRate > 0): ?>
                                        <?= number_format($unbilledAmount, 2, ',', ' ') ?> zł
                                        <br><small>(<?= number_format($unbilledHours, 2, ',', '.') ?>h × <?= number_format($hourlyRate, 2, ',', '.') ?> zł)</small>
                                    <?php else: ?>
                                        <?= number_format($unbilledHours, 2, ',', '.') ?>h
                                    <?php endif; ?>
                                </strong>
                            <?php else: ?>
                                <span style="color: var(--success);">✓ Rozliczone</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2 style="margin-bottom: 20px;">Aktywności</h2>
        <table style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 100px;">Data</th>
                    <th style="width: 200px;">Opis</th>
                    <th style="width: 200px;">Projekt</th>
                    <th style="width: 110px; text-align: center;">Godziny</th>
                    <th style="width: 70px; text-align: right;">Czas</th>
                    <th style="width: 50px; text-align: center;">Rozliczona</th>
                    <th style="width: 60px; text-align: center;">Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $activity): ?>
                    <?php $color = htmlspecialchars($activity->project()->color ?? '#1e90ff'); ?>
                    <tr style="border-left: 4px solid <?= $color ?>; background-color: <?= $activity->is_billed ? 'rgba(39, 174, 96, 0.05)' : 'transparent' ?>;">
                        <td style="width: 100px; font-size: 13px;"><?= $activity->date ?></td>
                        <td style="width: 200px; font-size: 13px;"><?= htmlspecialchars(substr($activity->description, 0, 35)) ?></td>
                        <td style="width: 200px; font-size: 13px;">
                            <span style="display: inline-block; width: 10px; height: 10px; background-color: <?= $color ?>; border-radius: 50%; margin-right: 6px; vertical-align: middle;"></span>
                            <?= htmlspecialchars($activity->project()->name) ?>
                        </td>
                        <td style="width: 110px; text-align: center; font-size: 13px;"><?= substr($activity->time_from, 0, 5) ?> - <?= substr($activity->time_to, 0, 5) ?></td>
                        <td style="width: 70px; text-align: right; font-size: 13px;"><?= minutes_to_time($activity->duration_minutes) ?></td>
                        <td style="width: 50px; text-align: center;">
                            <?= $activity->is_billed ? '<span style="color: var(--success); font-weight: 600; font-size: 16px;">✓</span>' : '<span style="color: var(--text-light);">-</span>' ?>
                        </td>
                        <td style="width: 60px; text-align: center;">
                            <a href="/activities/<?= $activity->id ?>/edit" style="color: var(--primary); text-decoration: none; font-size: 12px;">Edytuj</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="card">
        <p style="text-align: center; color: var(--text-light);">Brak wyników dla wybranych filtrów</p>
    </div>
<?php endif; ?>
