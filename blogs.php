<?php

declare(strict_types=1);

require_once __DIR__ . '/data/blogs.php';

// Pagination & Filtering
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

$search = isset($_GET['q']) ? trim($_GET['q']) : null;
$categorySlug = isset($_GET['category']) ? trim($_GET['category']) : null;

$categories = getAllBlogCategories(true);
$currentCategory = null;
$selectedCategoryId = null;

if ($categorySlug) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $categorySlug) {
            $currentCategory = $cat;
            $selectedCategoryId = (int)$cat['id'];
            break;
        }
    }
}

$totalAllPosts = countBlogs(true, null, null);
$totalPosts = countBlogs(true, $selectedCategoryId, $search);
$posts = getAllBlogs($perPage, $offset, true, $selectedCategoryId, $search);
$totalPages = ceil($totalPosts / $perPage);

// SEO Metadata
$metaTitle = $currentCategory ? ($currentCategory['name'] . ' Blogs | Boutique Hotels in Udaipur') : 'Blogs & Stories | Boutique Hotels in Udaipur';
$metaDescription = $currentCategory && !empty($currentCategory['description']) ? $currentCategory['description'] : 'Read our latest blogs, articles, and stories about boutique hotels, heritage stays, and experiences in Udaipur.';
$canonicalUrl = $categorySlug ? ('https://boutiquehotelsudaipur.com/blogs?category=' . urlencode($categorySlug)) : 'https://boutiquehotelsudaipur.com/blogs';

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
.blog-hero {
    background: #4b1111;
    padding: 130px 0 50px;
    color: #fff;
    text-align: center;
    border-bottom: 1px solid #c9913d;
}
.blog-hero h1 {
    font-family: 'Cinzel', serif;
    font-size: 2.6rem;
    font-weight: 700;
    margin-bottom: 14px;
    color: #f7e6c4;
}
.blog-hero p {
    font-size: 1.1rem;
    color: #dfddd6;
    max-width: 720px;
    margin: 0 auto 28px;
    font-family: 'Lato', sans-serif;
}
.cat-pill {
    display: inline-block;
    padding: 8px 18px;
    background: rgba(255,255,255,0.08);
    color: #f7e6c4;
    border: 1px solid rgba(247,230,196,0.3);
    border-radius: 30px;
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 500;
    transition: all 0.2s ease;
}
.cat-pill:hover, .cat-pill.active {
    background: var(--brand-primary, #c9913d);
    color: #ffffff;
    border-color: var(--brand-primary, #c9913d);
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
.blog-card-cat {
    font-size: 0.78rem;
    color: #a67c52;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
    margin-bottom: 8px;
    text-decoration: none;
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
<section class="blog-hero">
    <div class="container">
        <span class="text-uppercase" style="letter-spacing: 2px; font-size: 0.8rem; color: #f7e6c4;">Stories & Insights</span>
        <h1>Our Blogs</h1>
        <p>Explore articles, stories, and insights about the finest boutique hotels, heritage havelis, and stays in Udaipur.</p>

        <!-- Category Tabs -->
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="/blogs" class="cat-pill <?php echo !$categorySlug ? 'active' : ''; ?>">
                All Stories (<?php echo $totalAllPosts; ?>)
            </a>
            <?php foreach ($categories as $cat): ?>
            <?php $isActive = ($categorySlug === $cat['slug']); ?>
            <a href="/blogs?category=<?php echo urlencode($cat['slug']); ?>" class="cat-pill <?php echo $isActive ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($cat['name']); ?> (<?php echo (int)($cat['post_count'] ?? 0); ?>)
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Blog Listing Grid -->
<section style="background-color: #f8f5f0; padding: 60px 0 80px; min-height: 60vh;">
    <div class="container">
        
        <?php if ($search): ?>
        <div class="mb-4 text-white">
            <h4>Search results for "<strong><?php echo htmlspecialchars($search); ?></strong>" (<?php echo $totalPosts; ?> found):</h4>
            <a href="/blogs" style="color: #e8a87c; font-size: 0.9rem;">← Clear search</a>
        </div>
        <?php endif; ?>

        <?php if (empty($posts)): ?>
        <div class="text-center py-5" style="color: #63615b;">
            <i class="bi bi-journal-x" style="font-size: 3rem; color: #4b1111;"></i>
            <h3 class="mt-3" style="color: #4b1111; font-family: 'Cinzel', serif; font-weight: 600;">No articles published yet</h3>
            <p>Our editorial team is crafting new boutique hotel guides. Check back soon!</p>
            <a href="/" class="btn mt-2" style="background-color: #4b1111; color: #f7e6c4; border-radius: 4px; padding: 8px 20px;">Back to Homepage</a>
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
                        <?php if (!empty($post['category_name'])): ?>
                        <a href="/blogs/category/<?php echo urlencode($post['category_slug']); ?>" class="blog-card-cat">
                            <?php echo htmlspecialchars($post['category_name']); ?>
                        </a>
                        <?php endif; ?>

                        <a href="/blog/<?php echo urlencode($post['slug']); ?>" class="blog-card-title">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>

                        <p class="blog-card-excerpt">
                            <?php echo htmlspecialchars($post['excerpt']); ?>
                        </p>

                        <div class="blog-card-meta">
                            <span><i class="bi bi-calendar3"></i> <?php echo date('M j, Y', strtotime((string)$post['published_at'])); ?></span>
                            <a href="/blog/<?php echo urlencode($post['slug']); ?>" class="read-link">
                                Read Blog <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <?php
        $pageUrl = function($p) use ($categorySlug, $search) {
            $params = ['page' => $p];
            if ($categorySlug) $params['category'] = $categorySlug;
            if ($search) $params['q'] = $search;
            return '?' . http_build_query($params);
        };
        ?>
        <div class="d-flex justify-content-center mt-5">
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="<?php echo $pageUrl($page - 1); ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo $pageUrl($i); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="<?php echo $pageUrl($page + 1); ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
