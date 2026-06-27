<?php use App\Core\Auth; ?>

<div class="card-header">
    <div>
        <h1>Cześć, <?= htmlspecialchars($user->name) ?>!</h1>
    </div>
    <div>
        <a href="/tracker" class="btn btn-primary">Dodaj aktywność</a>
    </div>
</div>


<div class="summary-stats">
    <div class="stat-box">
        <div class="stat-value"><?= minutes_to_time($todayTotal) ?></div>
        <div class="stat-label">Dzisiaj</div>
    </div>
    <div class="stat-box">
        <div class="stat-value"><?= $weekTotal ?></div>
        <div class="stat-label">Ostatnie 7 dni</div>
    </div>
</div>

<?php if ($activities): ?>
    <div class="card">
        <h2 style="margin-bottom: 20px;">Aktywności — <?= htmlspecialchars($activityPeriod) ?></h2>
        <div class="activities-list">
            <?php foreach ($activities as $activity): ?>
                <div class="activity-item" style="border-left-color: <?= htmlspecialchars($activity->project()->color ?? '#1e90ff') ?>;">
                    <div class="activity-info">
                        <div class="activity-desc"><?= htmlspecialchars($activity->description) ?></div>
                        <div class="activity-meta">
                            <span style="display: inline-block; width: 12px; height: 12px; background-color: <?= htmlspecialchars($activity->project()->color ?? '#1e90ff') ?>; border-radius: 50%; margin-right: 6px; vertical-align: middle;"></span>
                            <?= htmlspecialchars($activity->project()->name) ?> •
                            <?= date('d.m.Y', strtotime($activity->date)) ?> • <?= substr($activity->time_from, 0, 5) ?> - <?= substr($activity->time_to, 0, 5) ?>
                            <?php if ($activity->is_billed): ?>
                                <span style="color: var(--success); margin-left: 10px; font-weight: 600;">✓</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="activity-time"><?= minutes_to_time($activity->duration_minutes) ?></div>
                    <a href="/activities/<?= $activity->id ?>/edit" class="btn btn-secondary" style="margin-left: 10px;">Edytuj</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <p style="text-align: center; color: var(--text-light);">Brak aktywności. <a href="/tracker">Dodaj pierwszą</a></p>
    </div>
<?php endif; ?>

<div class="summary-stats">
    <div class="stat-box">
        <div class="stat-value"><?= minutes_to_time($unbilledMinutes) ?></div>
        <div class="stat-label">Do rozliczenia</div>
    </div>
    <div class="stat-box">
        <div class="stat-value"><?= $unbilledMinutes > 0 ? round(($unbilledMinutes / $totalMinutes) * 100, 1) : 0 ?>%</div>
        <div class="stat-label">Z całości</div>
    </div>
</div>

<?php if ($byProject): ?>
    <div class="card">
        <h2 style="margin-bottom: 20px;">Podsumowanie do rozliczenia</h2>
        <table>
            <thead>
                <tr>
                    <th>Projekt</th>
                    <th style="width: 200px; text-align: right;">Do rozliczenia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($byProject as $pid => $acts): ?>
                    <?php
                    $project = \App\Models\Project::find($pid);
                    $color = htmlspecialchars($project->color ?? '#1e90ff');
                    $unbilledMinutes = $projectFinances[$pid]['unbilled_minutes'] ?? 0;
                    $hourlyRate = $projectFinances[$pid]['rate'] ?? 0;
                    $unbilledHours = $unbilledMinutes / 60;
                    $unbilledAmount = $unbilledHours * $hourlyRate;
                    ?>
                    <?php if ($unbilledMinutes > 0): ?>
                        <tr>
                            <td>
                                <span style="display: inline-block; width: 12px; height: 12px; background-color: <?= $color ?>; border-radius: 50%; margin-right: 8px; vertical-align: middle;"></span>
                                <strong><?= htmlspecialchars($project->name) ?></strong>
                            </td>
                            <td style="text-align: right; font-size: 12px; color: var(--text-light);">
                                <strong style="color: var(--warning); font-size: 13px;">
                                    <?php if ($hourlyRate > 0): ?>
                                        <?= number_format($unbilledAmount, 2, ',', ' ') ?> zł
                                        <br><small>(<?= number_format($unbilledHours, 2, ',', '.') ?>h × <?= number_format($hourlyRate, 2, ',', '.') ?> zł)</small>
                                    <?php else: ?>
                                        <?= number_format($unbilledHours, 2, ',', '.') ?>h
                                    <?php endif; ?>
                                </strong>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
