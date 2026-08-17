<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$currentPage = 'amenities';
$pageTitle = 'Amenities';

$db = new SQLite3(__DIR__ . '/../data/new.sqlite.db');

// Handle form submission for adding/editing amenities
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $id        = $_POST['id'] ?? null;
    $name      = trim($_POST['name'] ?? '');
    $icon      = trim($_POST['icon'] ?? '');
    $icon_type_name = trim($_POST['icon_type_name'] ?? 'bi');

    if ($name === '') {
        $error = 'Amenity name cannot be empty.';
    } else {
        if ($id) {
            $stmt = $db->prepare('UPDATE link_amenities SET name = :name, icon = :icon, icon_type_name = :icon_type_name WHERE id = :id');
            $stmt->bindValue(':name',      $name,         SQLITE3_TEXT);
            $stmt->bindValue(':icon',      $icon ?: null, SQLITE3_TEXT);
            $stmt->bindValue(':icon_type_name', $icon_type_name, SQLITE3_TEXT);
            $stmt->bindValue(':id',        (int)$id,      SQLITE3_INTEGER);
            $stmt->execute();
        } else {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $name)));
            $stmt = $db->prepare('INSERT INTO link_amenities (name, slug, icon, icon_type_name, is_active) VALUES (:name, :slug, :icon, :icon_type_name, 1)');
            $stmt->bindValue(':name',      $name,         SQLITE3_TEXT);
            $stmt->bindValue(':slug',      $slug,         SQLITE3_TEXT);
            $stmt->bindValue(':icon',      $icon ?: null, SQLITE3_TEXT);
            $stmt->bindValue(':icon_type_name', $icon_type_name, SQLITE3_TEXT);
            $stmt->execute();
        }
        header('Location: /admin/amenities.php');
        exit;
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $stmt = $db->prepare('DELETE FROM link_amenities WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
    header('Location: /admin/amenities.php');
    exit;
}

// Handle active toggle (AJAX)
if (isset($_GET['toggle'])) {
    $id   = (int)$_GET['toggle'];
    $stmt = $db->prepare('UPDATE link_amenities SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        $row = $db->querySingle("SELECT is_active FROM link_amenities WHERE id = $id");
        header('Content-Type: application/json');
        echo json_encode(['is_active' => (int)$row]);
        exit;
    }
    header('Location: /admin/amenities.php');
    exit;
}

// Fetch all amenities
$amenitiesResult = $db->query('SELECT * FROM link_amenities ORDER BY name');
$amenities = [];
while ($row = $amenitiesResult->fetchArray(SQLITE3_ASSOC)) {
    $amenities[] = $row;
}

require_once __DIR__ . '/layout-header.php';
?>

<style>
    .amenity-card {
        background: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 4px;
        padding: 24px;
        margin-bottom: 32px;
    }
    .amenity-card h2 {
        font-size: 11px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #888;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #2a2a2a;
    }
    .form-label-dark {
        font-size: 10px;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #666;
        margin-bottom: 6px;
        display: block;
    }
    .form-control-dark,
    .form-select-dark {
        background: #0d0d0d;
        border: 1px solid #2a2a2a;
        color: #ccc;
        border-radius: 2px;
        font-size: 13px;
        padding: 10px 12px;
        width: 100%;
        transition: border-color 0.15s;
    }
    .form-control-dark:focus,
    .form-select-dark:focus {
        background: #0d0d0d;
        border-color: #444;
        color: #fff;
        outline: none;
        box-shadow: none;
    }
    .form-hint { color: #444; font-size: 11px; margin-top: 4px; }

    .icon-preview-box {
        background: #0d0d0d;
        border: 1px solid #2a2a2a;
        border-radius: 2px;
        padding: 10px 12px;
        min-height: 42px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #e8a045;
        font-size: 18px;
    }
    .icon-preview-box .icon-cls-label { font-size: 11px; font-family: monospace; color: #555; }

    .btn-add {
        background: transparent;
        border: 1px solid #e8a045;
        color: #e8a045;
        font-size: 10px;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 8px 20px;
        border-radius: 2px;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-add:hover { background: #e8a045; color: #000; }

    .btn-action {
        font-size: 10px;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 2px;
        border: 1px solid;
        background: transparent;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
    }
    .btn-action-edit   { color: #C8D0C8; background-color: #1A1E1A; border-color: #3d3d3d; }
    .btn-action-edit:hover   { border-color: #9ebffe; color: #9ebffe; }
    .btn-action-delete { color: #e05555; border-color: #e05555; }
    .btn-action-delete:hover { background: #e05555; color: #fff; }

    .amenity-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .amenity-table thead tr { border-bottom: 1px solid #2a2a2a; }
    .amenity-table th {
        font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase;
        color: #555; padding: 10px 14px; font-weight: 400; text-align: left;
    }
    .amenity-table td {
        padding: 12px 14px; color: #aaa;
        border-bottom: 1px solid #1a1a1a; vertical-align: middle;
    }
    .amenity-table tbody tr:hover { background: #1f1f1f; }

    .icon-preview-cell { width: 48px; text-align: center; }
    .icon-preview-cell i { font-size: 18px; color: #e8a045; }
    .icon-preview-cell .no-icon {
        display: inline-block;
        width: 26px; height: 26px;
        border: 1px dashed #2a2a2a;
        border-radius: 2px;
    }

    .icon-type-pill {
        display: inline-block;
        background: #222; color: #777; border: 1px solid #333;
        font-size: 9px; padding: 2px 7px; border-radius: 2px;
        letter-spacing: 1.5px; font-family: monospace; text-transform: uppercase;
    }
    .icon-name-cell { color: #555; font-size: 11px; font-family: monospace; vertical-align: middle; }

    .toggle-switch { position: relative; display: inline-block; width: 38px; height: 20px; flex-shrink: 0; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; inset: 0;
        background: #2a2a2a; border: 1px solid #3a3a3a;
        border-radius: 20px; cursor: pointer;
        transition: background 0.25s, border-color 0.25s;
    }
    .toggle-slider::before {
        content: ''; position: absolute;
        width: 13px; height: 13px; left: 3px; top: 3px;
        background: #555; border-radius: 50%;
        transition: transform 0.25s, background 0.25s;
    }
    .toggle-switch input:checked + .toggle-slider { background: rgba(80,200,120,.15); border-color: #50c878; }
    .toggle-switch input:checked + .toggle-slider::before { background: #50c878; transform: translateX(18px); }

    .page-heading {
        font-size: 11px; letter-spacing: 3px; text-transform: uppercase;
        color: #666; margin-bottom: 28px; padding-bottom: 16px;
        border-bottom: 1px solid #1e1e1e;
    }

    .modal-backdrop-custom {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.78);
        z-index: 1050;
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        align-items: center;
        justify-content: center;
    }
    .modal-backdrop-custom.show { display: flex; animation: bdIn 0.18s ease; }
    @keyframes bdIn { from { opacity:0; } to { opacity:1; } }

    .edit-modal {
        background: #1a1a1a;
        border: 1px solid #2e2e2e;
        border-radius: 4px;
        width: 100%;
        max-width: 500px;
        margin: 0 16px;
        box-shadow: 0 32px 80px rgba(0,0,0,0.7);
        animation: mdIn 0.22s cubic-bezier(0.34,1.3,0.64,1);
        overflow: hidden;
    }
    @keyframes mdIn {
        from { opacity:0; transform: translateY(-20px) scale(0.96); }
        to   { opacity:1; transform: translateY(0) scale(1); }
    }

    .edit-modal-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px; border-bottom: 1px solid #242424;
    }
    .edit-modal-header h3 {
        font-size: 10px; letter-spacing: 2.5px; text-transform: uppercase;
        color: #777; margin: 0; display: flex; align-items: center; gap: 10px;
    }
    .edit-modal-header h3::before {
        content: ''; display: inline-block;
        width: 3px; height: 13px; border-radius: 2px;
    }
    #editModalBackdrop .edit-modal-header h3::before { background: #4a9aea; }
    #addModalBackdrop  .edit-modal-header h3::before { background: #e8a045; }

    .modal-close-btn {
        background: transparent; border: 1px solid #2a2a2a; color: #555;
        width: 26px; height: 26px; border-radius: 2px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; line-height: 1; transition: all 0.15s; padding: 0;
    }
    .modal-close-btn:hover { border-color: #e05555; color: #e05555; }

    .edit-modal-body { padding: 20px; }

    .edit-modal-footer {
        padding: 12px 20px 16px;
        display: flex; align-items: center; gap: 10px;
        border-top: 1px solid #1e1e1e;
    }
    .btn-modal-save {
        background: transparent; border: 1px solid #4a9aea; color: #4a9aea;
        font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase;
        padding: 8px 22px; border-radius: 2px; cursor: pointer; transition: all 0.2s;
    }
    .btn-modal-save:hover { background: #4a9aea; color: #000; }
    .btn-modal-save-amber {
        background: transparent; border: 1px solid #e8a045; color: #e8a045;
        font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase;
        padding: 8px 22px; border-radius: 2px; cursor: pointer; transition: all 0.2s;
    }
    .btn-modal-save-amber:hover { background: #e8a045; color: #000; }
    .btn-modal-cancel {
        background: transparent; border: 1px solid #2a2a2a; color: #555;
        font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase;
        padding: 8px 18px; border-radius: 2px; cursor: pointer; transition: all 0.2s;
    }
    .btn-modal-cancel:hover { border-color: #444; color: #888; }

    .add-trigger-card {
        background: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 4px;
        padding: 20px 24px;
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .add-trigger-card p { font-size: 12px; color: #555; margin: 0; }
</style>

<div class="container-fluid px-4 py-4">

    <div class="page-heading">Amenities</div>

    <?php if (isset($error)): ?>
        <div class="mb-4" style="background:#2a1a1a;border:1px solid #e05555;color:#e05555;font-size:12px;border-radius:2px;padding:12px 16px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="add-trigger-card">
        <p>Create a new amenity with a name, icon type, and icon class.</p>
        <button type="button" class="btn-add" id="openAddModalBtn">+ Add Amenity</button>
    </div>

    <div class="amenity-card">
        <h2>Existing Amenities &nbsp;<span style="color:#2e2e2e;">(<?= count($amenities) ?>)</span></h2>
        <table class="amenity-table">
            <thead>
                <tr>
                    <th style="width:56px;">Icon</th>
                    <th>Name</th>
                    <th>Icon Type</th>
                    <th>Icon Class</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($amenities as $amenity): ?>
                <?php
                    $typeKey       = !empty($amenity['icon_type_name']) ? $amenity['icon_type_name'] : 'bi';
                    $typeLabels    = ['bi' => 'Bootstrap', 'fa' => 'Font Awesome', 'mi' => 'Material', 'cu' => 'Custom'];
                    $pillLabel     = strtoupper($typeKey);
                    $typeFullLabel = $typeLabels[$typeKey] ?? strtoupper($typeKey);
                ?>
                <tr>
                    <td class="icon-preview-cell">
                        <?php if (!empty($amenity['icon'])): ?>
                            <?php if ($typeKey === 'cu'): ?>
                                <!-- Custom: render as image -->
                                <img src="/<?= htmlspecialchars($amenity['icon']) ?>"
                                     style="width:24px;height:24px;object-fit:contain;"
                                     alt="<?= htmlspecialchars($amenity['name']) ?>">
                            <?php elseif ($typeKey === 'mi'): ?>
                                <!-- Material Icons: ligature text -->
                                <span class="material-icons"
                                      style="color:#9ebffe;font-size:20px;vertical-align:middle;line-height:1;">
                                    <?= htmlspecialchars($amenity['icon']) ?>
                                </span>
                            <?php else: ?>
                                <!-- Bootstrap Icons / Font Awesome: CSS class on <i> -->
                                <i class="<?= htmlspecialchars($amenity['icon']) ?>"
                                   style="color:#9ebffe;font-size:20px;vertical-align:middle;line-height:1;"></i>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="no-icon"></span>
                        <?php endif; ?>
                    </td>

                    <td style="color:#ccc;"><?= htmlspecialchars($amenity['name']) ?></td>

                    <td>
                        <span class="icon-type-pill"><?= htmlspecialchars($pillLabel) ?></span>
                        <span style="color:#444;font-size:11px;margin-left:6px;"><?= htmlspecialchars($typeFullLabel) ?></span>
                    </td>

                    <td>
                        <?php if (!empty($amenity['icon'])): ?>
                            <span class="icon-name-cell"><?= htmlspecialchars($amenity['icon']) ?></span>
                        <?php else: ?>
                            <span style="color:#2e2e2e;font-size:11px;">— no icon —</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <label class="toggle-switch" title="<?= $amenity['is_active'] ? 'Active' : 'Inactive' ?>">
                                <input type="checkbox"
                                       <?= $amenity['is_active'] ? 'checked' : '' ?>
                                       onchange="toggleAmenity(<?= $amenity['id'] ?>, this)">
                                <span class="toggle-slider"></span>
                            </label>

                            <button type="button"
                                    class="btn-action btn-action-edit js-edit-btn"
                                    data-id="<?= $amenity['id'] ?>"
                                    data-name="<?= htmlspecialchars($amenity['name'], ENT_QUOTES) ?>"
                                    data-icon="<?= htmlspecialchars($amenity['icon'] ?? '', ENT_QUOTES) ?>"
                                    data-icon-type="<?= htmlspecialchars($typeKey, ENT_QUOTES) ?>">
                                Edit
                            </button>

                            <a href="/admin/amenities.php?delete=<?= $amenity['id'] ?>"
                               class="btn-action btn-action-delete"
                               onclick="return confirm('Delete this amenity?')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- ADD MODAL -->
<div class="modal-backdrop-custom" id="addModalBackdrop">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <h3>Add New Amenity</h3>
            <button type="button" class="modal-close-btn js-close-add" aria-label="Close">&times;</button>
        </div>
        <form method="POST" action="/admin/amenities.php" id="addForm">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <div class="edit-modal-body">
                <div class="mb-3">
                    <label class="form-label-dark" for="add-name">Amenity Name</label>
                    <input type="text" class="form-control-dark" id="add-name" name="name"
                           required placeholder="e.g. Swimming Pool">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-5">
                        <label class="form-label-dark" for="add-icon-type">Icon Type</label>
                        <select class="form-select-dark" id="add-icon-type" name="icon_type_name"
                                onchange="updateAddHints()">
                            <option value="bi">Bootstrap Icons</option>
                            <option value="fa">Font Awesome</option>
                            <option value="mi">Material Icons</option>
                            <option value="cu">Custom</option>
                        </select>
                        <div class="form-hint" id="add-type-hint"></div>
                    </div>
                    <div class="col-7">
                        <label class="form-label-dark" for="add-icon" id="add-icon-label">Icon Class</label>
                        <input type="text" class="form-control-dark" id="add-icon" name="icon"
                               placeholder="e.g. bi-wifi" oninput="updateAddPreview()">
                        <div class="form-hint" id="add-name-hint"></div>
                    </div>
                </div>
                <div>
                    <label class="form-label-dark">Icon Preview</label>
                    <div class="icon-preview-box" id="add-preview-box">
                        <span style="color:#2e2e2e;font-size:12px;">— enter an icon class above to preview —</span>
                    </div>
                </div>
            </div>
            <div class="edit-modal-footer">
                <button type="submit" class="btn-modal-save-amber">Add Amenity</button>
                <button type="button" class="btn-modal-cancel js-close-add">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-backdrop-custom" id="editModalBackdrop">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <h3>Edit Amenity</h3>
            <button type="button" class="modal-close-btn" id="modalCloseBtn" aria-label="Close">&times;</button>
        </div>
        <form method="POST" action="/admin/amenities.php" id="editForm">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="id" id="modal-id">
            <div class="edit-modal-body">
                <div class="mb-3">
                    <label class="form-label-dark" for="modal-name">Amenity Name</label>
                    <input type="text" class="form-control-dark" id="modal-name" name="name" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-5">
                        <label class="form-label-dark" for="modal-icon-type">Icon Type</label>
                        <select class="form-select-dark" id="modal-icon-type" name="icon_type_name"
                                onchange="updateModalHints()">
                            <option value="bi">Bootstrap Icons</option>
                            <option value="fa">Font Awesome</option>
                            <option value="mi">Material Icons</option>
                            <option value="cu">Custom</option>
                        </select>
                        <div class="form-hint" id="modal-type-hint"></div>
                    </div>
                    <div class="col-7">
                        <label class="form-label-dark" for="modal-icon" id="modal-icon-label">Icon Class</label>
                        <input type="text" class="form-control-dark" id="modal-icon" name="icon"
                               placeholder="e.g. bi-wifi" oninput="updateModalPreview()">
                        <div class="form-hint" id="modal-name-hint"></div>
                    </div>
                </div>
                <div>
                    <label class="form-label-dark">Icon Preview</label>
                    <div class="icon-preview-box" id="modal-preview-box">
                        <span style="color:#2e2e2e;font-size:12px;">— enter an icon class to preview —</span>
                    </div>
                </div>
            </div>
            <div class="edit-modal-footer">
                <button type="submit" class="btn-modal-save">Save Changes</button>
                <button type="button" class="btn-modal-cancel" id="modalCancelBtn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
const iconHints = {
    bi: { label: 'Icon Class',  type: 'Prefix: bi',                  name: 'e.g. bi-wifi, bi-water, bi-shield-lock' },
    fa: { label: 'Icon Class',  type: 'Prefix: fa / fas / far',      name: 'e.g. fa-car, fas fa-swimming-pool'      },
    mi: { label: 'Icon Class',  type: 'No prefix — ligature name',   name: 'e.g. restaurant, wifi, local_parking'   },
    cu: { label: 'Image Path',  type: 'Path relative to web root',   name: 'e.g. assets/icons/wifi.png'            }
};

/* ── shared preview renderer ── */
function renderPreview(cls, type, boxId) {
    const box = document.getElementById(boxId);
    if (!cls) {
        box.innerHTML = '<span style="color:#2e2e2e;font-size:12px;">— enter a value above to preview —</span>';
        return;
    }
    if (type === 'cu') {
        box.innerHTML = `<img src="/${cls}" style="width:24px;height:24px;object-fit:contain;"
            onerror="this.outerHTML='<span style=\'color:#e05555;font-size:11px;\'>Image not found: /${cls}</span>'"
            ><span class="icon-cls-label">${cls}</span>`;
    } else if (type === 'mi') {
        box.innerHTML = `<span class="material-icons" style="font-size:20px;color:#e8a045;">${cls}</span><span class="icon-cls-label">${cls}</span>`;
    } else {
        box.innerHTML = `<i class="${cls}" style="font-size:20px;color:#e8a045;"></i><span class="icon-cls-label">${cls}</span>`;
    }
}

/* ── shared hints updater ── */
function applyHints(typeSelId, typeHintId, nameHintId, iconLabelId, previewFn) {
    const sel = document.getElementById(typeSelId);
    if (!sel) return;
    const t = iconHints[sel.value] ? sel.value : 'bi';
    sel.value = t;
    document.getElementById(typeHintId).textContent  = iconHints[t].type;
    document.getElementById(nameHintId).textContent  = iconHints[t].name;
    document.getElementById(iconLabelId).textContent = iconHints[t].label;
    previewFn();
}

/* ── ADD MODAL ── */
function openAddModal() {
    document.getElementById('addForm').reset();
    applyHints('add-icon-type','add-type-hint','add-name-hint','add-icon-label', updateAddPreview);
    document.getElementById('add-preview-box').innerHTML =
        '<span style="color:#2e2e2e;font-size:12px;">— enter a value above to preview —</span>';
    document.getElementById('addModalBackdrop').classList.add('show');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('add-name').focus(), 50);
}
function closeAddModal() {
    document.getElementById('addModalBackdrop').classList.remove('show');
    document.body.style.overflow = '';
}
function updateAddHints() {
    applyHints('add-icon-type','add-type-hint','add-name-hint','add-icon-label', updateAddPreview);
}
function updateAddPreview() {
    const cls  = document.getElementById('add-icon').value.trim();
    const type = document.getElementById('add-icon-type').value;
    renderPreview(cls, type, 'add-preview-box');
}

/* ── EDIT MODAL ── */
function openEditModal(btn) {
    document.getElementById('modal-id').value        = btn.getAttribute('data-id');
    document.getElementById('modal-name').value      = btn.getAttribute('data-name');
    document.getElementById('modal-icon').value      = btn.getAttribute('data-icon');
    document.getElementById('modal-icon-type').value = btn.getAttribute('data-icon-type') || 'bi';
    updateModalHints();
    document.getElementById('editModalBackdrop').classList.add('show');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('modal-name').focus(), 50);
}
function closeEditModal() {
    document.getElementById('editModalBackdrop').classList.remove('show');
    document.body.style.overflow = '';
}
function updateModalHints() {
    applyHints('modal-icon-type','modal-type-hint','modal-name-hint','modal-icon-label', updateModalPreview);
}
function updateModalPreview() {
    const cls  = document.getElementById('modal-icon').value.trim();
    const type = document.getElementById('modal-icon-type').value;
    renderPreview(cls, type, 'modal-preview-box');
}

/* ── AJAX TOGGLE ── */
function toggleAmenity(id, checkbox) {
    fetch('/admin/amenities.php?toggle=' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const active = data.is_active === 1;
        checkbox.checked = active;
        checkbox.closest('label').title = active ? 'Active' : 'Inactive';
    })
    .catch(() => { checkbox.checked = !checkbox.checked; });
}

/* ── EVENT DELEGATION ── */
document.addEventListener('click', function (e) {
    const editBtn = e.target.closest('.js-edit-btn');
    if (editBtn) { openEditModal(editBtn); return; }
    if (e.target.closest('.js-close-add'))  { closeAddModal();  return; }
    if (e.target.closest('#openAddModalBtn')) { openAddModal(); return; }
    if (e.target.closest('#modalCloseBtn') || e.target.closest('#modalCancelBtn')) { closeEditModal(); return; }
    if (e.target.id === 'editModalBackdrop') { closeEditModal(); return; }
    if (e.target.id === 'addModalBackdrop')  { closeAddModal();  return; }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { closeEditModal(); closeAddModal(); }
});

document.addEventListener('DOMContentLoaded', function() {
    applyHints('add-icon-type','add-type-hint','add-name-hint','add-icon-label', updateAddPreview);
});
</script>