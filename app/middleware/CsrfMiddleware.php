<?php
/**
 * app/middleware/CsrfMiddleware.php
 * CSRF (Cross-Site Request Forgery) protection.
 *
 * Every state-changing form must include a hidden CSRF token field.
 * AJAX requests must include the token in the X-CSRF-Token header.
 *
 * Token is generated per-session (in index.php) and verified here.
 */

declare(strict_types=1);

/**
 * Output a hidden CSRF input field for use in HTML forms.
 * Embed this in every <form> tag that changes server state.
 *
 * Usage in views:
 *   <?= csrfField() ?>
 *
 * @return string HTML hidden input field
 */
function csrfField(): string
{
    $token = $_SESSION['csrf_token'] ?? '';
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Get the raw CSRF token value (for JavaScript/AJAX requests).
 *
 * Usage in views (to pass to JS):
 *   const csrfToken = <?= json_encode(csrfToken()) ?>;
 *
 * @return string CSRF token
 */
function csrfToken(): string
{
    return $_SESSION['csrf_token'] ?? '';
}

/**
 * Verify the CSRF token from a form submission or AJAX header.
 * Aborts with 403 if the token is missing or invalid.
 *
 * @return void
 */
function verifyCsrf(): void
{
    // Get token from POST body (forms) or from AJAX header
    $submittedToken = $_POST['csrf_token']
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? '';

    $sessionToken = $_SESSION['csrf_token'] ?? '';

    // Use hash_equals() — constant-time comparison to prevent timing attacks
    if (empty($submittedToken) || !hash_equals($sessionToken, $submittedToken)) {
        http_response_code(403);

        // If this is an AJAX request, return JSON error
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            jsonResponse(['success' => false, 'error' => 'CSRF token mismatch.'], 403);
        }

        // Otherwise, show an error page
        flashMessage('error', 'Ombi lililoshindwa. Tafadhali jaribu tena. / Invalid request. Please try again.');
        redirect(APP_URL . '/');
    }
}
