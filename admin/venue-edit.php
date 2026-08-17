<?php
require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/../data/venues-admin.php';
require_once __DIR__ . '/../data/venues.php';

$currentPage = 'venues';
$pageTitle = 'Edit Venue';

$slug = (string) ($_GET['slug'] ?? $_GET['id'] ?? '');
$formData = $slug !== '' ? adminLoadVenueFormData($slug) : null;
$error = '';

if ($formData === null) {
    echo '<div style="padding: 40px; text-align: center;"><h2 style="color: #e06363;">Venue not found</h2><p><a href="/admin/venues.php">Back to venues</a></p></div>';
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $formData = array_merge($formData, $_POST);
    $result = adminSaveVenue($_POST, $_FILES, (int) $formData['id']);
    if (!empty($result['success'])) {
        header('Location: /admin/venue-edit.php?slug=' . urlencode((string) $result['slug']) . '&saved=1');
        exit;
    }
    $error = (string) ($result['error'] ?? 'Unable to save venue.');
}

$venue = getVenueBySlug((string) $formData['slug']);

require_once __DIR__ . '/layout-header.php';
?>

<div class="topbar">
    <h1>Edit: <?php echo htmlspecialchars($formData['name']); ?></h1>
    <div class="topbar-actions">
        <a href="/venues/<?php echo htmlspecialchars((string) $formData['slug']); ?>" target="_blank" class="btn btn-secondary">View on Site</a>
        <a href="/admin/venues.php" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="content">
    <?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Venue saved successfully.</div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- <?php if ($venue && !empty($venue['images'])): ?>
    <div class="card">
        <div class="card-title">Current Images</div>
        <div class="image-preview-grid">
            <?php foreach ($venue['imageGallery'] as $image): ?>
            <div class="image-preview-item">
                <img src="<?php echo htmlspecialchars($image['url']); ?>" alt="">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?> -->

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
        <?php require __DIR__ . '/venue-form-fields.php'; ?>
        <div style="display:flex;gap:12px;margin-bottom:40px;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="/admin/venues.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
