<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/../data/blogs.php';

$currentPage = 'blog-add';
$pageTitle = 'Add Blog Post';

$formData = [
    'title' => '',
    'slug' => '',
    'category_id' => '',
    'excerpt' => '',
    'content' => '',
    'featured_image' => '',
    'author' => 'Boutique Hotels Editorial Team',
    'tags' => '',
    'status' => 'published',
    'meta_title' => '',
    'meta_description' => '',
    'meta_keywords' => '',
    'canonical_url' => '',
    'og_image' => '',
    'schema_faq_json' => '',
    'published_at' => date('Y-m-d H:i')
];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $formData = array_merge($formData, $_POST);
    $result = saveBlog($_POST, $_FILES, null);

    if (!empty($result['success'])) {
        header('Location: /admin/blogs.php?saved=1');
        exit;
    }
    $error = (string)($result['error'] ?? 'Unable to save blog post.');
}

require_once __DIR__ . '/layout-header.php';
?>

<div class="topbar">
    <h1>Create New Blog Post</h1>
    <div class="topbar-actions">
        <a href="/admin/blogs.php" class="btn btn-secondary">Back to Blogs</a>
    </div>
</div>

<div class="content">
    <?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
        <?php 
        $isEdit = false;
        require __DIR__ . '/blog-form-fields.php'; 
        ?>
    </form>
</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
