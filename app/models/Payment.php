<?php
/**
 * app/models/Payment.php
 * Payment data model — simulated mobile money transactions.
 */

declare(strict_types=1);

class Payment extends BaseModel
{
    protected string $table = 'payments';

    /**
     * Create a new simulated payment record.
     * Immediately marks the payment as completed (simulation mode).
     * Also credits the owner wallet via the User model.
     *
     * @param array $data [booking_id, payer_id, amount, payment_method, mobile_number]
     * @return array ['success' => bool, 'transaction_id' => string, 'payment_id' => int]
     */
    public function processSimulated(array $data): array
    {
        $amount     = (float)$data['amount'];
        $commission = calculateCommission($amount);
        $payout     = calculateOwnerPayout($amount);
        $txnId      = generateTransactionId();

        // Insert the payment record
        $paymentId = $this->create([
            'booking_id'          => (int)$data['booking_id'],
            'payer_id'            => (int)$data['payer_id'],
            'amount'              => $amount,
            'platform_commission' => $commission,
            'owner_payout'        => $payout,
            'payment_method'      => $data['payment_method'],
            'mobile_number'       => !empty($data['mobile_number']) ? sanitizePhone($data['mobile_number']) : null,
            'transaction_id'      => $txnId,
            'status'              => 'completed',   // Simulated — instant success
            'paid_at'             => date('Y-m-d H:i:s'),
        ]);

        return [
            'success'        => true,
            'transaction_id' => $txnId,
            'payment_id'     => $paymentId,
            'commission'     => $commission,
            'payout'         => $payout,
        ];
    }

    /**
     * Get a payment record for a specific booking.
     *
     * @param int $bookingId
     * @return array|null
     */
    public function getByBooking(int $bookingId): ?array
    {
        $result = $this->findOneBy('booking_id', $bookingId);
        return $result ?: null;
    }

    /**
     * Get full payment history for a customer with booking details.
     *
     * @param int $customerId
     * @return array
     */
    public function getCustomerHistory(int $customerId): array
    {
        $sql = "SELECT p.*, b.booking_ref, b.pickup_address, b.delivery_address,
                       l.name AS lorry_name, l.lorry_type
                FROM payments p
                INNER JOIN bookings b ON b.id = p.booking_id
                INNER JOIN lorries l ON l.id = b.lorry_id
                WHERE p.payer_id = :customer_id
                ORDER BY p.created_at DESC";

        return $this->rawQuery($sql, [':customer_id' => $customerId]);
    }

    /**
     * Get total earnings and commission for a lorry owner.
     *
     * @param int $ownerId
     * @return array ['total_earned', 'commission_paid', 'total_trips']
     */
    public function getOwnerEarnings(int $ownerId): array
    {
        $sql = "SELECT
                    COALESCE(SUM(p.owner_payout), 0)        AS total_earned,
                    COALESCE(SUM(p.platform_commission), 0) AS commission_paid,
                    COUNT(p.id)                             AS total_trips
                FROM payments p
                INNER JOIN bookings b ON b.id = p.booking_id
                INNER JOIN lorries l ON l.id = b.lorry_id AND l.owner_id = :owner_id
                WHERE p.status = 'completed'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':owner_id' => $ownerId]);
        return $stmt->fetch() ?: ['total_earned' => 0, 'commission_paid' => 0, 'total_trips' => 0];
    }
}

