<?php

declare(strict_types=1);

require_once __DIR__ . '/data/blogs.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$category = getBlogCategoryBySlug($slug);

if (!$category) {
    http_response_code(404);
    $metaTitle = 'Category Not Found | Boutique Hotels in Udaipur';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container py-5 text-center text-white" style="margin-top:100px;"><h2>Category Not Found</h2><p>The category you are looking for does not exist.</p><a href="/blogs" class="btn btn-primary">Browse All Blogs</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

$categoryId = (int)$category['id'];
$totalPosts = countBlogs(true, $categoryId);
$posts = getAllBlogs($perPage, $offset, true, $categoryId);
$totalPages = ceil($totalPosts / $perPage);

// SEO Metadata
$metaTitle = !empty($category['meta_title']) ? $category['meta_title'] : ($category['name'] . ' — Boutique Hotels in Udaipur Guides');
$metaDescription = !empty($category['meta_description']) ? $category['meta_description'] : ($category['description'] ?: 'Explore our curated guides for ' . $category['name'] . ' in Udaipur.');
$canonicalUrl = !empty($category['canonical_url']) ? $category['canonical_url'] : ('https://boutiquehotelsudaipur.com/blogs/category/' . urlencode($category['slug']));

$schemaJson = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'headline' => $metaTitle,
    'description' => $metaDescription,
    'url' => $canonicalUrl,
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'Boutique Hotels In Udaipur',
        'url' => 'https://boutiquehotelsudaipur.com'
    ]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

require_once __DIR__ . '/includes/header.php';
?>

<style>
:root {
    --brand-primary: #c9913d;
}
.cat-hero {
    background: #4b1111;
    padding: 130px 0 50px;
    color: #fff;
    text-align: center;
    border-bottom: 1px solid #c9913d;
}
.cat-hero h1 {
    font-family: 'Cinzel', serif;
    font-size: 2.6rem;
    color: #f7e6c4;
    margin-bottom: 12px;
}
.cat-hero p {
    font-size: 1.1rem;
    color: #dfddd6;
    max-width: 680px;
    line-height: 1.6;
}
.blog-card {
    background: #ffffff;
    border: 1px solid rgba(201,145,61,0.2);
    border-radius: 6px;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 15px rgba(0,0,0,0.04);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.blog-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 24px rgba(0,0,0,0.08);
    border-color: var(--brand-primary, #c9913d);
}
.blog-card-img {
    height: 220px;
    width: 100%;
    object-fit: cover;
}
.blog-card-body {
    padding: 24px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.blog-card-title {
    font-family: 'Cinzel', serif;
    font-size: 1.25rem;
    color: #3b332a;
    line-height: 1.35;
    margin-bottom: 12px;
    text-decoration: none;
    font-weight: 600;
}
.blog-card-title:hover {
    color: var(--brand-primary, #c9913d);
}
.blog-card-excerpt {
    color: #665a4c;
    font-size: 0.94rem;
    line-height: 1.65;
    margin-bottom: 18px;
    flex: 1;
}
.blog-card-meta {
    border-top: 1px solid #f0ebe1;
    padding-top: 14px;
    font-size: 0.82rem;
    color: #8c7d72;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.read-link {
    color: var(--brand-primary, #c9913d);
    text-decoration: none;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.read-link:hover {
    color: #a67c52;
    text-decoration: underline;
}
</style>

<!-- Hero Section -->
<section class="cat-hero">
    <div class="container">
        <h1><?php echo htmlspecialchars($category['name']); ?></h1>
        <?php if (!empty($category['description'])): ?>
        <p style="margin: 0 auto;"><?php echo htmlspecialchars($category['description']); ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- Blog Cards List -->
<section style="background-color: #f8f5f0; padding: 60px 0 80px; min-height: 55vh;">
    <div class="container">

        <?php if (empty($posts)): ?>
        <div class="text-center py-5" style="color: #63615b;">
            <i class="bi bi-journal-x" style="font-size: 3rem; color: #4b1111;"></i>
            <h3 class="mt-3" style="color: #4b1111; font-family: 'Cinzel', serif; font-weight: 600;">No articles published in this category yet</h3>
            <p>Our team is currently writing stories for <?php echo htmlspecialchars($category['name']); ?>. Check back shortly!</p>
            <a href="/blogs" class="btn mt-2" style="background-color: #4b1111; color: #f7e6c4; border-radius: 4px; padding: 8px 20px;">← View All Blogs</a>
        </div>
        <?php else: ?>

        <div class="row g-4">
            <?php foreach ($posts as $post): ?>
            <div class="col-lg-4 col-md-6">
                <article class="blog-card">
                    <a href="/blog/<?php echo urlencode($post['slug']); ?>">
                        <?php if (!empty($post['featured_image'])): ?>
                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-card-img" loading="lazy">
                        <?php else: ?>
                        <div class="blog-card-img d-flex align-items-center justify-content-center" style="background:#221414; color:#785a5a;">
                            <i class="bi bi-image" style="font-size:2.5rem;"></i>
                        </div>
                        <?php endif; ?>
                    </a>
                    <div class="blog-card-body">
                        <a href="/blog/<?php echo urlencode($post['slug']); ?>" class="blog-card-title">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>

                        <p class="blog-card-excerpt">
                            <?php echo htmlspecialchars($post['excerpt']); ?>
                        </p>

                        <div class="blog-card-meta">
                            <span><i class="bi bi-calendar3"></i> <?php echo date('M j, Y', strtotime((string)$post['published_at'])); ?></span>
                            <a href="/blog/<?php echo urlencode($post['slug']); ?>" class="read-link">
                                Read Guide <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center mt-5">
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
