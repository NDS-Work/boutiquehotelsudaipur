<?php

declare(strict_types=1);

require_once __DIR__ . '/venues.php';

function _getBlogDb(): PDO {
    static $blogPdo = null;
    if ($blogPdo instanceof PDO) {
        return $blogPdo;
    }

    $dbPath = __DIR__ . '/new.sqlite.db';
    $blogPdo = new PDO('sqlite:' . $dbPath);
    $blogPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $blogPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $blogPdo->setAttribute(PDO::ATTR_TIMEOUT, 30);
    $blogPdo->exec('PRAGMA busy_timeout = 30000');
    $blogPdo->exec('PRAGMA journal_mode = WAL');
    $blogPdo->exec('PRAGMA synchronous = NORMAL');

    _ensureBlogTablesExist($blogPdo);
    return $blogPdo;
}

function _ensureBlogTablesExist(PDO $db): void {
    static $checked = false;
    if ($checked) {
        return;
    }

    $db->exec("
        CREATE TABLE IF NOT EXISTS link_blog_categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            description TEXT,
            featured_image TEXT,
            meta_title TEXT,
            meta_description TEXT,
            canonical_url TEXT,
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS link_blogs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER,
            title TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            excerpt TEXT,
            content TEXT NOT NULL,
            featured_image TEXT,
            author TEXT DEFAULT 'Boutique Hotels Editorial Team',
            tags TEXT,
            status TEXT DEFAULT 'published',
            meta_title TEXT,
            meta_description TEXT,
            meta_keywords TEXT,
            canonical_url TEXT,
            og_image TEXT,
            schema_faq_json TEXT,
            views INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES link_blog_categories(id) ON DELETE SET NULL
        );

        CREATE INDEX IF NOT EXISTS idx_link_blogs_slug ON link_blogs (slug);
        CREATE INDEX IF NOT EXISTS idx_link_blogs_status ON link_blogs (status);
        CREATE INDEX IF NOT EXISTS idx_link_blogs_cat ON link_blogs (category_id);
        CREATE INDEX IF NOT EXISTS idx_link_blog_cat_slug ON link_blog_categories (slug);
    ");

    $checked = true;
}

// ─────────────────────────────────────────────────────────────
// Category Functions
// ─────────────────────────────────────────────────────────────

function getAllBlogCategories(bool $withCount = true): array {
    $db = _getBlogDb();
    if ($withCount) {
        $stmt = $db->query("
            SELECT c.*, COUNT(b.id) as post_count
            FROM link_blog_categories c
            LEFT JOIN link_blogs b ON b.category_id = c.id AND b.status = 'published'
            GROUP BY c.id
            ORDER BY c.sort_order ASC, c.name ASC
        ");
    } else {
        $stmt = $db->query("SELECT * FROM link_blog_categories ORDER BY sort_order ASC, name ASC");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBlogCategoryById(int $id): ?array {
    $db = _getBlogDb();
    $stmt = $db->prepare("SELECT * FROM link_blog_categories WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);
    return $cat ?: null;
}

function getBlogCategoryBySlug(string $slug): ?array {
    $db = _getBlogDb();
    $stmt = $db->prepare("SELECT * FROM link_blog_categories WHERE slug = :slug LIMIT 1");
    $stmt->execute([':slug' => $slug]);
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);
    return $cat ?: null;
}

function saveBlogCategory(array $data, ?int $id = null): array {
    $db = _getBlogDb();
    $name = trim((string)($data['name'] ?? ''));
    if ($name === '') {
        return ['success' => false, 'error' => 'Category name is required.'];
    }

    $slug = trim((string)($data['slug'] ?? ''));
    if ($slug === '') {
        $slug = slugifyBlogText($name);
    } else {
        $slug = slugifyBlogText($slug);
    }

    // Check slug uniqueness
    $slugCheckSql = "SELECT id FROM link_blog_categories WHERE slug = :slug";
    $params = [':slug' => $slug];
    if ($id) {
        $slugCheckSql .= " AND id != :id";
        $params[':id'] = $id;
    }
    $stmt = $db->prepare($slugCheckSql);
    $stmt->execute($params);
    if ($stmt->fetch()) {
        $slug = $slug . '-' . time();
    }

    $description = trim((string)($data['description'] ?? ''));
    $featuredImage = trim((string)($data['featured_image'] ?? ''));
    $metaTitle = trim((string)($data['meta_title'] ?? ''));
    $metaDescription = trim((string)($data['meta_description'] ?? ''));
    $canonicalUrl = trim((string)($data['canonical_url'] ?? ''));
    $sortOrder = (int)($data['sort_order'] ?? 0);

    if ($id) {
        $stmt = $db->prepare("
            UPDATE link_blog_categories
            SET name = :name, slug = :slug, description = :description,
                featured_image = :featured_image, meta_title = :meta_title,
                meta_description = :meta_description, canonical_url = :canonical_url,
                sort_order = :sort_order, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $stmt->execute([
            ':name' => $name,
            ':slug' => $slug,
            ':description' => $description,
            ':featured_image' => $featuredImage,
            ':meta_title' => $metaTitle,
            ':meta_description' => $metaDescription,
            ':canonical_url' => $canonicalUrl,
            ':sort_order' => $sortOrder,
            ':id' => $id,
        ]);
        return ['success' => true, 'id' => $id, 'slug' => $slug];
    } else {
        $stmt = $db->prepare("
            INSERT INTO link_blog_categories (
                name, slug, description, featured_image,
                meta_title, meta_description, canonical_url, sort_order
            ) VALUES (
                :name, :slug, :description, :featured_image,
                :meta_title, :meta_description, :canonical_url, :sort_order
            )
        ");
        $stmt->execute([
            ':name' => $name,
            ':slug' => $slug,
            ':description' => $description,
            ':featured_image' => $featuredImage,
            ':meta_title' => $metaTitle,
            ':meta_description' => $metaDescription,
            ':canonical_url' => $canonicalUrl,
            ':sort_order' => $sortOrder,
        ]);
        return ['success' => true, 'id' => (int)$db->lastInsertId(), 'slug' => $slug];
    }
}

function deleteBlogCategory(int $id): bool {
    $db = _getBlogDb();
    $stmt = $db->prepare("DELETE FROM link_blog_categories WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

// ─────────────────────────────────────────────────────────────
// Blog Post Functions
// ─────────────────────────────────────────────────────────────

function getAllBlogs(
    int $limit = 20,
    int $offset = 0,
    bool $onlyPublished = true,
    ?int $categoryId = null,
    ?string $search = null
): array {
    $db = _getBlogDb();
    $conditions = [];
    $params = [];

    if ($onlyPublished) {
        $conditions[] = "b.status = 'published'";
    }

    if ($categoryId !== null && $categoryId > 0) {
        $conditions[] = "b.category_id = :cat_id";
        $params[':cat_id'] = $categoryId;
    }

    if ($search !== null && trim($search) !== '') {
        $conditions[] = "(b.title LIKE :search OR b.content LIKE :search OR b.tags LIKE :search)";
        $params[':search'] = '%' . trim($search) . '%';
    }

    $whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $sql = "
        SELECT b.*, c.name as category_name, c.slug as category_slug
        FROM link_blogs b
        LEFT JOIN link_blog_categories c ON c.id = b.category_id
        {$whereClause}
        ORDER BY b.published_at DESC, b.id DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countBlogs(bool $onlyPublished = true, ?int $categoryId = null, ?string $search = null): int {
    $db = _getBlogDb();
    $conditions = [];
    $params = [];

    if ($onlyPublished) {
        $conditions[] = "status = 'published'";
    }
    if ($categoryId !== null && $categoryId > 0) {
        $conditions[] = "category_id = :cat_id";
        $params[':cat_id'] = $categoryId;
    }
    if ($search !== null && trim($search) !== '') {
        $conditions[] = "(title LIKE :search OR content LIKE :search OR tags LIKE :search)";
        $params[':search'] = '%' . trim($search) . '%';
    }

    $whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';
    $sql = "SELECT COUNT(*) FROM link_blogs {$whereClause}";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function getBlogById(int $id): ?array {
    $db = _getBlogDb();
    $stmt = $db->prepare("
        SELECT b.*, c.name as category_name, c.slug as category_slug
        FROM link_blogs b
        LEFT JOIN link_blog_categories c ON c.id = b.category_id
        WHERE b.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);
    return $blog ?: null;
}

function getBlogBySlug(string $slug, bool $onlyPublished = true): ?array {
    $db = _getBlogDb();
    $sql = "
        SELECT b.*, c.name as category_name, c.slug as category_slug
        FROM link_blogs b
        LEFT JOIN link_blog_categories c ON c.id = b.category_id
        WHERE b.slug = :slug
    ";
    if ($onlyPublished) {
        $sql .= " AND b.status = 'published'";
    }
    $sql .= " LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute([':slug' => $slug]);
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);
    return $blog ?: null;
}

function getRecentBlogs(int $limit = 5, ?int $excludeId = null): array {
    $db = _getBlogDb();
    $sql = "
        SELECT b.id, b.title, b.slug, b.featured_image, b.published_at, b.excerpt, c.name as category_name, c.slug as category_slug
        FROM link_blogs b
        LEFT JOIN link_blog_categories c ON c.id = b.category_id
        WHERE b.status = 'published'
    ";
    $params = [];
    if ($excludeId !== null) {
        $sql .= " AND b.id != :exclude_id";
        $params[':exclude_id'] = $excludeId;
    }
    $sql .= " ORDER BY b.published_at DESC LIMIT :limit";

    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRelatedBlogs(int $categoryId, int $limit = 3, ?int $excludeId = null): array {
    $db = _getBlogDb();
    $sql = "
        SELECT b.id, b.title, b.slug, b.featured_image, b.published_at, b.excerpt, c.name as category_name, c.slug as category_slug
        FROM link_blogs b
        LEFT JOIN link_blog_categories c ON c.id = b.category_id
        WHERE b.status = 'published' AND b.category_id = :cat_id
    ";
    $params = [':cat_id' => $categoryId];
    if ($excludeId !== null) {
        $sql .= " AND b.id != :exclude_id";
        $params[':exclude_id'] = $excludeId;
    }
    $sql .= " ORDER BY b.published_at DESC LIMIT :limit";

    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) < $limit) {
        $more = getRecentBlogs($limit - count($results), $excludeId);
        $existingIds = array_column($results, 'id');
        foreach ($more as $item) {
            if (!in_array($item['id'], $existingIds, true)) {
                $results[] = $item;
            }
        }
    }

    return $results;
}

function incrementBlogViews(int $id): void {
    try {
        $db = _getBlogDb();
        $stmt = $db->prepare("UPDATE link_blogs SET views = views + 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } catch (Throwable $e) {
        // Silently skip view increment failures
    }
}

function saveBlog(array $post, array $files = [], ?int $id = null): array {
    $db = _getBlogDb();

    $title = trim((string)($post['title'] ?? ''));
    if ($title === '') {
        return ['success' => false, 'error' => 'Blog title is required.'];
    }

    $content = trim((string)($post['content'] ?? ''));
    if ($content === '') {
        return ['success' => false, 'error' => 'Blog body content cannot be empty.'];
    }

    // Clean up empty paragraphs with <br>, extra spaces, or empty lines generated by rich text editors
    $content = preg_replace('/<p[^>]*>(\s*|<br\s*\/?>|&nbsp;)*<\/p>/i', '', $content);
    $content = trim($content);

    $slug = trim((string)($post['slug'] ?? ''));
    if ($slug === '') {
        $slug = slugifyBlogText($title);
    } else {
        $slug = slugifyBlogText($slug);
    }

    // Check slug uniqueness
    $slugCheckSql = "SELECT id FROM link_blogs WHERE slug = :slug";
    $params = [':slug' => $slug];
    if ($id) {
        $slugCheckSql .= " AND id != :id";
        $params[':id'] = $id;
    }
    $stmt = $db->prepare($slugCheckSql);
    $stmt->execute($params);
    if ($stmt->fetch()) {
        $slug = $slug . '-' . time();
    }

    $categoryId = !empty($post['category_id']) ? (int)$post['category_id'] : null;
    $excerpt = trim((string)($post['excerpt'] ?? ''));
    if ($excerpt === '') {
        // Automatically generate excerpt from HTML content
        $cleanText = strip_tags($content);
        $excerpt = mb_substr($cleanText, 0, 160) . (mb_strlen($cleanText) > 160 ? '...' : '');
    }

    $author = trim((string)($post['author'] ?? 'Boutique Hotels Editorial Team'));
    $tags = trim((string)($post['tags'] ?? ''));
    $status = ($post['status'] ?? 'published') === 'draft' ? 'draft' : 'published';

    $metaTitle = trim((string)($post['meta_title'] ?? ''));
    if ($metaTitle === '') {
        $metaTitle = $title . ' | Boutique Hotels in Udaipur';
    }

    $metaDescription = trim((string)($post['meta_description'] ?? ''));
    if ($metaDescription === '') {
        $metaDescription = $excerpt;
    }

    $metaKeywords = trim((string)($post['meta_keywords'] ?? ''));
    $canonicalUrl = trim((string)($post['canonical_url'] ?? ''));
    $ogImage = trim((string)($post['og_image'] ?? ''));
    $schemaFaqJson = trim((string)($post['schema_faq_json'] ?? ''));
    $featuredImage = trim((string)($post['featured_image'] ?? ''));

    // Handle uploaded featured image if provided
    if (!empty($files['featured_image_file']['name'])) {
        $uploaded = _handleBlogImageUpload($files['featured_image_file']);
        if ($uploaded['success']) {
            $featuredImage = $uploaded['url'];
        }
    }

    if ($ogImage === '' && $featuredImage !== '') {
        $ogImage = $featuredImage;
    }

    $publishedAt = !empty($post['published_at']) ? date('Y-m-d H:i:s', strtotime((string)$post['published_at'])) : date('Y-m-d H:i:s');

    if ($id) {
        $stmt = $db->prepare("
            UPDATE link_blogs
            SET category_id = :category_id,
                title = :title,
                slug = :slug,
                excerpt = :excerpt,
                content = :content,
                featured_image = :featured_image,
                author = :author,
                tags = :tags,
                status = :status,
                meta_title = :meta_title,
                meta_description = :meta_description,
                meta_keywords = :meta_keywords,
                canonical_url = :canonical_url,
                og_image = :og_image,
                schema_faq_json = :schema_faq_json,
                published_at = :published_at,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $stmt->execute([
            ':category_id' => $categoryId,
            ':title' => $title,
            ':slug' => $slug,
            ':excerpt' => $excerpt,
            ':content' => $content,
            ':featured_image' => $featuredImage,
            ':author' => $author,
            ':tags' => $tags,
            ':status' => $status,
            ':meta_title' => $metaTitle,
            ':meta_description' => $metaDescription,
            ':meta_keywords' => $metaKeywords,
            ':canonical_url' => $canonicalUrl,
            ':og_image' => $ogImage,
            ':schema_faq_json' => $schemaFaqJson,
            ':published_at' => $publishedAt,
            ':id' => $id,
        ]);
        return ['success' => true, 'id' => $id, 'slug' => $slug];
    } else {
        $stmt = $db->prepare("
            INSERT INTO link_blogs (
                category_id, title, slug, excerpt, content, featured_image,
                author, tags, status, meta_title, meta_description,
                meta_keywords, canonical_url, og_image, schema_faq_json,
                published_at
            ) VALUES (
                :category_id, :title, :slug, :excerpt, :content, :featured_image,
                :author, :tags, :status, :meta_title, :meta_description,
                :meta_keywords, :canonical_url, :og_image, :schema_faq_json,
                :published_at
            )
        ");
        $stmt->execute([
            ':category_id' => $categoryId,
            ':title' => $title,
            ':slug' => $slug,
            ':excerpt' => $excerpt,
            ':content' => $content,
            ':featured_image' => $featuredImage,
            ':author' => $author,
            ':tags' => $tags,
            ':status' => $status,
            ':meta_title' => $metaTitle,
            ':meta_description' => $metaDescription,
            ':meta_keywords' => $metaKeywords,
            ':canonical_url' => $canonicalUrl,
            ':og_image' => $ogImage,
            ':schema_faq_json' => $schemaFaqJson,
            ':published_at' => $publishedAt,
        ]);
        return ['success' => true, 'id' => (int)$db->lastInsertId(), 'slug' => $slug];
    }
}

function deleteBlog(int $id): bool {
    $db = _getBlogDb();
    $stmt = $db->prepare("DELETE FROM link_blogs WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

// ─────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────

function slugifyBlogText(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'n-a');
}

function _handleBlogImageUpload(array $file): array {
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $maxBytes = 8 * 1024 * 1024; // 8MB

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error code: ' . ($file['error'] ?? 'unknown')];
    }

    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'error' => 'Image size exceeds maximum limit of 8MB.'];
    }

    $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return ['success' => false, 'error' => 'Invalid image format. Allowed: JPG, PNG, WEBP, GIF.'];
    }

    $uploadDir = dirname(__DIR__) . '/assets/uploads/blogs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'blog_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => false, 'error' => 'Failed to save uploaded file to destination.'];
    }

    return ['success' => true, 'url' => '/assets/uploads/blogs/' . $filename];
}
