<h1>Time Tracker</h1>

<div id="successMessage" class="success-message" style="display: none; margin-bottom: 20px;">
    ✓ Aktywność dodana!
</div>

<div class="tracker-form">
    <form id="activityForm">
        <div class="form-row">
            <div>
                <label>Opisanie</label>
                <input type="text" id="description" name="description" list="descriptions" placeholder="np. Aktualizacja wtyczek" required autofocus>
                <datalist id="descriptions"></datalist>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label>Projekt</label>
                <select id="project" name="project_id" required>
                    <option value="">-- Wybierz projekt --</option>
                    <?php foreach ($projects as $p): ?>
                        <option value="<?= $p->id ?>" data-color="<?= htmlspecialchars($p->color ?? '#1e90ff') ?>" <?= $defaultProjectId == $p->id ? 'selected' : '' ?>>
                            ● <?= htmlspecialchars($p->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label>Data</label>
                <input type="date" id="date" name="date" value="<?= $today ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label>Czas od</label>
                <input type="time" id="timeFrom" name="time_from" required>
            </div>
            <div>
                <label>Czas do</label>
                <input type="time" id="timeTo" name="time_to" required>
            </div>
            <div>
                <label>Czas wykonania</label>
                <div class="duration-display" id="duration">00:00</div>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Dodaj</button>
        </div>
    </form>
</div>

<?php if ($activities): ?>
    <div class="card">
        <h2 style="margin-bottom: 20px;">Historia aktywności</h2>
        <div id="activitiesList">
            <?php
            // Group activities by month
            $groupedByMonth = [];
            foreach ($activities as $activity) {
                $monthKey = date('Y-m', strtotime($activity->date));
                $monthLabel = date('F Y', strtotime($activity->date));
                if (!isset($groupedByMonth[$monthKey])) {
                    $groupedByMonth[$monthKey] = ['label' => $monthLabel, 'activities' => []];
                }
                $groupedByMonth[$monthKey]['activities'][] = $activity;
            }

            foreach ($groupedByMonth as $monthKey => $monthData):
            ?>
                <div style="margin-top: 30px; margin-bottom: 20px;">
                    <h3 style="color: var(--text-light); font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding-bottom: 10px; border-bottom: 2px solid var(--border);">
                        <?= htmlspecialchars($monthData['label']) ?>
                    </h3>
                    <div class="activities-list" style="margin-top: 15px;">
                        <?php foreach ($monthData['activities'] as $activity): ?>
                            <div class="activity-item" style="border-left-color: <?= htmlspecialchars($activity->project()->color ?? '#1e90ff') ?>;">
                                <div class="activity-info">
                                    <div class="activity-desc"><?= htmlspecialchars($activity->description) ?></div>
                                    <div class="activity-meta">
                                        <span style="display: inline-block; width: 12px; height: 12px; background-color: <?= htmlspecialchars($activity->project()->color ?? '#1e90ff') ?>; border-radius: 50%; margin-right: 6px; vertical-align: middle;"></span>
                                        <?= date('d.m.Y', strtotime($activity->date)) ?> • <?= substr($activity->time_from, 0, 5) ?> - <?= substr($activity->time_to, 0, 5) ?> •
                                        <?= htmlspecialchars($activity->project()->name) ?>
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
            <?php endforeach; ?>
        </div>
        <div style="margin-top: 30px; text-align: center;">
            <button id="loadMoreBtn" class="btn btn-secondary" onclick="loadMoreActivities()">Załaduj poprzedni miesiąc</button>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('activityForm');
    const descInput = document.getElementById('description');
    const projectSelect = document.getElementById('project');
    const timeFromInput = document.getElementById('timeFrom');
    const timeToInput = document.getElementById('timeTo');
    const durationDisplay = document.getElementById('duration');
    const successMsg = document.getElementById('successMessage');

    // Update form inputs color based on selected project
    function applyProjectColor() {
        const option = projectSelect.selectedOptions[0];
        const color = option?.dataset.color || '#333';
        form.querySelectorAll('input, select').forEach(input => {
            input.style.color = projectSelect.value ? color : '#333';
        });
    }
    projectSelect.addEventListener('change', applyProjectColor);
    applyProjectColor();

    // Handle form submission (AJAX)
    form.addEventListener('submit', e => {
        e.preventDefault();

        const formData = new FormData(form);
        fetch('/activities', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Show success message
                successMsg.style.display = 'block';
                setTimeout(() => {
                    successMsg.style.display = 'none';
                }, 3000);

                // Reset form
                form.reset();
                durationDisplay.textContent = '00:00';

                // Reload activities list
                location.reload();
            } else {
                alert('Błąd: ' + (data.error || 'Nie można dodać aktywności'));
            }
        })
        .catch(err => {
            alert('Błąd: ' + err.message);
        });
    });

    // Load descriptions from API
    function loadDescriptions(query) {
        if (query.length < 1) {
            document.getElementById('descriptions').innerHTML = '';
            return;
        }

        fetch('/api/descriptions?q=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(descriptions => {
                const datalist = document.getElementById('descriptions');
                datalist.innerHTML = descriptions
                    .map(d => '<option value="' + d + '">')
                    .join('');
            });
    }

    descInput.addEventListener('input', e => {
        loadDescriptions(e.target.value);
    });

    // Auto-format time input: 1700 -> 17:00
    function formatTime(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length >= 3) {
            const hours = value.substring(0, value.length - 2);
            const mins = value.substring(value.length - 2);
            input.value = String(hours).padStart(2, '0') + ':' + mins;
        }
    }

    timeFromInput.addEventListener('blur', () => formatTime(timeFromInput));
    timeToInput.addEventListener('blur', () => formatTime(timeToInput));

    // Calculate duration
    function calculateDuration() {
        if (!timeFromInput.value || !timeToInput.value) return;

        const [fromHour, fromMin] = timeFromInput.value.split(':').map(Number);
        const [toHour, toMin] = timeToInput.value.split(':').map(Number);

        const fromTotal = fromHour * 60 + fromMin;
        const toTotal = toHour * 60 + toMin;
        let duration = toTotal - fromTotal;

        if (duration < 0) duration += 24 * 60; // Handle next day

        const hours = Math.floor(duration / 60);
        const mins = duration % 60;
        durationDisplay.textContent = String(hours).padStart(2, '0') + ':' + String(mins).padStart(2, '0');
    }

    timeFromInput.addEventListener('change', calculateDuration);
    timeToInput.addEventListener('change', calculateDuration);

    // Tab navigation
    const inputs = form.querySelectorAll('input[type="text"], input[type="date"], input[type="time"], select');
    inputs.forEach((input, i) => {
        input.addEventListener('keydown', e => {
            if (e.key === 'Tab' && !e.shiftKey && i === inputs.length - 1) {
                e.preventDefault();
                form.querySelector('button[type="submit"]').focus();
            }
        });
    });

    // Auto-format on input (as user types)
    timeFromInput.addEventListener('input', e => {
        let val = e.target.value.replace(/\D/g, '');
        if (val.length === 4) {
            e.target.value = val.substring(0, 2) + ':' + val.substring(2);
        }
    });

    timeToInput.addEventListener('input', e => {
        let val = e.target.value.replace(/\D/g, '');
        if (val.length === 4) {
            e.target.value = val.substring(0, 2) + ':' + val.substring(2);
        }
    });
});

// Load more activities by month
let monthsBack = 1;  // Start from 2 (April) since we already show 0 (June) and 1 (May)
function loadMoreActivities() {
    const btn = document.getElementById('loadMoreBtn');
    btn.disabled = true;
    btn.textContent = 'Ładowanie...';

    monthsBack++;  // 2 → April, 3 → March, etc.

    fetch('/api/more-activities?months_back=' + monthsBack)
        .then(r => r.json())
        .then(data => {
            if (!data.activities || data.activities.length === 0) {
                btn.textContent = 'Koniec historii';
                btn.disabled = true;
                return;
            }

            const list = document.getElementById('activitiesList');

            // Create month section
            const monthDiv = document.createElement('div');
            monthDiv.style.marginTop = '30px';
            monthDiv.style.marginBottom = '20px';

            const monthHeader = document.createElement('h3');
            monthHeader.style.color = 'var(--text-light)';
            monthHeader.style.fontSize = '14px';
            monthHeader.style.fontWeight = '600';
            monthHeader.style.textTransform = 'uppercase';
            monthHeader.style.letterSpacing = '0.5px';
            monthHeader.style.paddingBottom = '10px';
            monthHeader.style.borderBottom = '2px solid var(--border)';
            monthHeader.textContent = data.month;

            const activitiesDiv = document.createElement('div');
            activitiesDiv.className = 'activities-list';
            activitiesDiv.style.marginTop = '15px';

            data.activities.forEach(act => {
                const div = document.createElement('div');
                div.className = 'activity-item';
                div.style.borderLeftColor = act.color || '#1e90ff';
                const actDate = new Date(act.date + 'T00:00:00');
                const formattedDate = String(actDate.getDate()).padStart(2, '0') + '.' + String(actDate.getMonth() + 1).padStart(2, '0') + '.' + actDate.getFullYear();
                div.innerHTML = `
                    <div class="activity-info">
                        <div class="activity-desc">${escapeHtml(act.description)}</div>
                        <div class="activity-meta">
                            <span style="display: inline-block; width: 12px; height: 12px; background-color: ${act.color || '#1e90ff'}; border-radius: 50%; margin-right: 6px; vertical-align: middle;"></span>
                            ${formattedDate} • ${act.time_from.substring(0, 5)} - ${act.time_to.substring(0, 5)} •
                            ${escapeHtml(act.project_name)}
                            ${act.is_billed ? '<span style="color: var(--success); margin-left: 10px; font-weight: 600;">✓</span>' : ''}
                        </div>
                    </div>
                    <div class="activity-time">${minutesToTime(act.duration_minutes)}</div>
                    <a href="/activities/${act.id}/edit" class="btn btn-secondary" style="margin-left: 10px;">Edytuj</a>
                `;
                activitiesDiv.appendChild(div);
            });

            monthDiv.appendChild(monthHeader);
            monthDiv.appendChild(activitiesDiv);
            list.appendChild(monthDiv);

            btn.disabled = false;
            btn.textContent = 'Załaduj poprzedni miesiąc';
        })
        .catch(err => {
            monthsBack--;
            btn.disabled = false;
            btn.textContent = 'Załaduj poprzedni miesiąc';
            alert('Błąd: ' + err.message);
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function minutesToTime(minutes) {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return String(hours).padStart(2, '0') + ':' + String(mins).padStart(2, '0');
}
</script>
