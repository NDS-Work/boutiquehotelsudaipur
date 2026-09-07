<?php
// Router script for PHP built-in server (php -S)
// Replicates .htaccess rewrite rules

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve existing files/directories directly (urldecode handles spaces in paths)
if ($uri !== '/' && file_exists(__DIR__ . urldecode($uri))) {
    return false;
}

// /hotels/collection/slug → hotels.php?collection=slug
if (preg_match('#^/hotels/collection/([^/]+)/?$#', $uri, $m)) {
    $_GET['collection'] = $m[1];
    require __DIR__ . '/hotels.php';
    exit;
}

// /hotels/attraction/slug → hotels.php?attraction=slug
if (preg_match('#^/hotels/attraction/([^/]+)/?$#', $uri, $m)) {
    $_GET['attraction'] = $m[1];
    require __DIR__ . '/hotels.php';
    exit;
}

// /hotels/occasion/slug → hotels.php?occasion=slug
if (preg_match('#^/hotels/occasion/([^/]+)/?$#', $uri, $m)) {
    $_GET['occasion'] = $m[1];
    require __DIR__ . '/hotels.php';
    exit;
}

// /hotels/amenity/slug → hotels.php?amenitySlug=slug
if (preg_match('#^/hotels/amenity/([^/]+)/?$#', $uri, $m)) {
    $_GET['amenitySlug'] = $m[1];
    require __DIR__ . '/hotels.php';
    exit;
}

// /hotels → hotels.php
if (preg_match('#^/hotels/?$#', $uri)) {
    require __DIR__ . '/hotels.php';
    exit;
}

// /hotels/slug → venue-detail.php?slug=slug
if (preg_match('#^/hotels/([^/]+)/?$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/venue-detail.php';
    exit;
}

// /blogs/category/slug → blog-category.php?slug=slug
if (preg_match('#^/blogs/category/([^/]+)/?$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/blog-category.php';
    exit;
}

// /blogs → blogs.php
if (preg_match('#^/blogs/?$#', $uri)) {
    require __DIR__ . '/blogs.php';
    exit;
}

// /blog/slug → blog-detail.php?slug=slug
if (preg_match('#^/blog/([^/]+)/?$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/blog-detail.php';
    exit;
}

// /admin/page → admin/page.php
if (preg_match('#^/admin/([^/]+)/?$#', $uri, $m)) {
    $file = __DIR__ . '/admin/' . $m[1] . '.php';
    if (file_exists($file)) {
        require $file;
        exit;
    }
}

// /page → page.php
if (preg_match('#^/([^/]+)/?$#', $uri, $m)) {
    $file = __DIR__ . '/' . $m[1] . '.php';
    if (file_exists($file)) {
        require $file;
        exit;
    }
}

// Root route
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    exit;
}

// 404 for anything else not matched
require __DIR__ . '/404.php';

