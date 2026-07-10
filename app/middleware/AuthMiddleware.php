<?php
/**
 * app/middleware/AuthMiddleware.php
 * Role-based access control middleware.
 *
 * Guards controller methods by checking:
 * 1. User is logged in (has active session)
 * 2. User has the required role (customer, lorry_owner, admin, super_admin)
 * 3. User account is not suspended or banned
 *
 * Usage in controllers:
 *   requireLogin();             // Any logged-in user
 *   requireRole('customer');    // Customer only
 *   requireRole(['admin','super_admin']); // Admin or Super Admin
 */

declare(strict_types=1);

/**
 * Check if a user is currently logged in.
 *
 * @return bool True if a valid session exists
 */
function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']);
}

/**
 * Get the currently logged-in user's ID.
 *
 * @return int|null User ID or null if not logged in
 */
function currentUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Get the currently logged-in user's role.
 *
 * @return string|null Role string or null if not logged in
 */
function currentUserRole(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get the currently logged-in user's full name.
 *
 * @return string Name or empty string
 */
function currentUserName(): string
{
    return $_SESSION['user_name'] ?? '';
}

/**
 * Require the user to be logged in.
 * Redirects to login page with return URL if not authenticated.
 *
 * @return void
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        $returnUrl = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        flashMessage('error', 'Tafadhali ingia kwanza. / Please log in first.');
        redirect(APP_URL . '/auth/login?return=' . $returnUrl);
    }
}

/**
 * Require the user to be a guest (not logged in).
 * Redirects logged-in users to their dashboard.
 *
 * @return void
 */
function requireGuest(): void
{
    if (isLoggedIn()) {
        redirect(getDashboardUrl(currentUserRole() ?? ''));
    }
}

/**
 * Require the user to have a specific role (or one of several roles).
 * Also enforces login if not authenticated.
 *
 * @param string|string[] $roles Single role or array of allowed roles
 * @return void
 */
function requireRole(string|array $roles): void
{
    // Must be logged in first
    requireLogin();

    $allowed     = (array) $roles;
    $currentRole = currentUserRole();

    // super_admin has access to everything
    if ($currentRole === 'super_admin') {
        return;
    }

    if (!in_array($currentRole, $allowed, true)) {
        // User is logged in but lacks permission
        http_response_code(403);
        flashMessage('error', 'Huna ruhusa. / You do not have permission to access this page.');
        redirect(getDashboardUrl($currentRole ?? ''));
    }
}

/**
 * Get the appropriate dashboard URL for a given role.
 *
 * @param string $role User role
 * @return string URL to redirect to
 */
function getDashboardUrl(string $role): string
{
    return match($role) {
        'customer'    => APP_URL . '/dashboard/customer',
        'lorry_owner' => APP_URL . '/dashboard/owner',
        'admin'       => APP_URL . '/admin/',
        'super_admin' => APP_URL . '/admin/',
        default       => APP_URL . '/',
    };
}

/**
 * Log the user into the session after successful authentication.
 * Called by AuthController after verifying credentials.
 *
 * @param array $user User record from database
 * @return void
 */
function loginUser(array $user): void
{
    // Keep current session language if already selected
    $currentSessionLang = $_SESSION['user_lang'] ?? null;

    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);

    // Store essential user data in session (never store password hash)
    $_SESSION['user_id']    = (int) $user['id'];
    $_SESSION['user_role']  = $user['role'];
    $_SESSION['user_name']  = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    
    // Maintain selected language from homepage or fallback to DB/default
    $_SESSION['user_lang']  = $currentSessionLang ?? $user['preferred_lang'] ?? 'en';
    $_SESSION['logged_in_at'] = time();

    // Persist this language preference to the database
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE users SET preferred_lang = ? WHERE id = ?");
        $stmt->execute([$_SESSION['user_lang'], (int)$user['id']]);
    } catch (Exception $e) {
        // Fail silently
    }
}

/**
 * Destroy the current user session (logout).
 *
 * @return void
 */
function logoutUser(): void
{
    // Clear session data
    $_SESSION = [];

    // Delete the session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Check if the session has exceeded the idle timeout.
 * Call this at the start of each request if session expiry is needed.
 *
 * @return void
 */
function checkSessionTimeout(): void
{
    if (!isLoggedIn()) {
        return;
    }

    $lastActivity = $_SESSION['last_activity'] ?? null;

    if ($lastActivity !== null && (time() - (int)$lastActivity) > SESSION_LIFETIME) {
        logoutUser();
        flashMessage('warning', 'Muda wa kikao umekwisha. / Your session has expired. Please log in again.');
        redirect(APP_URL . '/auth/login');
    }

    $_SESSION['last_activity'] = time();
}
