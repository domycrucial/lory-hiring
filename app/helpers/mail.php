<?php
/**
 * app/helpers/mail.php
 * Email sending via PHPMailer + Gmail SMTP.
 * Requires: composer require phpmailer/phpmailer
 *
 * All email templates are defined here.
 * Supports English and Swahili subject/body.
 */

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

// Load PHPMailer autoloader (Composer)
$composerAutoload = ROOT_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

/**
 * Send an email using Gmail SMTP via PHPMailer.
 *
 * @param string $toEmail     Recipient email address
 * @param string $toName      Recipient display name
 * @param string $subject     Email subject line
 * @param string $htmlBody    HTML email body
 * @param string $plainText   Plain-text fallback body
 * @return bool True on success, false on failure
 */
function sendEmail(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    string $plainText = ''
): bool {
    // Validate that PHPMailer is available
    if (!class_exists(PHPMailer::class)) {
        error_log('[OLHS Mail] PHPMailer not found. Run: composer install');
        return false;
    }

    $mail = new PHPMailer(true); // true = throw exceptions

    try {
        // ─── SMTP Configuration ───────────────────────────────
        $mail->isSMTP();
        $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth   = true;
        $mail->Username   = env('MAIL_USERNAME', '');
        $mail->Password   = env('MAIL_PASSWORD', '');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) env('MAIL_PORT', '587');
        $mail->CharSet    = 'UTF-8';

        // Suppress debug output in production
        $mail->SMTPDebug = APP_DEBUG ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;

        // ─── Sender & Recipient ───────────────────────────────
        $mail->setFrom(
            env('MAIL_FROM_EMAIL', env('MAIL_USERNAME', '')),
            env('MAIL_FROM_NAME', APP_NAME)
        );
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(env('MAIL_FROM_EMAIL', env('MAIL_USERNAME', '')), APP_NAME);

        // ─── Content ──────────────────────────────────────────
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = wrapEmailTemplate($subject, $htmlBody);
        $mail->AltBody = $plainText ?: strip_tags($htmlBody);

        $mail->send();

        // Log successful send
        error_log("[OLHS Mail] Sent '{$subject}' to {$toEmail}");
        return true;

    } catch (MailException $e) {
        error_log('[OLHS Mail] Failed: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Wrap email content in a branded HTML template.
 *
 * @param string $title   Email subject (used as heading)
 * @param string $content Main HTML content
 * @return string Complete HTML email
 */
function wrapEmailTemplate(string $title, string $content): string
{
    $appName = APP_NAME;
    $appUrl  = APP_URL;
    $year    = date('Y');

    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>{$title}</title>
    </head>
    <body style="margin:0;padding:0;background:#F4F6FA;font-family:Arial,sans-serif;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F4F6FA;padding:30px 0;">
        <tr><td align="center">
          <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1);">
            <!-- Header -->
            <tr>
              <td style="background:#1A3C6E;padding:24px 32px;text-align:center;">
                <h1 style="color:#fff;margin:0;font-size:24px;">🚛 {$appName}</h1>
              </td>
            </tr>
            <!-- Body -->
            <tr>
              <td style="padding:32px;">
                {$content}
              </td>
            </tr>
            <!-- Footer -->
            <tr>
              <td style="background:#F4F6FA;padding:20px 32px;text-align:center;border-top:1px solid #E2E8F0;">
                <p style="color:#6B7280;font-size:13px;margin:0;">
                  &copy; {$year} {$appName} &bull; <a href="{$appUrl}" style="color:#1A3C6E;">{$appUrl}</a>
                </p>
              </td>
            </tr>
          </table>
        </td></tr>
      </table>
    </body>
    </html>
    HTML;
}

// ─── Email Templates ──────────────────────────────────────────

/**
 * Send booking confirmation email to customer.
 *
 * @param array $user    Customer user record
 * @param array $booking Booking record with lorry data
 * @return bool
 */
function sendBookingConfirmationEmail(array $user, array $booking): bool
{
    $ref    = e($booking['booking_ref']);
    $lorry  = e($booking['lorry_name'] ?? 'Your lorry');
    $date   = formatDate($booking['preferred_date'], 'd M Y');
    $price  = formatTZS((float)$booking['quoted_price']);
    $pickup = e($booking['pickup_address']);
    $drop   = e($booking['delivery_address']);
    $link   = APP_URL . '/bookings/detail/' . (int)$booking['id'];

    $html = <<<HTML
    <h2 style="color:#1A3C6E;">Booking Received ✅</h2>
    <p>Dear <strong>{$user['full_name']}</strong>,</p>
    <p>Your booking request has been submitted successfully. The lorry owner will respond within 24 hours.</p>

    <table width="100%" style="border-collapse:collapse;margin:20px 0;">
      <tr><td style="padding:8px;border:1px solid #E2E8F0;background:#F9FAFB;font-weight:600;">Booking Ref</td>
          <td style="padding:8px;border:1px solid #E2E8F0;">{$ref}</td></tr>
      <tr><td style="padding:8px;border:1px solid #E2E8F0;background:#F9FAFB;font-weight:600;">Lorry</td>
          <td style="padding:8px;border:1px solid #E2E8F0;">{$lorry}</td></tr>
      <tr><td style="padding:8px;border:1px solid #E2E8F0;background:#F9FAFB;font-weight:600;">Pickup</td>
          <td style="padding:8px;border:1px solid #E2E8F0;">{$pickup}</td></tr>
      <tr><td style="padding:8px;border:1px solid #E2E8F0;background:#F9FAFB;font-weight:600;">Delivery</td>
          <td style="padding:8px;border:1px solid #E2E8F0;">{$drop}</td></tr>
      <tr><td style="padding:8px;border:1px solid #E2E8F0;background:#F9FAFB;font-weight:600;">Date</td>
          <td style="padding:8px;border:1px solid #E2E8F0;">{$date}</td></tr>
      <tr><td style="padding:8px;border:1px solid #E2E8F0;background:#F9FAFB;font-weight:600;">Quoted Price</td>
          <td style="padding:8px;border:1px solid #E2E8F0;color:#16A34A;font-weight:600;">{$price}</td></tr>
    </table>

    <p><a href="{$link}" style="display:inline-block;background:#F0A500;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;">View Booking Details</a></p>
    HTML;

    return sendEmail(
        $user['email'],
        $user['full_name'],
        "Booking Confirmed — {$ref}",
        $html
    );
}

/**
 * Send booking status update email (accepted/declined/completed).
 *
 * @param array  $user    Customer user record
 * @param array  $booking Booking record
 * @param string $status  New status string
 * @return bool
 */
function sendBookingStatusEmail(array $user, array $booking, string $status): bool
{
    $ref    = e($booking['booking_ref']);
    $label  = bookingStatusLabel($status);
    $link   = APP_URL . '/bookings/detail/' . (int)$booking['id'];

    $html = <<<HTML
    <h2 style="color:#1A3C6E;">Booking Update: {$label}</h2>
    <p>Dear <strong>{$user['full_name']}</strong>,</p>
    <p>Your booking <strong>{$ref}</strong> status has been updated to: <strong>{$label}</strong>.</p>
    <p><a href="{$link}" style="display:inline-block;background:#1A3C6E;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;">View Booking</a></p>
    HTML;

    return sendEmail(
        $user['email'],
        $user['full_name'],
        "Booking {$label} — {$ref}",
        $html
    );
}

/**
 * Send password reset email with a secure reset link.
 *
 * @param array  $user  User record
 * @param string $token Raw (un-hashed) reset token
 * @return bool
 */
function sendPasswordResetEmail(array $user, string $token): bool
{
    $link = APP_URL . '/auth/reset-password?token=' . urlencode($token) . '&email=' . urlencode($user['email']);

    $html = <<<HTML
    <h2 style="color:#1A3C6E;">Reset Your Password 🔑</h2>
    <p>Dear <strong>{$user['full_name']}</strong>,</p>
    <p>We received a request to reset your password. Click the button below to create a new password. This link expires in <strong>1 hour</strong>.</p>
    <p><a href="{$link}" style="display:inline-block;background:#DC2626;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;">Reset Password</a></p>
    <p style="color:#6B7280;font-size:13px;">If you didn't request this, please ignore this email. Your password will not be changed.</p>
    HTML;

    return sendEmail(
        $user['email'],
        $user['full_name'],
        'Password Reset Request — ' . APP_NAME,
        $html
    );
}
