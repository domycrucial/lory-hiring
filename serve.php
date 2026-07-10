<?php
/**
 * serve.php
 * Serves files from the storage folder securely.
 * Checks for path traversal and serves correct mime types.
 */

declare(strict_types=1);

// ─── If running under CLI server (PHP built-in server) ────────
if (php_sapi_name() === 'cli-server') {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = urldecode($uri);

    // Serve storage files through this script
    if (preg_match('#^/storage/(.*)$#', $uri, $matches)) {
        $_GET['file'] = $matches[1];
    } elseif ($uri !== '/' && (file_exists(__DIR__ . '/public' . $uri) || is_file(__DIR__ . '/public' . $uri))) {
        return false; // let built-in server serve public static file directly
    } elseif ($uri !== '/' && (file_exists(__DIR__ . $uri) || is_file(__DIR__ . $uri))) {
        return false; // let built-in server serve static file directly
    } else {
        // Route everything else to the index.php front controller
        include __DIR__ . '/index.php';
        exit;
    }
}

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
