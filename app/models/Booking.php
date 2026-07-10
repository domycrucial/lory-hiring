<?php
/**
 * app/models/Booking.php
 * Booking data model — manages the full booking lifecycle.
 * Status flow: pending → accepted → in_transit → completed | cancelled
 */

declare(strict_types=1);

class Booking extends BaseModel
{
    protected string $table = 'bookings';

    // ─────────────────────────────────────────────────────────
    // CREATE BOOKING
    // ─────────────────────────────────────────────────────────

    /**
     * Create a new booking request.
     * Sets auto-cancel deadline to 24 hours from now.
     *
     * @param array $data Booking form data
     * @return int New booking ID
     */
    public function createBooking(array $data): int
    {
        // Insert the booking first to get the ID for the reference number
        $id = $this->create([
            'booking_ref'       => 'TEMP',  // Will be updated below
            'customer_id'       => (int)$data['customer_id'],
            'lorry_id'          => (int)$data['lorry_id'],
            'pickup_address'    => sanitizeString($data['pickup_address']),
            'delivery_address'  => sanitizeString($data['delivery_address']),
            'pickup_lat'        => !empty($data['pickup_lat'])   ? (float)$data['pickup_lat']   : null,
            'pickup_lng'        => !empty($data['pickup_lng'])   ? (float)$data['pickup_lng']   : null,
            'delivery_lat'      => !empty($data['delivery_lat']) ? (float)$data['delivery_lat'] : null,
            'delivery_lng'      => !empty($data['delivery_lng']) ? (float)$data['delivery_lng'] : null,
            'distance_km'       => !empty($data['distance_km'])  ? (float)$data['distance_km']  : null,
            'goods_description' => sanitizeString($data['goods_description']),
            'weight_kg'         => !empty($data['weight_kg']) ? (float)$data['weight_kg'] : null,
            'preferred_date'    => $data['preferred_date'],
            'preferred_time'    => !empty($data['preferred_time']) ? $data['preferred_time'] : null,
            'quoted_price'      => (float)$data['quoted_price'],
            'status'            => 'pending',
            'auto_cancel_at'    => date('Y-m-d H:i:s', strtotime('+' . BOOKING_TIMEOUT_HOURS . ' hours')),
        ]);

        // Generate human-readable booking reference and update
        $ref = generateBookingRef($id);
        $this->update($id, ['booking_ref' => $ref]);

        return $id;
    }

    // ─────────────────────────────────────────────────────────
    // FETCH BOOKINGS
    // ─────────────────────────────────────────────────────────

    /**
     * Get a booking with full lorry and customer details.
     *
     * @param int $bookingId
     * @return array|null
     */
    public function getFullDetail(int $bookingId): ?array
    {
        $sql = "SELECT b.*,
                       u.full_name  AS customer_name,
                       u.email      AS customer_email,
                       u.phone      AS customer_phone,
                       l.name       AS lorry_name,
                       l.lorry_type,
                       l.plate_number,
                       l.price_per_km,
                       l.owner_id,
                       o.full_name  AS owner_name,
                       o.phone      AS owner_phone,
                       p.photo_path AS lorry_photo
                FROM bookings b
                INNER JOIN users u ON u.id = b.customer_id
                INNER JOIN lorries l ON l.id = b.lorry_id
                INNER JOIN users o ON o.id = l.owner_id
                LEFT JOIN lorry_photos p ON p.lorry_id = l.id AND p.is_primary = 1
                WHERE b.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $bookingId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get all bookings for a customer (for 'My Bookings' page).
     *
     * @param int    $customerId
     * @param string $status     Filter by status ('' = all)
     * @return array
     */
    public function getByCustomer(int $customerId, string $status = ''): array
    {
        $sql    = "SELECT b.*, l.name AS lorry_name, l.lorry_type, l.plate_number,
                          o.full_name AS owner_name, o.phone AS owner_phone,
                          p.photo_path AS lorry_photo
                   FROM bookings b
                   INNER JOIN lorries l ON l.id = b.lorry_id
                   INNER JOIN users o ON o.id = l.owner_id
                   LEFT JOIN lorry_photos p ON p.lorry_id = l.id AND p.is_primary = 1
                   WHERE b.customer_id = :customer_id";
        $params = [':customer_id' => $customerId];

        if (!empty($status)) {
            $sql            .= " AND b.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY b.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get all bookings for a lorry owner's lorries.
     *
     * @param int    $ownerId
     * @param string $status  Filter by status ('' = all)
     * @return array
     */
    public function getByOwner(int $ownerId, string $status = ''): array
    {
        $sql    = "SELECT b.*, l.name AS lorry_name, l.lorry_type,
                          u.full_name AS customer_name, u.phone AS customer_phone,
                          p.photo_path AS lorry_photo
                   FROM bookings b
                   INNER JOIN lorries l ON l.id = b.lorry_id
                   INNER JOIN users u ON u.id = b.customer_id
                   LEFT JOIN lorry_photos p ON p.lorry_id = l.id AND p.is_primary = 1
                   WHERE (b.status = 'pending' OR l.owner_id = :owner_id)";
        $params = [':owner_id' => $ownerId];

        if (!empty($status)) {
            $sql            .= " AND b.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY b.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────────────────
    // STATUS TRANSITIONS
    // ─────────────────────────────────────────────────────────

    /**
     * Accept a booking (owner action).
     * Validates that the booking belongs to the owner's lorry.
     *
     * @param int $bookingId
     * @param int $ownerId   Owner's user ID (authorisation check)
     * @return bool
     */
    public function accept(int $bookingId, int $ownerId): bool
    {
        // 1. Check if the booking is still pending
        $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id = :id FOR UPDATE");
        $stmt->execute([':id' => $bookingId]);
        $booking = $stmt->fetch();
        
        if (!$booking || $booking['status'] !== 'pending') {
            return false; // Already accepted or cancelled
        }
        
        // 2. Find an approved lorry belonging to this owner
        $stmtLorry = $this->db->prepare("SELECT id FROM lorries WHERE owner_id = :owner_id AND approval_status = 'approved' LIMIT 1");
        $stmtLorry->execute([':owner_id' => $ownerId]);
        $lorry = $stmtLorry->fetch();
        
        if (!$lorry) {
            return false; // Owner has no approved lorry to accept the booking
        }
        
        $assignedLorryId = $lorry['id'];
        
        // 3. Update the booking status and assign it to this owner's lorry
        $sql = "UPDATE bookings 
                SET status = 'accepted', 
                    lorry_id = :lorry_id,
                    accepted_at = NOW() 
                WHERE id = :booking_id AND status = 'pending'";
        $stmtUpdate = $this->db->prepare($sql);
        $stmtUpdate->execute([
            ':lorry_id'   => $assignedLorryId,
            ':booking_id' => $bookingId
        ]);
        
        return $stmtUpdate->rowCount() > 0;
    }

    /**
     * Decline a booking (owner action).
     *
     * @param int    $bookingId
     * @param int    $ownerId
     * @param string $reason Reason for decline
     * @return bool
     */
    public function decline(int $bookingId, int $ownerId, string $reason = ''): bool
    {
        $sql  = "UPDATE bookings 
                 SET status = 'cancelled',
                     cancellation_reason = :reason
                 WHERE id = :booking_id AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':booking_id' => $bookingId,
            ':reason'     => sanitizeString($reason),
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Mark a booking as completed (owner action).
     * Also increments the lorry's total_trips counter and resets availability to 'available'.
     *
     * @param int $bookingId
     * @param int $ownerId
     * @return bool
     */
    public function complete(int $bookingId, int $ownerId): bool
    {
        try {
            $this->db->beginTransaction();

            // Update booking status to completed
            $sql  = "UPDATE bookings b
                     INNER JOIN lorries l ON l.id = b.lorry_id AND l.owner_id = :owner_id
                     SET b.status = 'completed', b.completed_at = NOW()
                     WHERE b.id = :booking_id AND b.status IN ('accepted', 'in_transit')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':owner_id' => $ownerId, ':booking_id' => $bookingId]);

            if ($stmt->rowCount() === 0) {
                $this->db->rollBack();
                return false;
            }

            // Increment total_trips on the lorry and make it available
            $booking = $this->findById($bookingId);
            if ($booking) {
                $this->db->prepare(
                    "UPDATE lorries SET total_trips = total_trips + 1, availability_status = 'available' WHERE id = :id"
                )->execute([':id' => $booking['lorry_id']]);
            }

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('[OLHS Booking] Complete failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Start the trip (mark booking as in_transit) (owner action).
     *
     * @param int $bookingId
     * @param int $ownerId
     * @return bool
     */
    public function startTrip(int $bookingId, int $ownerId): bool
    {
        try {
            $this->db->beginTransaction();

            $sql  = "UPDATE bookings b
                     INNER JOIN lorries l ON l.id = b.lorry_id AND l.owner_id = :owner_id
                     SET b.status = 'in_transit'
                     WHERE b.id = :booking_id AND b.status = 'accepted'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':owner_id' => $ownerId, ':booking_id' => $bookingId]);

            if ($stmt->rowCount() === 0) {
                $this->db->rollBack();
                return false;
            }

            // Mark lorry as on_trip
            $booking = $this->findById($bookingId);
            if ($booking) {
                $this->db->prepare(
                    "UPDATE lorries SET availability_status = 'on_trip' WHERE id = :id"
                )->execute([':id' => $booking['lorry_id']]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('[OLHS Booking] startTrip failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel a booking (customer action).
     * Only allowed when status is 'pending' (before owner accepts).
     *
     * @param int    $bookingId
     * @param int    $customerId
     * @param string $reason
     * @return bool
     */
    public function cancelByCustomer(int $bookingId, int $customerId, string $reason = ''): bool
    {
        $sql  = "UPDATE bookings
                 SET status = 'cancelled', cancellation_reason = :reason
                 WHERE id = :id AND customer_id = :customer_id AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':reason'      => sanitizeString($reason),
            ':id'          => $bookingId,
            ':customer_id' => $customerId,
        ]);
        return $stmt->rowCount() > 0;
    }

    // ─────────────────────────────────────────────────────────
    // ADMIN & STATS
    // ─────────────────────────────────────────────────────────

    /**
     * Auto-cancel bookings that were not responded to within 24 hours.
     * Called by a cron job or pseudo-cron on each request.
     *
     * @return int Number of bookings auto-cancelled
     */
    public function autoCancelExpired(): int
    {
        $sql  = "UPDATE bookings
                 SET status = 'cancelled',
                     cancellation_reason = 'Auto-cancelled: Owner did not respond within 24 hours.'
                 WHERE status = 'pending'
                   AND auto_cancel_at IS NOT NULL
                   AND auto_cancel_at < NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Get booking statistics for the admin dashboard.
     *
     * @return array ['total', 'today', 'month', 'completed', 'pending', 'revenue_month']
     */
    public function getStats(): array
    {
        return [
            'total'         => (int)$this->rawScalar("SELECT COUNT(*) FROM bookings"),
            'today'         => (int)$this->rawScalar("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE()"),
            'month'         => (int)$this->rawScalar("SELECT COUNT(*) FROM bookings WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())"),
            'completed'     => (int)$this->rawScalar("SELECT COUNT(*) FROM bookings WHERE status = 'completed'"),
            'pending'       => (int)$this->rawScalar("SELECT COUNT(*) FROM bookings WHERE status = 'pending'"),
            'revenue_month' => (float)$this->rawScalar(
                "SELECT COALESCE(SUM(platform_commission), 0) FROM payments
                 WHERE status = 'completed'
                   AND MONTH(paid_at) = MONTH(NOW())
                   AND YEAR(paid_at) = YEAR(NOW())"
            ),
        ];
    }

    /**
     * Get booking counts grouped by date for the last 7 days.
     *
     * @return array
     */
    public function getBookingsPerDay(): array
    {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as label, COUNT(*) as count 
                FROM bookings 
                GROUP BY DATE(created_at) 
                ORDER BY DATE(created_at) ASC 
                LIMIT 7";
        return $this->rawQuery($sql);
    }

    /**
     * Get booking counts grouped by status.
     *
     * @return array
     */
    public function getBookingsStatusStats(): array
    {
        $sql = "SELECT status as label, COUNT(*) as count 
                FROM bookings 
                GROUP BY status";
        return $this->rawQuery($sql);
    }

    /**
     * Get daily booking counts by status for the last 7 days.
     *
     * @return array
     */
    public function getBookingsStatusTrend(): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m-%d') as label,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM bookings
                GROUP BY DATE(created_at)
                ORDER BY DATE(created_at) ASC
                LIMIT 7";
        return $this->rawQuery($sql);
    }
}
