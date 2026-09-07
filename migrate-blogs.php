<?php
// ─────────────────────────────────────────────────────────────────
//  migrate-blogs.php  –  Run ONCE to create blog tables & seed categories
//
//  Usage (CLI):     php migrate-blogs.php
//  Usage (Browser): /migrate-blogs.php?run=1
// ─────────────────────────────────────────────────────────────────

declare(strict_types=1);

require_once __DIR__ . '/data/blogs.php';

$isCli = PHP_SAPI === 'cli';

if (!$isCli && (!isset($_GET['run']) || $_GET['run'] !== '1')) {
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Blog Migration</title></head>
    <body style="font-family:sans-serif;padding:40px;background:#f8f9fa;">
        <h2>Blog Database Migration</h2>
        <p>This script will create the <code>link_blog_categories</code> and <code>link_blogs</code> tables and seed starter categories.</p>
        <a href="?run=1" style="display:inline-block;padding:10px 20px;background:#9ebffe;color:#000;text-decoration:none;border-radius:4px;font-weight:bold;">Execute Migration Now</a>
    </body>
    </html>
    <?php
    exit;
}

header('Content-Type: text/html; charset=utf-8');
echo "<h3>Executing Blog Migration...</h3><pre>";

try {
    $db = _getBlogDb();
    echo "✓ Connected to SQLite database.\n";
    echo "✓ Ensured tables 'link_blog_categories' and 'link_blogs' exist.\n";

    // Ensure assets/uploads/blogs directory exists
    $uploadDir = __DIR__ . '/assets/uploads/blogs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "✓ Created directory: assets/uploads/blogs/\n";
    } else {
        echo "✓ Directory assets/uploads/blogs/ already exists.\n";
    }

    // Seed default categories if empty
    $existingCats = getAllBlogCategories(false);
    if (empty($existingCats)) {
        $defaultCategories = [
            [
                'name' => 'Travel Guides',
                'slug' => 'travel-guides',
                'description' => 'Comprehensive itineraries, local travel tips, and essential guides for exploring the City of Lakes.',
                'meta_title' => 'Udaipur Travel Guides & Trip Planning | Boutique Hotels in Udaipur',
                'meta_description' => 'Explore insider travel guides, curated itineraries, and local tips for your dream vacation in Udaipur.',
                'sort_order' => 1
            ],
            [
                'name' => 'Heritage & Culture',
                'slug' => 'heritage-and-culture',
                'description' => 'Immerse yourself in Mewari traditions, royal architecture, palace histories, and cultural festivals.',
                'meta_title' => 'Heritage Hotels & Mewar Culture in Udaipur | Boutique Hotels in Udaipur',
                'meta_description' => 'Discover the royal heritage, Rajput architecture, and cultural stories behind Udaipur’s iconic havelis and palaces.',
                'sort_order' => 2
            ],
            [
                'name' => 'Couples & Romance',
                'slug' => 'couples-and-romance',
                'description' => 'Romantic lakeview dining, honeymoon sanctuaries, and couple experiences in India’s most romantic city.',
                'meta_title' => 'Romantic Udaipur Stays & Honeymoon Guides | Boutique Hotels in Udaipur',
                'meta_description' => 'Curated guides for couples and honeymooners seeking romantic boutique hotels, sunset points, and intimate dining.',
                'sort_order' => 3
            ],
            [
                'name' => 'Luxury Stays',
                'slug' => 'luxury-stays',
                'description' => 'Opulent palace sanctuaries, high-end lakefront suites, and royal Mewari hospitality.',
                'meta_title' => 'Luxury Boutique Hotels in Udaipur | Boutique Hotels in Udaipur',
                'meta_description' => 'Experience supreme luxury in Udaipur with our curated guides to the finest royal suites and lakeside boutique havelis.',
                'sort_order' => 4
            ]
        ];

        foreach ($defaultCategories as $cat) {
            saveBlogCategory($cat);
            echo "✓ Seeded category: " . htmlspecialchars($cat['name']) . "\n";
        }
    } else {
        echo "✓ Categories already exist (" . count($existingCats) . " found). Skipping seed.\n";
    }

    echo "\n<strong style='color:green;'>SUCCESS: Blog system is fully configured and ready!</strong>\n";
    echo "<p><a href='/admin/blogs.php' style='color:#0d6efd;'>Go to Admin Blogs</a> | <a href='/blogs' style='color:#0d6efd;'>View Public Blogs</a></p>";

} catch (Throwable $e) {
    echo "\n<strong style='color:red;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</strong>\n";
}
echo "</pre>";
