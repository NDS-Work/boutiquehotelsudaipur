<?php
require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/../data/venues-admin.php';

$currentPage = 'venue-add';
$pageTitle = 'Add Venue';

$formData = adminVenueDefaults();
$error = '';
$success = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $formData = array_merge($formData, $_POST);
    $result = adminSaveVenue($_POST, $_FILES, null);
    if (!empty($result['success'])) {
        header('Location: /admin/venue-edit.php?slug=' . urlencode((string) $result['slug']) . '&saved=1');
        exit;
    }
    $error = (string) ($result['error'] ?? 'Unable to save venue.');
}

require_once __DIR__ . '/layout-header.php';
?>

<div class="topbar">
    <h1>Add New Venue</h1>
    <div class="topbar-actions">
        <a href="/admin/venues.php" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="content">
    <?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
        <?php require __DIR__ . '/venue-form-fields.php'; ?>
        <div style="display:flex;gap:12px;margin-bottom:40px;">
            <button type="submit" class="btn btn-primary">Save Venue</button>
            <a href="/admin/venues.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
