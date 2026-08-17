<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$currentPage = 'collection';
$pageTitle = 'Collection';

$db = new SQLite3(__DIR__ . '/../data/new.sqlite.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['collection_name'] ?? '');
        if ($name !== '') {
            $stmt = $db->prepare('INSERT INTO link_collection (collection_name) VALUES (:name)');
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->execute();
        }
    } elseif ($action === 'edit') {
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['new_collection_name'] ?? '');
        if ($id > 0 && $name !== '') {
            $stmt = $db->prepare('UPDATE link_collection SET collection_name = :name WHERE id = :id');
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->bindValue(':id',   $id,   SQLITE3_INTEGER);
            $stmt->execute();
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare('DELETE FROM link_collection WHERE id = :id');
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $stmt->execute();
        }
    }

    header('Location: collection.php');
    exit;
}

$collections = [];
$result = $db->query('SELECT id, collection_name FROM link_collection ORDER BY id ASC');
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $collections[] = $row;
}

require_once __DIR__ . '/layout-header.php';
?>

<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --bg:        #000000;
        --surface:   #000;
        --card:      #0c0e0d;
        --gold:      #c8d0c8;
        --gold-dim:  #000000;
        --text:      #556055;
        --muted:     #556055;
        --danger:    #c0392b;
        --danger-dim:#7a2318;
        --radius:    10px;
    }

    body {
        background: var(--bg);
        color: var(--text);
        font-family: 'DM Sans', sans-serif;
        font-weight: 300;
        min-height: 100vh;
        padding: 2.5rem 2rem;
    }

    /* ── Page header ── */
    .page-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 2.2rem;
        padding-bottom: 1.2rem;
        border-bottom: 1px solid var(--border);
    }
    .page-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        font-weight: 700;
        color: #9ebffe;
        letter-spacing: .02em;
    }
    .page-header p {
        font-size: .82rem;
        color: var(--muted);
        margin-top: .2rem;
    }

    /* ── Add button ── */
    .btn-add {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        background: var(--gold);
        color: #0f0e0c;
        font-family: 'DM Sans', sans-serif;
        font-size: .82rem;
        font-weight: 500;
        padding: .55rem 1.1rem;
        border: none;
        border-radius: var(--radius);
        cursor: pointer;
        transition: background .18s, transform .12s;
        white-space: nowrap;
    }
    .btn-add:hover { background: #e0bb66; transform: translateY(-1px); }

    /* ── Table ── */
    .table-wrap {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }
    thead th {
        background: var(--surface);
        font-size: .72rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--muted);
        padding: .85rem 1.2rem;
        text-align: left;
    }
    tbody tr {
        border-top: 1px solid var(--border);
        transition: background .14s;
    }
    tbody tr:hover { background: #1e1c18; }
    td { padding: .9rem 1.2rem; font-size: .9rem; }
    td.num  { color: var(--muted); font-size: .78rem; width: 54px; }
    td.name { font-weight: 400; }
    td.actions { text-align: right; white-space: nowrap; }

    /* ── Row action buttons ── */
    .btn-edit, .btn-del {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-family: 'DM Sans', sans-serif;
        font-size: .78rem;
        font-weight: 500;
        padding: .38rem .85rem;
        border-radius: 6px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all .16s;
    }
    .btn-edit { background: #1a1e1a; color: #c8d0c8; }
    .btn-edit:hover { border-color: #9ebffe; color: #9ebffe; }
    .btn-del  { background: transparent; border-color: var(--danger-dim); color: #e05c4a; margin-left: .4rem; }
    .btn-del:hover  { background: var(--danger); color: #fff; border-color: var(--danger); }

    /* ── Empty state ── */
    .empty {
        text-align: center;
        padding: 3.5rem 1rem;
        color: var(--muted);
        font-size: .9rem;
    }

    /* ── Modal backdrop ── */
    .modal-bg {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.72);
        backdrop-filter: blur(3px);
        z-index: 100;
        align-items: center;
        justify-content: center;
    }
    .modal-bg.open { display: flex; }

    /* ── Modal box ── */
    .modal {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        width: 100%;
        max-width: 430px;
        padding: 2rem;
        position: relative;
        animation: slideUp .22s ease;
    }
    @keyframes slideUp {
        from { transform: translateY(18px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }
    .modal h2 {
        font-family: 'Playfair Display', serif;
        font-size: 1.25rem;
        color: var(--gold);
        margin-bottom: 1.4rem;
    }
    .modal-close {
        position: absolute;
        top: 1rem; right: 1rem;
        background: none;
        border: none;
        color: var(--muted);
        font-size: 1.3rem;
        cursor: pointer;
        line-height: 1;
        transition: color .15s;
    }
    .modal-close:hover { color: var(--text); }

    /* ── Form elements ── */
    .field { margin-bottom: 1.1rem; }
    .field label {
        display: block;
        font-size: .75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--muted);
        margin-bottom: .45rem;
    }
    .field input[type="text"] {
        width: 100%;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 7px;
        color: var(--text);
        font-family: 'DM Sans', sans-serif;
        font-size: .9rem;
        padding: .65rem .9rem;
        outline: none;
        transition: border-color .16s;
    }
    .field input[type="text"]:focus { border-color: var(--gold-dim); }
    .field input[readonly] { color: var(--muted); cursor: not-allowed; }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: .6rem;
        margin-top: 1.6rem;
    }
    .btn-cancel {
        background: transparent;
        border: 1px solid var(--border);
        color: var(--muted);
        font-family: 'DM Sans', sans-serif;
        font-size: .85rem;
        padding: .55rem 1.1rem;
        border-radius: 7px;
        cursor: pointer;
        transition: all .15s;
    }
    .btn-cancel:hover { border-color: var(--muted); color: var(--text); }

    .btn-submit {
        background: var(--gold);
        border: none;
        color: #0f0e0c;
        font-family: 'DM Sans', sans-serif;
        font-size: .85rem;
        font-weight: 500;
        padding: .55rem 1.3rem;
        border-radius: 7px;
        cursor: pointer;
        transition: background .15s, transform .12s;
    }
    .btn-submit:hover { background: #e0bb66; transform: translateY(-1px); }
    .btn-submit.danger { background: var(--danger); color: #fff; }
    .btn-submit.danger:hover { background: #e74c3c; }
</style>

<!-- ── Page Header ── -->
<div class="page-header">
    <div>
        <h1>Collections</h1>
        <p><?= count($collections) ?> collection<?= count($collections) !== 1 ? 's' : '' ?></p>
    </div>
    <button class="btn-add" onclick="openAdd()">
        <span style="font-size:1rem;line-height:1">＋</span> Add Collection
    </button>
</div>

<!-- ── Table ── -->
<div class="table-wrap">
    <?php if (empty($collections)): ?>
        <div class="empty">No collections found. Add one to get started.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Collection Name</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($collections as $c): ?>
            <tr>
                <td class="num"><?= $c['id'] ?></td>
                <td class="name"><?= htmlspecialchars($c['collection_name']) ?></td>
                <td class="actions">
                    <button class="btn-edit"
                            onclick="openEdit(<?= $c['id'] ?>, <?= htmlspecialchars(json_encode($c['collection_name'])) ?>)">
                        ✎ Edit
                    </button>
                    <button class="btn-del"
                            onclick="openDelete(<?= $c['id'] ?>, <?= htmlspecialchars(json_encode($c['collection_name'])) ?>)">
                        ✕ Delete
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>


<!-- ══════════ ADD MODAL ══════════ -->
<div class="modal-bg" id="addModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        <h2>Add Collection</h2>
        <form method="POST" action="collection.php">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="add">
            <div class="field">
                <label for="add_name">Collection Name</label>
                <input type="text" id="add_name" name="collection_name"
                       placeholder="e.g. Luxury Villas" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-submit">Add Collection</button>
            </div>
        </form>
    </div>
</div>


<!-- ══════════ EDIT MODAL ══════════ -->
<div class="modal-bg" id="editModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        <h2>Edit Collection</h2>
        <form method="POST" action="collection.php">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="field">
                <label>Current Name</label>
                <input type="text" id="edit_current" readonly>
            </div>
            <div class="field">
                <label for="edit_new">New Collection Name</label>
                <input type="text" id="edit_new" name="new_collection_name"
                       placeholder="Enter new name" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>


<!-- ══════════ DELETE MODAL ══════════ -->
<div class="modal-bg" id="deleteModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('deleteModal')">✕</button>
        <h2>Delete Collection</h2>
        <p style="color:var(--muted);font-size:.9rem;margin-bottom:1.2rem;line-height:1.6">
            Are you sure you want to delete
            <strong id="delete_name" style="color:var(--text)"></strong>?
            This action cannot be undone.
        </p>
        <form method="POST" action="collection.php">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="delete_id">
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('deleteModal')">Cancel</button>
                <button type="submit" class="btn-submit danger">Yes, Delete</button>
            </div>
        </form>
    </div>
</div>


<script>
    function openAdd() {
        document.getElementById('add_name').value = '';
        openModal('addModal');
    }

    function openEdit(id, currentName) {
        document.getElementById('edit_id').value      = id;
        document.getElementById('edit_current').value = currentName;
        document.getElementById('edit_new').value     = '';
        openModal('editModal');
    }

    function openDelete(id, name) {
        document.getElementById('delete_id').value         = id;
        document.getElementById('delete_name').textContent = name;
        openModal('deleteModal');
    }

    function openModal(id) {
        document.getElementById(id).classList.add('open');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    // Close on backdrop click
    document.querySelectorAll('.modal-bg').forEach(bg => {
        bg.addEventListener('click', e => {
            if (e.target === bg) bg.classList.remove('open');
        });
    });

    // Close on Escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-bg.open').forEach(m => m.classList.remove('open'));
        }
    });
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>