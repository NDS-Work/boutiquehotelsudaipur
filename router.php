<?php
// Router script for PHP built-in server (php -S)
// Replicates .htaccess rewrite rules

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve existing files/directories directly (urldecode handles spaces in paths)
if ($uri !== '/' && file_exists(__DIR__ . urldecode($uri))) {
    return false;
}

// /hotels/slug → venue-detail.php?slug=slug
if (preg_match('#^/hotels/([^/]+)/?$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/venue-detail.php';
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

// Default: index.php
require __DIR__ . '/index.php';
