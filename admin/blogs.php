<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/../data/blogs.php';

$currentPage = 'blogs';
$pageTitle = 'Blog Posts';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryId = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : null;
$statusFilter = isset($_GET['status']) && in_array($_GET['status'], ['published', 'draft'], true) ? $_GET['status'] : null;

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    verifyCsrf();
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        deleteBlog($id);
        header('Location: /admin/blogs.php?deleted=1');
        exit;
    }
}

// Fetch categories for filtering
$categories = getAllBlogCategories(false);

// Query posts
$allPosts = getAllBlogs(100, 0, false, $categoryId, $search !== '' ? $search : null);
if ($statusFilter !== null) {
    $allPosts = array_filter($allPosts, fn($p) => ($p['status'] ?? '') === $statusFilter);
}

// Stats
$totalCount = countBlogs(false);
$publishedCount = countBlogs(true);
$draftCount = $totalCount - $publishedCount;

require_once __DIR__ . '/layout-header.php';
?>

<div class="topbar">
    <h1>Blog Posts</h1>
    <div class="topbar-actions">
        <a href="/admin/download-content-guide.php" class="btn btn-secondary" title="Download Markdown template & content guide to share with writers">📥 Download Content Skill (.md)</a>
        <a href="/admin/blog-categories.php" class="btn btn-secondary">Manage Categories</a>
        <a href="/admin/blog-add.php" class="btn btn-primary">+ Add New Blog</a>
    </div>
</div>

<div class="content">

    <?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Blog post saved successfully.</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Blog post deleted successfully.</div>
    <?php endif; ?>

    <!-- Overview Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="card" style="padding: 16px;">
            <div style="color: var(--muted); font-size: 11px; text-transform: uppercase;">Total Articles</div>
            <div style="font-size: 24px; font-weight: bold; color: var(--accent); margin-top: 4px;"><?php echo $totalCount; ?></div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="color: var(--muted); font-size: 11px; text-transform: uppercase;">Published</div>
            <div style="font-size: 24px; font-weight: bold; color: var(--success); margin-top: 4px;"><?php echo $publishedCount; ?></div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="color: var(--muted); font-size: 11px; text-transform: uppercase;">Drafts</div>
            <div style="font-size: 24px; font-weight: bold; color: var(--muted); margin-top: 4px;"><?php echo $draftCount; ?></div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="color: var(--muted); font-size: 11px; text-transform: uppercase;">Categories</div>
            <div style="font-size: 24px; font-weight: bold; color: var(--text); margin-top: 4px;"><?php echo count($categories); ?></div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: 20px; padding: 14px 20px;">
        <form method="GET" action="/admin/blogs.php" style="display:flex; flex-wrap:wrap; gap: 12px; align-items:center;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by title, tags..." style="width: 240px; padding: 6px 12px; font-size: 12px;">
            
            <select name="category" style="width: 180px; padding: 6px 12px; font-size: 12px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo (int)$cat['id']; ?>" <?php echo $categoryId === (int)$cat['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>

            <select name="status" style="width: 140px; padding: 6px 12px; font-size: 12px;">
                <option value="">All Statuses</option>
                <option value="published" <?php echo $statusFilter === 'published' ? 'selected' : ''; ?>>Published</option>
                <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Draft</option>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            <?php if ($search !== '' || $categoryId !== null || $statusFilter !== null): ?>
            <a href="/admin/blogs.php" class="btn btn-sm btn-link" style="color:var(--muted); text-decoration:none;">Clear Filters</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Posts Table -->
    <div class="card">
        <div class="card-title">All Articles (<?php echo count($allPosts); ?>)</div>

        <table class="table" style="width:100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                    <th style="padding: 10px; width: 60px;">Image</th>
                    <th style="padding: 10px;">Title & Slug</th>
                    <th style="padding: 10px;">Category</th>
                    <th style="padding: 10px; text-align: center;">Status</th>
                    <th style="padding: 10px; text-align: center;">Views</th>
                    <th style="padding: 10px;">Date</th>
                    <th style="padding: 10px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($allPosts)): ?>
                <tr>
                    <td colspan="7" style="padding: 40px; text-align: center; color: var(--muted);">
                        No blog posts found. <a href="/admin/blog-add.php" style="color:var(--accent);">Create your first article</a>!
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($allPosts as $post): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 10px;">
                        <?php if (!empty($post['featured_image'])): ?>
                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="" style="width: 50px; height: 38px; object-fit: cover; border-radius: 3px;">
                        <?php else: ?>
                        <div style="width: 50px; height: 38px; background: var(--surface2); border: 1px solid var(--border); border-radius: 3px; display: flex; align-items:center; justify-content:center; color:var(--muted); font-size:10px;">No Pic</div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 10px;">
                        <a href="/admin/blog-edit.php?id=<?php echo (int)$post['id']; ?>" style="color: var(--text); font-weight: bold; text-decoration: none;">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                        <div style="color: var(--muted); font-size: 11px; margin-top: 2px;">
                            /blog/<?php echo htmlspecialchars($post['slug']); ?>
                        </div>
                    </td>
                    <td style="padding: 10px;">
                        <?php if (!empty($post['category_name'])): ?>
                        <span style="background: var(--surface2); color: var(--accent); padding: 2px 8px; border-radius: 10px; font-size: 11px;">
                            <?php echo htmlspecialchars($post['category_name']); ?>
                        </span>
                        <?php else: ?>
                        <span style="color: var(--muted); font-size: 11px;">Uncategorized</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <?php if (($post['status'] ?? '') === 'published'): ?>
                        <span style="color: var(--success); font-weight: 500; font-size: 11px;">● Published</span>
                        <?php else: ?>
                        <span style="color: var(--muted); font-size: 11px;">○ Draft</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 10px; text-align: center; color: var(--muted);">
                        <?php echo number_format((int)($post['views'] ?? 0)); ?>
                    </td>
                    <td style="padding: 10px; color: var(--muted); font-size: 11px;">
                        <?php echo date('M j, Y', strtotime((string)$post['published_at'])); ?>
                    </td>
                    <td style="padding: 10px; text-align: right;">
                        <a href="/admin/blog-edit.php?id=<?php echo (int)$post['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                        <a href="/blog/<?php echo urlencode($post['slug']); ?>" target="_blank" class="btn btn-sm btn-secondary" title="View Public Post">↗</a>
                        <form method="POST" action="/admin/blogs.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this blog post?');">
                            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$post['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">✕</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
