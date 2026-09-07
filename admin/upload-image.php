<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

// Support Quill or standard file input named 'image' or 'file'
$file = $_FILES['image'] ?? $_FILES['file'] ?? null;
if (!$file) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No image file provided.']);
    exit;
}

require_once __DIR__ . '/../data/blogs.php';
$result = _handleBlogImageUpload($file);

if (!$result['success']) {
    http_response_code(400);
    echo json_encode($result);
    exit;
}

echo json_encode([
    'success' => true,
    'url' => $result['url']
]);
