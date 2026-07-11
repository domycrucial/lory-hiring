<?php
/**
 * index.php — Main Application Entry Point (Front Controller)
 *
 * All HTTP requests are routed here by .htaccess.
 * This file bootstraps the application, then dispatches
 * to the appropriate controller method based on the URL.
 */

declare(strict_types=1);

// ─── Define root path constant ───────────────────────────────
define('ROOT_PATH', __DIR__);
define('START_TIME', microtime(true));

// ─── Bootstrap: load config and core files ───────────────────
require_once ROOT_PATH . '/config/env.php';       // Load .env first
require_once ROOT_PATH . '/config/constants.php'; // App constants
require_once ROOT_PATH . '/config/db.php';         // PDO connection

// ─── Load helpers ─────────────────────────────────────────────
require_once ROOT_PATH . '/app/helpers/sanitize.php';
require_once ROOT_PATH . '/app/helpers/format.php';
require_once ROOT_PATH . '/app/helpers/mail.php';
require_once ROOT_PATH . '/app/helpers/sms.php';
require_once ROOT_PATH . '/app/helpers/log.php';

// ─── Load middleware ──────────────────────────────────────────
require_once ROOT_PATH . '/app/middleware/CsrfMiddleware.php';
require_once ROOT_PATH . '/app/middleware/AuthMiddleware.php';

// ─── Load models ──────────────────────────────────────────────
require_once ROOT_PATH . '/app/models/BaseModel.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Lorry.php';
require_once ROOT_PATH . '/app/models/Booking.php';
require_once ROOT_PATH . '/app/models/Payment.php';
require_once ROOT_PATH . '/app/models/Notification.php';
require_once ROOT_PATH . '/app/models/Withdrawal.php';
require_once ROOT_PATH . '/app/models/Review.php';

// ─── Load controllers ─────────────────────────────────────────
require_once ROOT_PATH . '/app/controllers/HomeController.php';
require_once ROOT_PATH . '/app/controllers/AuthController.php';
require_once ROOT_PATH . '/app/controllers/LorryController.php';
require_once ROOT_PATH . '/app/controllers/BookingController.php';
require_once ROOT_PATH . '/app/controllers/PaymentController.php';
require_once ROOT_PATH . '/app/controllers/NotificationController.php';
require_once ROOT_PATH . '/app/controllers/AdminController.php';
require_once ROOT_PATH . '/app/controllers/WalletController.php';
require_once ROOT_PATH . '/app/controllers/ReviewController.php';
require_once ROOT_PATH . '/app/controllers/ProfileController.php';

// ─── Start session ────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => false,          // Set true when SSL is enabled on production
        'httponly' => true,            // Prevent JS from accessing session cookie
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ─── Enforce session idle timeout ─────────────────────────────
checkSessionTimeout();

// ─── Auto-cancel expired bookings (Pseudo-cron) ──────────────
try {
    $bookingModel = new Booking();
    $bookingModel->autoCancelExpired();
} catch (Exception $e) {
    error_log('[OLHS Pseudo-Cron] Failed auto-cancelling: ' . $e->getMessage());
}

// ─── Regenerate CSRF token if not set ────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ─── Parse request URL and method ─────────────────────────────
$requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
$requestUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip the base URL path if app is in a subdirectory
$basePath = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/');
if ($basePath !== '' && str_starts_with($requestUri, $basePath)) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestUri = trim($requestUri, '/');

// ─── Route Definitions ────────────────────────────────────────
// Format: 'METHOD:uri-pattern' => [ControllerClass, methodName]
// Dynamic segments use named regex groups: (?P<id>[0-9]+)
$routes = [
    // ── Home ──────────────────────────────────────────────────
    'GET:'                            => ['HomeController',         'index'],

    // ── Auth ──────────────────────────────────────────────────
    'GET:auth/login'                  => ['AuthController',         'loginForm'],
    'POST:auth/login'                 => ['AuthController',         'login'],
    'GET:auth/register'               => ['AuthController',         'registerForm'],
    'POST:auth/register'              => ['AuthController',         'register'],
    'GET:auth/logout'                 => ['AuthController',         'logout'],
    'GET:auth/forgot-password'        => ['AuthController',         'forgotPasswordForm'],
    'POST:auth/forgot-password'       => ['AuthController',         'forgotPassword'],
    'GET:auth/reset-password'         => ['AuthController',         'resetPasswordForm'],
    'POST:auth/reset-password'        => ['AuthController',         'resetPassword'],

    // ── Lorries ───────────────────────────────────────────────
    'GET:lorries/search'              => ['LorryController',        'search'],
    'GET:lorries/add'                 => ['LorryController',        'addForm'],
    'POST:lorries/add'                => ['LorryController',        'add'],
    'GET:lorries/mine'                => ['LorryController',        'mine'],
    'GET:lorries/(?P<id>[0-9]+)'      => ['LorryController',        'detail'],
    'GET:lorries/(?P<id>[0-9]+)/edit' => ['LorryController',        'editForm'],
    'POST:lorries/(?P<id>[0-9]+)/edit'=> ['LorryController',        'edit'],
    'POST:lorries/(?P<id>[0-9]+)/delete'=> ['LorryController',      'delete'],

    // ── Bookings ──────────────────────────────────────────────
    'GET:bookings/create/(?P<lorry_id>[0-9]+)' => ['BookingController', 'createForm'],
    'POST:bookings/create'            => ['BookingController',      'create'],
    'GET:bookings/mine'               => ['BookingController',        'myBookings'],
    'GET:api/v1/bookings/(?P<id>[0-9]+)' => ['BookingController', 'apiDetail'],
    'GET:bookings/owner'              => ['BookingController',      'ownerBookings'],
    'GET:bookings/detail/(?P<id>[0-9]+)' => ['BookingController',  'detail'],
    'POST:bookings/(?P<id>[0-9]+)/accept'  => ['BookingController','accept'],
    'POST:bookings/(?P<id>[0-9]+)/decline' => ['BookingController','decline'],
    'POST:bookings/(?P<id>[0-9]+)/complete'=> ['BookingController','complete'],
    'POST:bookings/(?P<id>[0-9]+)/cancel'  => ['BookingController','cancel'],
    'POST:bookings/(?P<id>[0-9]+)/start'   => ['BookingController','startTrip'],

    // ── Language Switcher ─────────────────────────────────────
    'GET:lang/switch/(?P<lang>en|sw)' => ['HomeController',         'switchLang'],

    // ── Payments ──────────────────────────────────────────────
    'GET:payments/checkout/(?P<booking_id>[0-9]+)' => ['PaymentController', 'checkoutForm'],
    'POST:payments/checkout'          => ['PaymentController',      'checkout'],
    'GET:payments/success'            => ['PaymentController',      'success'],
    'GET:payments/history'            => ['PaymentController',      'history'],
    'GET:api/v1/wallet/balance'       => ['PaymentController',      'apiBalance'],
    'POST:api/v1/wallet/deposit'      => ['PaymentController',      'apiDeposit'],
    'POST:api/v1/payments/checkout-ajax' => ['PaymentController',   'checkoutAjax'],

    // ── Dashboard ─────────────────────────────────────────────
    'GET:dashboard/customer'          => ['HomeController',         'customerDashboard'],
    'GET:dashboard/owner'             => ['HomeController',         'ownerDashboard'],



    // ── Wallet ────────────────────────────────────────────────
    'GET:wallet'                      => ['WalletController',       'index'],
    'POST:wallet/withdraw'            => ['WalletController',       'withdraw'],

    // ── Admin Panel ───────────────────────────────────────────
    'GET:admin'                       => ['AdminController',        'index'],
    'GET:admin/'                      => ['AdminController',        'index'],
    'GET:admin/lorries'               => ['AdminController',        'lorries'],
    'GET:admin/logs'                  => ['AdminController',        'systemLogs'],
    'POST:admin/lorries/(?P<id>[0-9]+)/approve' => ['AdminController', 'approve'],
    'POST:admin/lorries/(?P<id>[0-9]+)/reject'  => ['AdminController', 'reject'],
    'GET:admin/users'                 => ['AdminController',        'users'],
    'POST:admin/users/create'         => ['AdminController',        'createUser'],
    'POST:admin/users/(?P<id>[0-9]+)/suspend'   => ['AdminController', 'suspend'],
    'POST:admin/users/(?P<id>[0-9]+)/activate'  => ['AdminController', 'activate'],
    'GET:admin/bookings'              => ['AdminController',        'bookings'],
    'GET:admin/payments'              => ['AdminController',        'payments'],
    'POST:admin/withdrawals/(?P<id>[0-9]+)/approve' => ['AdminController', 'approveWithdrawal'],
    'POST:admin/withdrawals/(?P<id>[0-9]+)/reject'  => ['AdminController', 'rejectWithdrawal'],

    // ── Profile ───────────────────────────────────────────────
    'GET:profile'                     => ['ProfileController',      'show'],
    'GET:profile/edit'                => ['ProfileController',      'editForm'],
    'POST:profile/edit'               => ['ProfileController',      'update'],

    // ── Reviews ───────────────────────────────────────────────
    'GET:bookings/(?P<booking_id>[0-9]+)/review' => ['ReviewController', 'create'],
    'POST:bookings/(?P<booking_id>[0-9]+)/review' => ['ReviewController', 'store'],

    // ── Notifications (AJAX JSON endpoints) ───────────────────
    'GET:api/v1/notifications'        => ['NotificationController', 'getUnread'],
    'POST:api/v1/notifications/read'  => ['NotificationController', 'markAllRead'],

    // ── Lorry API (AJAX) ──────────────────────────────────────
    'GET:api/v1/lorries/search'       => ['LorryController',        'apiSearch'],
    'GET:api/v1/lorries/(?P<id>[0-9]+)' => ['LorryController',     'apiDetail'],
];

// ─── Router: Match request to a route ────────────────────────
$matched = false;

foreach ($routes as $routeKey => $handler) {
    // Split 'METHOD:pattern' into parts
    [$routeMethod, $routePattern] = explode(':', $routeKey, 2);

    // Check HTTP method matches
    if ($routeMethod !== $requestMethod) {
        continue;
    }

    // Build full regex pattern (anchored, case-insensitive)
    $pattern = '#^' . $routePattern . '$#i';

    if (preg_match($pattern, $requestUri, $params)) {
        // Remove numeric keys from $params (keep only named groups)
        $params = array_filter($params, 'is_string', ARRAY_FILTER_USE_KEY);

        [$controllerClass, $method] = $handler;

        // Instantiate controller and call method
        $controller = new $controllerClass();
        $controller->$method($params);

        $matched = true;
        break;
    }
}

// ─── 404 — No matching route found ───────────────────────────
if (!$matched) {
    http_response_code(404);
    require_once VIEW_PATH . '/errors/404.php';
}
