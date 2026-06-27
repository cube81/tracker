<h1>Zarządzanie aktywnościami</h1>

<div class="card">
    <h2 style="margin-bottom: 20px;">Filtry</h2>
    <form method="GET" id="filterForm">
        <div class="form-row">
            <div>
                <label>Od</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" required>
            </div>
            <div>
                <label>Do</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" required>
            </div>
            <div>
                <label>Projekt</label>
                <select name="project_id">
                    <option value="">-- Wszystkie --</option>
                    <?php foreach ($projects as $p): ?>
                        <option value="<?= $p->id ?>" <?= $projectId == $p->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <button type="submit" class="btn btn-primary">Filtruj</button>
            <a href="/activities/manage" class="btn btn-secondary">Wyczyść</a>
        </div>
    </form>
</div>

<?php if ($activities): ?>
    <div class="card" style="margin-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Aktywności (<span id="selectedCount">0</span> zaznaczonych)</h2>
            <div>
                <button class="btn btn-primary" onclick="bulkMarkBilled()" style="margin-right: 10px;">Oznacz jako rozliczone</button>
                <button class="btn btn-secondary" onclick="bulkMarkUnbilled()" style="margin-right: 10px;">Odznacz</button>
                <button class="btn btn-danger" onclick="bulkDelete()">Usuń zaznaczone</button>
            </div>
        </div>

        <table style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 30px;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                    </th>
                    <th style="width: 100px;">Data</th>
                    <th style="width: 200px;">Opis</th>
                    <th style="width: 200px;">Projekt</th>
                    <th style="width: 110px; text-align: center;">Godziny</th>
                    <th style="width: 70px; text-align: right;">Czas</th>
                    <th style="width: 50px; text-align: center;">Rozliczona</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $activity): ?>
                    <?php $color = htmlspecialchars($activity->project()->color ?? '#1e90ff'); ?>
                    <tr style="border-left: 4px solid <?= $color ?>; background-color: <?= $activity->is_billed ? 'rgba(39, 174, 96, 0.05)' : 'transparent' ?>;">
                        <td style="width: 30px;">
                            <input type="checkbox" class="activity-checkbox" value="<?= $activity->id ?>" onchange="updateSelectedCount()">
                        </td>
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
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="card" style="margin-top: 30px;">
        <p style="text-align: center; color: var(--text-light);">Brak aktywności dla wybranych filtrów</p>
    </div>
<?php endif; ?>

<style>
.toast {
    position: fixed; top: 24px; left: 50%; transform: translateX(-50%);
    padding: 14px 28px; border-radius: 8px; font-size: 14px; font-weight: 600;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15); z-index: 9999;
    opacity: 0; transition: opacity 0.3s; pointer-events: none;
}
.toast.success { background: #27ae60; color: white; }
.toast.error   { background: #e74c3c; color: white; }
.toast.show    { opacity: 1; }

.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.45); z-index: 9000;
    align-items: center; justify-content: center;
}
.modal-overlay.show { display: flex; }
.modal-box {
    background: white; border-radius: 12px; padding: 36px 32px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.25); max-width: 400px; width: 90%;
    text-align: center;
}
.modal-box .modal-icon { font-size: 40px; margin-bottom: 16px; }
.modal-box h3 { font-size: 18px; margin-bottom: 10px; color: #1a1a2e; }
.modal-box p  { font-size: 14px; color: #666; margin-bottom: 24px; line-height: 1.5; }
.modal-actions { display: flex; gap: 12px; justify-content: center; }
.modal-actions .btn { min-width: 110px; }
</style>

<div class="toast" id="toast"></div>

<div class="modal-overlay" id="modalOverlay">
    <div class="modal-box">
        <div class="modal-icon" id="modalIcon"></div>
        <h3 id="modalTitle"></h3>
        <p id="modalMessage"></p>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeModal()">Anuluj</button>
            <button class="btn" id="modalConfirmBtn" onclick="modalConfirm()">Potwierdź</button>
        </div>
    </div>
</div>

<script>
let _modalCallback = null;

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast ' + type + ' show';
    setTimeout(() => { t.className = 'toast ' + type; }, 3000);
}

function showConfirm(icon, title, msg, btnLabel, btnClass, callback) {
    document.getElementById('modalIcon').textContent = icon;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = msg;
    const btn = document.getElementById('modalConfirmBtn');
    btn.textContent = btnLabel;
    btn.className = 'btn ' + btnClass;
    _modalCallback = callback;
    document.getElementById('modalOverlay').classList.add('show');
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('show');
    _modalCallback = null;
}

function modalConfirm() {
    const callback = _modalCallback;
    closeModal();
    if (callback) callback();
}

document.getElementById('modalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

function updateSelectedCount() {
    const checked = document.querySelectorAll('.activity-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked;
}

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.activity-checkbox').forEach(cb => cb.checked = checkbox.checked);
    updateSelectedCount();
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.activity-checkbox:checked')).map(cb => cb.value);
}

function bulkRequest(action, ids, successMsg) {
    const formData = new FormData();
    formData.append('action', action);
    ids.forEach(id => formData.append('activity_ids[]', id));
    fetch('/api/bulk-update', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(successMsg.replace('{n}', data.count));
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast('Błąd: ' + (data.error || 'Nieznany błąd'), 'error');
            }
        })
        .catch(err => showToast('Błąd: ' + err.message, 'error'));
}

function bulkMarkBilled() {
    const ids = getSelectedIds();
    if (!ids.length) { showToast('Zaznacz co najmniej jedną aktywność', 'error'); return; }
    showConfirm('✓', 'Oznacz jako rozliczone', 'Oznaczyć ' + ids.length + ' aktywności jako rozliczone?', 'Oznacz', 'btn-primary',
        () => bulkRequest('mark_billed', ids, '✓ {n} aktywności oznaczone jako rozliczone'));
}

function bulkMarkUnbilled() {
    const ids = getSelectedIds();
    if (!ids.length) { showToast('Zaznacz co najmniej jedną aktywność', 'error'); return; }
    showConfirm('↩', 'Odznacz rozliczenie', 'Odznączyć ' + ids.length + ' aktywności?', 'Odznacz', 'btn-secondary',
        () => bulkRequest('mark_unbilled', ids, '✓ {n} aktywności odznaczone'));
}

function bulkDelete() {
    const ids = getSelectedIds();
    if (!ids.length) { showToast('Zaznacz co najmniej jedną aktywność', 'error'); return; }
    showConfirm('🗑', 'Usuń aktywności', 'Usunąć ' + ids.length + ' aktywności? Tej operacji nie można cofnąć.', 'Usuń', 'btn-danger',
        () => bulkRequest('delete', ids, '✓ Usunięto {n} aktywności'));
}
</script>
