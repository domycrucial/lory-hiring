<?php
/**
 * app/helpers/sanitize.php
 * Input sanitisation and validation utility functions.
 *
 * RULE: Always sanitise ALL user input before outputting to HTML.
 *       Always validate before trusting any value in business logic.
 */

declare(strict_types=1);

/**
 * Escape a string for safe HTML output (prevents XSS).
 * Use this on EVERY variable echoed into HTML.
 *
 * @param mixed $value Any value to escape
 * @return string HTML-safe string
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Sanitise a string: trim whitespace + strip dangerous HTML tags.
 *
 * @param string $input Raw user input
 * @return string Sanitised string
 */
function sanitizeString(string $input): string
{
    return trim(strip_tags($input));
}

/**
 * Sanitise and validate an email address.
 *
 * @param string $email Raw email input
 * @return string|false Sanitised email or false if invalid
 */
function sanitizeEmail(string $email): string|false
{
    $clean = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    return filter_var($clean, FILTER_VALIDATE_EMAIL) ? strtolower($clean) : false;
}

/**
 * Sanitise a phone number — keeps digits, +, -, spaces only.
 *
 * @param string $phone Raw phone number
 * @return string Sanitised phone number
 */
function sanitizePhone(string $phone): string
{
    return preg_replace('/[^0-9+\-\s()]/', '', trim($phone));
}

/**
 * Validate that a string is within min/max length.
 *
 * @param string $value Input string
 * @param int    $min   Minimum length
 * @param int    $max   Maximum length
 * @return bool
 */
function validateLength(string $value, int $min, int $max): bool
{
    $len = mb_strlen(trim($value));
    return $len >= $min && $len <= $max;
}

/**
 * Validate a password: min 8 chars, at least one uppercase,
 * one lowercase, and one digit.
 *
 * @param string $password
 * @return bool
 */
function validatePassword(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)    // Has uppercase
        && preg_match('/[a-z]/', $password)    // Has lowercase
        && preg_match('/[0-9]/', $password);   // Has digit
}

/**
 * Get a POST value — sanitised as a plain string.
 * Returns the default value if the key doesn't exist.
 *
 * @param string $key     POST field name
 * @param string $default Default value
 * @return string
 */
function post(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? sanitizeString((string)$_POST[$key]) : $default;
}

/**
 * Get a GET value — sanitised as a plain string.
 *
 * @param string $key     GET parameter name
 * @param string $default Default value
 * @return string
 */
function get(string $key, string $default = ''): string
{
    return isset($_GET[$key]) ? sanitizeString((string)$_GET[$key]) : $default;
}

/**
 * Get a POST value as an integer.
 *
 * @param string $key     POST field name
 * @param int    $default Default value
 * @return int
 */
function postInt(string $key, int $default = 0): int
{
    return isset($_POST[$key]) ? (int)$_POST[$key] : $default;
}

/**
 * Get a GET parameter as an integer.
 *
 * @param string $key     GET parameter name
 * @param int    $default Default value
 * @return int
 */
function getInt(string $key, int $default = 0): int
{
    return isset($_GET[$key]) ? (int)$_GET[$key] : $default;
}

/**
 * Return JSON response and exit. Used by API endpoints.
 *
 * @param array $data   Data to encode as JSON
 * @param int   $status HTTP status code
 * @return never
 */
function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Redirect to a URL and exit.
 *
 * @param string $url Absolute or relative URL to redirect to
 * @return never
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Set a flash message to display on the next page load.
 * Supports types: success, error, warning, info.
 *
 * @param string $type    Message type (success|error|warning|info)
 * @param string $message Message text
 */
function flashMessage(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear the current flash message (if any).
 *
 * @return array|null ['type' => ..., 'message' => ...] or null
 */
function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
