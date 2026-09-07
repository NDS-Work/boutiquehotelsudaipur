<?php
require_once __DIR__ . '/auth.php';
requireLogin();

$filename = 'blog-body-generator-skill.md';
$filepath = dirname(__DIR__) . '/skills/blog-body-generator/SKILL.md';

if (!file_exists($filepath)) {
    http_response_code(404);
    echo "Content template file not found.";
    exit;
}

header('Content-Description: File Transfer');
header('Content-Type: text/markdown; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

readfile($filepath);
exit;
