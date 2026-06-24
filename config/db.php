<?php
/**
 * config/db.php
 * PDO database connection — Singleton pattern.
 * Returns a single shared PDO instance for the entire request lifecycle.
 *
 * Security: Uses prepared statements throughout the app (never raw concat).
 * Charset:  utf8mb4 for full Unicode support (including emoji in reviews).
 */

declare(strict_types=1);

/**
 * Returns the shared PDO database connection.
 * Creates the connection on first call, reuses it on subsequent calls.
 *
 * @throws PDOException If connection fails (error logged in production)
 * @return PDO
 */
function getDB(): PDO
{
    // Static variable persists across function calls within the same request
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo; // Return cached connection
    }

    // Read credentials from environment
    $host    = env('DB_HOST',    '127.0.0.1');
    $port    = env('DB_PORT',    '3306');
    $dbname  = env('DB_NAME',    'olhs_db');
    $user    = env('DB_USER',    'root');
    $pass    = env('DB_PASS',    '');
    $charset = env('DB_CHARSET', 'utf8mb4');

    // Build Data Source Name string
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

    // PDO options for security and performance
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,     // Throw exceptions on SQL errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,           // Always return associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                       // Use native prepared statements
        PDO::ATTR_PERSISTENT         => false,                       // No persistent connections (Laragon)
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci",
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Log error details — never expose DB credentials to the browser
        error_log('[OLHS DB] Connection failed: ' . $e->getMessage());

        if (APP_DEBUG) {
            // Show detailed error in development
            throw $e;
        }

        // Show a user-friendly error in production
        http_response_code(503);
        die('Service temporarily unavailable. Please try again later.');
    }

    return $pdo;
}
