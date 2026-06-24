<?php
/**
 * config/constants.php
 * Application-wide constants derived from .env values.
 * Loaded after config/env.php.
 */

declare(strict_types=1);

// ─── Application ─────────────────────────────────────────────
define('APP_NAME',    env('APP_NAME', 'Online Lorries Hiring System'));
define('APP_URL',     rtrim(env('APP_URL', 'http://lory-hiring.test'), '/'));
define('APP_ENV',     env('APP_ENV', 'development'));
define('APP_KEY',     env('APP_KEY', 'changeme'));
define('APP_VERSION', '1.0.0');
define('APP_DEBUG',   APP_ENV === 'development');

// ─── Paths (absolute filesystem paths) ───────────────────────
// ROOT_PATH is already defined in index.php (the front controller).
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
define('APP_PATH',     ROOT_PATH . '/app');
define('CONFIG_PATH',  ROOT_PATH . '/config');
define('PUBLIC_PATH',  ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('LOG_PATH',     ROOT_PATH . '/logs');
define('VIEW_PATH',    APP_PATH  . '/views');

// ─── Storage for uploaded lorry photos ────────────────────────
define('LORRY_PHOTO_PATH', STORAGE_PATH . '/lorries');     // Filesystem path
define('LORRY_PHOTO_URL',  APP_URL . '/storage/lorries');  // Public URL (via serve.php)

// ─── Session Settings ─────────────────────────────────────────
define('SESSION_NAME',        'olhs_session');
define('SESSION_LIFETIME',    7200);    // 2 hours idle timeout (seconds)
define('REMEMBER_ME_DAYS',    30);      // Remember-me cookie duration
define('LOGIN_MAX_ATTEMPTS',  5);       // Lock after N failed logins
define('LOCKOUT_MINUTES',     15);      // Lockout duration in minutes

// ─── Business Rules ───────────────────────────────────────────
define('COMMISSION_RATE',         (float) env('COMMISSION_RATE', '8'));         // % platform fee
define('MIN_WITHDRAWAL',          (int)   env('MIN_WITHDRAWAL',  '5000'));      // TZS
define('BOOKING_TIMEOUT_HOURS',   (int)   env('BOOKING_TIMEOUT_HOURS', '24')); // Auto-cancel

// ─── Supported Lorry Types ────────────────────────────────────
define('LORRY_TYPES', [
    'flatbed'      => ['en' => 'Flatbed',     'sw' => 'Basi Wazi'],
    'box'          => ['en' => 'Box Truck',   'sw' => 'Lori la Sanduku'],
    'tipper'       => ['en' => 'Tipper',      'sw' => 'Tipper'],
    'tanker'       => ['en' => 'Tanker',      'sw' => 'Tanki'],
    'refrigerated' => ['en' => 'Refrigerated','sw' => 'Jokofu la Lori'],
    'mini'         => ['en' => 'Mini Truck',  'sw' => 'Lori Ndogo'],
]);

// ─── Booking Status Labels ────────────────────────────────────
define('BOOKING_STATUSES', [
    'pending'    => ['en' => 'Pending',    'sw' => 'Inasubiri',  'css' => 'status-pending'],
    'accepted'   => ['en' => 'Accepted',   'sw' => 'Imekubaliwa','css' => 'status-accepted'],
    'in_transit' => ['en' => 'In Transit', 'sw' => 'Safarini',   'css' => 'status-transit'],
    'completed'  => ['en' => 'Completed',  'sw' => 'Imekamilika','css' => 'status-completed'],
    'cancelled'  => ['en' => 'Cancelled',  'sw' => 'Imefutwa',   'css' => 'status-cancelled'],
]);

// ─── Payment Methods ──────────────────────────────────────────
define('PAYMENT_METHODS', [
    'mpesa'  => ['en' => 'M-Pesa',      'sw' => 'M-Pesa'],
    'airtel' => ['en' => 'Airtel Money','sw' => 'Airtel Money'],
    'halotel'=> ['en' => 'Halotel',     'sw' => 'Halotel'],
    'card'   => ['en' => 'Bank Card',   'sw' => 'Kadi ya Benki'],
    'cash'   => ['en' => 'Cash',        'sw' => 'Pesa Taslimu'],
]);

// ─── Max file upload size (2MB per image) ─────────────────────
define('MAX_PHOTO_SIZE_BYTES', 2 * 1024 * 1024);  // 2 MB
define('MAX_PHOTOS_PER_LORRY', 5);
define('ALLOWED_PHOTO_TYPES',  ['image/jpeg', 'image/png', 'image/webp']);

// ─── Google Maps ──────────────────────────────────────────────
define('GOOGLE_MAPS_API_KEY', env('GOOGLE_MAPS_API_KEY', ''));

// ─── Error display (development only) ─────────────────────────
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}
