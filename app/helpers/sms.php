<?php
/**
 * app/helpers/sms.php
 * Simulated SMS notification helper.
 *
 * In MVP mode, SMS messages are NOT sent via a real gateway.
 * Instead, they are logged to the `sms_log` database table and
 * to the application log file. An admin can view all simulated
 * SMS messages in the admin panel.
 *
 * To switch to a real gateway (Africa's Talking), replace
 * the sendSMS() function body with the real API call.
 */

declare(strict_types=1);

/**
 * Send a simulated SMS notification.
 * Logs the message to the sms_log database table.
 *
 * @param string $recipientPhone Phone number with country code (e.g. +255712345678)
 * @param string $message        SMS message text (keep under 160 chars for 1 SMS credit)
 * @return bool True on success
 */
function sendSMS(string $recipientPhone, string $message): bool
{
    // Sanitise the phone number
    $phone   = sanitizePhone($recipientPhone);
    $message = trim($message);

    if (empty($phone) || empty($message)) {
        error_log('[OLHS SMS] Invalid phone or empty message — skipped');
        return false;
    }

    // Log to application error log
    error_log("[OLHS SMS SIMULATED] To: {$phone} | Msg: {$message}");

    // Log to sms_log database table
    try {
        $db  = getDB();
        $sql = "INSERT INTO sms_log (recipient, message, status, sent_at)
                VALUES (:recipient, :message, 'sent', NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':recipient' => $phone,
            ':message'   => mb_substr($message, 0, 500), // Max 500 chars in DB
        ]);
        return true;
    } catch (PDOException $e) {
        error_log('[OLHS SMS] DB log failed: ' . $e->getMessage());
        return false;
    }
}

// ─── SMS Message Templates ────────────────────────────────────

/**
 * SMS sent to customer when booking is submitted.
 *
 * @param string $bookingRef Booking reference number
 * @param string $lorryName  Name of the lorry booked
 * @return string
 */
function smsBookingSubmitted(string $bookingRef, string $lorryName): string
{
    return "OLHS: Booking {$bookingRef} for {$lorryName} submitted. " .
           "You will be notified once the owner responds. - olhs.co.tz";
}

/**
 * SMS sent to lorry owner when a new booking request arrives.
 *
 * @param string $bookingRef  Booking reference
 * @param string $customerName Customer's name
 * @param string $date        Preferred date
 * @return string
 */
function smsNewBookingRequest(string $bookingRef, string $customerName, string $date): string
{
    return "OLHS: New booking {$bookingRef} from {$customerName} on {$date}. " .
           "Log in to accept or decline within 24 hrs. - olhs.co.tz";
}

/**
 * SMS sent to customer when owner accepts booking.
 *
 * @param string $bookingRef Booking reference
 * @param string $ownerName  Owner's name
 * @return string
 */
function smsBookingAccepted(string $bookingRef, string $ownerName): string
{
    return "OLHS: Great news! Booking {$bookingRef} accepted by {$ownerName}. " .
           "Please complete payment to confirm. - olhs.co.tz";
}

/**
 * SMS sent to customer when owner declines booking.
 *
 * @param string $bookingRef Booking reference
 * @return string
 */
function smsBookingDeclined(string $bookingRef): string
{
    return "OLHS: Sorry, booking {$bookingRef} was declined. " .
           "Please search for another lorry on olhs.co.tz";
}

/**
 * SMS sent to customer when trip is marked completed.
 *
 * @param string $bookingRef Booking reference
 * @return string
 */
function smsBookingCompleted(string $bookingRef): string
{
    return "OLHS: Trip {$bookingRef} completed! Please rate your experience. " .
           "Thank you for using OLHS. - olhs.co.tz";
}

/**
 * SMS sent to owner when payment is confirmed.
 *
 * @param string $bookingRef Booking reference
 * @param string $amount     Payout amount formatted
 * @return string
 */
function smsPaymentReceived(string $bookingRef, string $amount): string
{
    return "OLHS: Payment received for {$bookingRef}. " .
           "{$amount} will be credited to your wallet after trip completion. - olhs.co.tz";
}
