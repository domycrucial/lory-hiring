<?php
/**
 * app/models/Review.php
 * Review data model.
 */

declare(strict_types=1);

class Review extends BaseModel
{
    protected string $table = 'reviews';

    /**
     * Get review for a booking.
     */
    public function getByBooking(int $bookingId): ?array
    {
        $result = $this->findOneBy('booking_id', $bookingId);
        return $result ?: null;
    }
}
