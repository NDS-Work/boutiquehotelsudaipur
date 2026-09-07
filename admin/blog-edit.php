<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/../data/blogs.php';

$currentPage = 'blogs';
$pageTitle = 'Edit Blog Post';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$blog = $id > 0 ? getBlogById($id) : null;

if (!$blog) {
    header('Location: /admin/blogs.php');
    exit;
}

$formData = $blog;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $formData = array_merge($formData, $_POST);
    $result = saveBlog($_POST, $_FILES, $id);

    if (!empty($result['success'])) {
        $success = 'Blog post updated successfully.';
        $blog = getBlogById($id);
        $formData = $blog;
    } else {
        $error = (string)($result['error'] ?? 'Unable to update blog post.');
    }
}

require_once __DIR__ . '/layout-header.php';
?>

<div class="topbar">
    <h1>Edit Blog Post</h1>
    <div class="topbar-actions">
        <a href="/blog/<?php echo urlencode($blog['slug']); ?>" target="_blank" class="btn btn-secondary">↗ View Live Article</a>
        <a href="/admin/blogs.php" class="btn btn-secondary">Back to Blogs</a>
    </div>
</div>

<div class="content">
    <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
        <?php 
        $isEdit = true;
        require __DIR__ . '/blog-form-fields.php'; 
        ?>
    </form>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
