<?php
/**
 * app/helpers/format.php
 * Formatting utility functions for display.
 * Handles currency (TZS), dates, booking references, and bilingual labels.
 */

declare(strict_types=1);

/**
 * Format an amount as Tanzanian Shillings (TZS).
 * Example: 68750 → "TZS 68,750"
 *
 * @param float|int $amount The monetary amount
 * @param bool      $symbol Include "TZS" prefix
 * @return string
 */
function formatTZS(float|int $amount, bool $symbol = true): string
{
    $formatted = number_format((float)$amount, 0, '.', ',');
    return $symbol ? "TZS {$formatted}" : $formatted;
}

/**
 * Format a database DATETIME string into a readable date.
 * Example: "2024-06-10 16:30:00" → "Mon, 10 Jun 2024"
 *
 * @param string|null $datetime  Database datetime string
 * @param string      $format    PHP date() format string
 * @return string
 */
function formatDate(?string $datetime, string $format = 'D, d M Y'): string
{
    if (empty($datetime)) {
        return '—';
    }
    try {
        return (new DateTime($datetime))->format($format);
    } catch (Exception) {
        return '—';
    }
}

/**
 * Format a database DATETIME to a readable date + time.
 * Example: "2024-06-10 16:30:00" → "10 Jun 2024, 4:30 PM"
 *
 * @param string|null $datetime
 * @return string
 */
function formatDateTime(?string $datetime): string
{
    return formatDate($datetime, 'd M Y, g:i A');
}

/**
 * Get a human-readable time ago string.
 * Example: "3 hours ago", "2 days ago"
 *
 * @param string $datetime Database datetime string
 * @return string
 */
function timeAgo(string $datetime): string
{
    try {
        $time  = new DateTime($datetime);
        $now   = new DateTime();
        $diff  = $now->getTimestamp() - $time->getTimestamp();

        if ($diff < 60)    return 'Just now';
        if ($diff < 3600)  return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';

        return formatDate($datetime, 'd M Y');
    } catch (Exception) {
        return '';
    }
}

/**
 * Generate a unique booking reference number.
 * Format: BK-YYYY-NNNNN (e.g. BK-2024-00123)
 *
 * @param int $lastId The last inserted booking ID
 * @return string
 */
function generateBookingRef(int $lastId): string
{
    return 'BK-' . date('Y') . '-' . str_pad((string)$lastId, 5, '0', STR_PAD_LEFT);
}

/**
 * Generate a simulated transaction ID for payment simulation.
 * Format: SIM-YYYYMMDD-XXXXXXXX
 *
 * @return string
 */
function generateTransactionId(): string
{
    return 'SIM-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
}

/**
 * Get the label for a lorry type in the current language.
 *
 * @param string $type Lorry type key (e.g. 'flatbed')
 * @param string $lang Language code ('en' or 'sw')
 * @return string
 */
function lorryTypeLabel(string $type, string $lang = 'en'): string
{
    $types = LORRY_TYPES;
    return $types[$type][$lang] ?? ucfirst($type);
}

/**
 * Get the label for a booking status in the current language.
 *
 * @param string $status Status key (e.g. 'pending')
 * @param string $lang   Language code ('en' or 'sw')
 * @return string
 */
function bookingStatusLabel(string $status, string $lang = 'en'): string
{
    $statuses = BOOKING_STATUSES;
    return $statuses[$status][$lang] ?? ucfirst($status);
}

/**
 * Get the CSS class for a booking status badge.
 *
 * @param string $status
 * @return string CSS class name
 */
function bookingStatusClass(string $status): string
{
    $statuses = BOOKING_STATUSES;
    return $statuses[$status]['css'] ?? 'status-pending';
}

/**
 * Calculate the platform commission amount.
 *
 * @param float $amount    Booking quoted price (TZS)
 * @param float $rate      Commission rate percent (default: COMMISSION_RATE)
 * @return float           Commission amount (TZS)
 */
function calculateCommission(float $amount, float $rate = COMMISSION_RATE): float
{
    return round($amount * ($rate / 100), 2);
}

/**
 * Calculate the owner payout after commission deduction.
 *
 * @param float $amount Booking quoted price (TZS)
 * @return float        Amount credited to owner wallet (TZS)
 */
function calculateOwnerPayout(float $amount): float
{
    return round($amount - calculateCommission($amount), 2);
}

/**
 * Render star rating HTML (1–5 stars, filled/empty).
 *
 * @param float $rating Average rating (e.g. 4.5)
 * @param int   $max    Maximum stars (default 5)
 * @return string HTML string
 */
function renderStars(float $rating, int $max = 5): string
{
    $html = '<span class="stars" aria-label="Rating: ' . number_format($rating, 1) . ' out of 5">';
    for ($i = 1; $i <= $max; $i++) {
        if ($i <= floor($rating)) {
            $html .= '<i class="fa-solid fa-star star-filled"></i>';
        } elseif ($i - $rating < 1 && $i - $rating > 0) {
            $html .= '<i class="fa-solid fa-star-half-stroke star-half"></i>';
        } else {
            $html .= '<i class="fa-regular fa-star star-empty"></i>';
        }
    }
    $html .= '</span>';
    return $html;
}

/**
 * Truncate a long string to N characters with an ellipsis.
 *
 * @param string $text    Input text
 * @param int    $length  Max characters before truncating
 * @return string
 */
function truncate(string $text, int $length = 100): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '…';
}

/**
 * Get the currently logged-in user's language preference.
 * Falls back to 'en' if not set.
 *
 * @return string 'en' or 'sw'
 */
function currentLang(): string
{
    return $_SESSION['user_lang'] ?? 'en';
}

/**
 * Get the public URL for a lorry photo.
 * Falls back to a default value or empty if no photo is specified.
 *
 * @param string|null $photoPath The database photo path or name
 * @return string
 */
function getLorryPhotoUrl(?string $photoPath): string
{
    if (empty($photoPath)) {
        return '';
    }
    
    // Check if it already starts with /storage/ or http (like in seeds or external links)
    if (str_starts_with($photoPath, '/storage/')) {
        return APP_URL . $photoPath;
    }
    
    if (str_starts_with($photoPath, 'http://') || str_starts_with($photoPath, 'https://')) {
        return $photoPath;
    }
    
    // Otherwise it is an uploaded filename
    return APP_URL . '/storage/lorries/' . $photoPath;
}
