<?php
/**
 * serve.php
 * Serves files from the storage folder securely.
 * Checks for path traversal and serves correct mime types.
 */

declare(strict_types=1);

require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/constants.php';

$file = $_GET['file'] ?? '';

if (empty($file)) {
    http_response_code(400);
    die('Bad Request');
}

// Resolve paths to handle directory traversal
$storagePath = realpath(STORAGE_PATH);
$filePath = realpath(STORAGE_PATH . DIRECTORY_SEPARATOR . $file);

if ($filePath === false || !str_starts_with($filePath, $storagePath) || !is_file($filePath)) {
    http_response_code(404);
    die('File not found');
}

// Get the MIME type
$mimeType = mime_content_type($filePath);

// Whitelist of allowed MIME types for safety
$allowedMimeTypes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/pdf',
];

if (!in_array($mimeType, $allowedMimeTypes, true)) {
    http_response_code(403);
    die('Forbidden');
}

// Set cache headers for performance
header('Cache-Control: public, max-age=86400'); // 1 day
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
exit;
