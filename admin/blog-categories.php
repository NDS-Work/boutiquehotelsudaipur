<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/../data/blogs.php';

$currentPage = 'blog-categories';
$pageTitle = 'Blog Categories';

$error = '';
$success = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $res = saveBlogCategory($_POST, $id);
        if ($res['success']) {
            $success = $id ? 'Category updated successfully.' : 'Category created successfully.';
        } else {
            $error = $res['error'] ?? 'Failed to save category.';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            deleteBlogCategory($id);
            $success = 'Category deleted successfully.';
        }
    }
}

// Edit mode
$editCategory = null;
if (isset($_GET['edit'])) {
    $editCategory = getBlogCategoryById((int)$_GET['edit']);
}

$categories = getAllBlogCategories(true);

require_once __DIR__ . '/layout-header.php';
?>

<div class="topbar">
    <h1>Blog Categories</h1>
    <div class="topbar-actions">
        <a href="/admin/blogs.php" class="btn btn-secondary">← Back to Blogs</a>
        <a href="/blogs" target="_blank" class="btn btn-secondary">↗ View Blog Front</a>
    </div>
</div>

<div class="content">

    <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">
        
        <!-- Add / Edit Form -->
        <div class="card" style="position: sticky; top: 80px;">
            <div class="card-title"><?php echo $editCategory ? 'Edit Category' : 'Add New Category'; ?></div>
            <form method="POST" action="/admin/blog-categories.php">
                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                <input type="hidden" name="action" value="save">
                <?php if ($editCategory): ?>
                <input type="hidden" name="id" value="<?php echo (int)$editCategory['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Heritage Hotels" value="<?php echo htmlspecialchars($editCategory['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Slug (URL key)</label>
                    <input type="text" name="slug" placeholder="leave blank to auto-generate" value="<?php echo htmlspecialchars($editCategory['slug'] ?? ''); ?>">
                    <div class="form-hint">Public URL: /blogs/category/[slug]</div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Brief summary of what this category covers..."><?php echo htmlspecialchars($editCategory['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Featured Image URL</label>
                    <input type="text" name="featured_image" placeholder="https://... or /assets/..." value="<?php echo htmlspecialchars($editCategory['featured_image'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" value="<?php echo (int)($editCategory['sort_order'] ?? 0); ?>">
                </div>

                <details style="margin-bottom: 20px; background: var(--surface2); padding: 12px; border: 1px solid var(--border);">
                    <summary style="cursor: pointer; font-weight: 500; color: var(--accent);">SEO Metadata Settings</summary>
                    <div style="margin-top: 12px;">
                        <div class="form-group">
                            <label>Meta Title</label>
                            <input type="text" name="meta_title" placeholder="Search engine title..." value="<?php echo htmlspecialchars($editCategory['meta_title'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Meta Description</label>
                            <textarea name="meta_description" rows="2" placeholder="Search engine description (150-160 chars)..."><?php echo htmlspecialchars($editCategory['meta_description'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Canonical URL</label>
                            <input type="text" name="canonical_url" placeholder="https://boutiquehotelsudaipur.com/blogs/category/..." value="<?php echo htmlspecialchars($editCategory['canonical_url'] ?? ''); ?>">
                        </div>
                    </div>
                </details>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary"><?php echo $editCategory ? 'Update Category' : 'Save Category'; ?></button>
                    <?php if ($editCategory): ?>
                    <a href="/admin/blog-categories.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Categories Table -->
        <div class="card">
            <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
                <span>All Categories (<?php echo count($categories); ?>)</span>
            </div>

            <table class="table" style="width:100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                        <th style="padding: 10px;">Order</th>
                        <th style="padding: 10px;">Name</th>
                        <th style="padding: 10px;">Slug</th>
                        <th style="padding: 10px; text-align: center;">Posts</th>
                        <th style="padding: 10px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: var(--muted);">No categories created yet.</td>
                    </tr>
                    <?php endif; ?>

                    <?php foreach ($categories as $cat): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 10px; color: var(--muted);"><?php echo (int)$cat['sort_order']; ?></td>
                        <td style="padding: 10px;">
                            <strong style="color: var(--text);"><?php echo htmlspecialchars($cat['name']); ?></strong>
                            <?php if (!empty($cat['description'])): ?>
                            <div style="color: var(--muted); font-size: 11px; margin-top: 3px; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($cat['description']); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 10px; font-family: monospace; color: var(--muted); font-size: 11px;">
                            /blogs/category/<?php echo htmlspecialchars($cat['slug']); ?>
                        </td>
                        <td style="padding: 10px; text-align: center;">
                            <span style="background: var(--surface2); padding: 3px 8px; border-radius: 12px; font-size: 11px;">
                                <?php echo (int)($cat['post_count'] ?? 0); ?>
                            </span>
                        </td>
                        <td style="padding: 10px; text-align: right;">
                            <a href="/admin/blog-categories.php?edit=<?php echo (int)$cat['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <a href="/blogs/category/<?php echo urlencode($cat['slug']); ?>" target="_blank" class="btn btn-sm btn-secondary" title="View Public Page">↗</a>
                            <form method="POST" action="/admin/blog-categories.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int)$cat['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">✕</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
