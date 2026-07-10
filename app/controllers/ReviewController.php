<?php
/**
 * app/controllers/ReviewController.php
 * Handles creating and saving customer reviews.
 */

declare(strict_types=1);

class ReviewController
{
    public function __construct()
    {
        requireRole('customer');
    }

    /**
     * GET /bookings/{booking_id}/review — Show review form.
     */
    public function create(array $params): void
    {
        $bookingId = (int)($params['booking_id'] ?? 0);
        
        $bookingModel = new Booking();
        $booking = $bookingModel->findById($bookingId);
        
        if (!$booking || (int)$booking['customer_id'] !== currentUserId()) {
            flashMessage('error', 'Booking not found.');
            redirect(APP_URL . '/bookings/mine');
        }

        if ($booking['status'] !== 'completed') {
            flashMessage('error', 'You can only review completed trips.');
            redirect(APP_URL . '/bookings/mine');
        }

        // Check if paid
        $paymentModel = new Payment();
        $payment = $paymentModel->getByBooking($bookingId);
        if (!$payment || $payment['status'] !== 'completed') {
            flashMessage('error', 'You must complete the payment before reviewing.');
            redirect(APP_URL . '/bookings/mine');
        }

        // Check if already reviewed
        $reviewModel = new Review();
        if ($reviewModel->getByBooking($bookingId)) {
            flashMessage('error', 'You have already reviewed this booking.');
            redirect(APP_URL . '/bookings/mine');
        }

        $pageTitle = 'Leave a Review';
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/reviews/create.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /bookings/{booking_id}/review — Store review.
     */
    public function store(array $params): void
    {
        verifyCsrf();
        $bookingId = (int)($params['booking_id'] ?? 0);
        $rating = postInt('rating');
        $comment = sanitizeString(post('comment'));

        if ($rating < 1 || $rating > 5) {
            flashMessage('error', 'Rating must be between 1 and 5 stars.');
            redirect(APP_URL . "/bookings/{$bookingId}/review");
        }

        $bookingModel = new Booking();
        $booking = $bookingModel->findById($bookingId);
        
        if (!$booking || (int)$booking['customer_id'] !== currentUserId()) {
            flashMessage('error', 'Booking not found.');
            redirect(APP_URL . '/bookings/mine');
        }

        if ($booking['status'] !== 'completed') {
            flashMessage('error', 'You can only review completed trips.');
            redirect(APP_URL . '/bookings/mine');
        }

        // Check if paid
        $paymentModel = new Payment();
        $payment = $paymentModel->getByBooking($bookingId);
        if (!$payment || $payment['status'] !== 'completed') {
            flashMessage('error', 'You must complete the payment before reviewing.');
            redirect(APP_URL . '/bookings/mine');
        }

        // Check if already reviewed
        $reviewModel = new Review();
        if ($reviewModel->getByBooking($bookingId)) {
            flashMessage('error', 'You have already reviewed this booking.');
            redirect(APP_URL . '/bookings/mine');
        }

        // Save review
        $reviewId = $reviewModel->create([
            'booking_id' => $bookingId,
            'reviewer_id' => currentUserId(),
            'lorry_id' => (int)$booking['lorry_id'],
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($reviewId) {
            // Update lorry average rating cache
            $lorryModel = new Lorry();
            $lorryModel->refreshAvgRating((int)$booking['lorry_id']);

            // Notify owner
            $lorry = $lorryModel->findById((int)$booking['lorry_id']);
            if ($lorry) {
                $notifModel = new Notification();
                $notifModel->notify(
                    (int)$lorry['owner_id'],
                    'review_received',
                    'New Review Received / Maoni Mapya',
                    'A customer left a ' . $rating . '-star review for your lorry ' . $lorry['name'] . '.',
                    APP_URL . '/bookings/owner'
                );
            }

            flashMessage('success', 'Thank you for your feedback! / Asante kwa maoni yako!');
            redirect(APP_URL . '/bookings/mine');
        } else {
            flashMessage('error', 'Failed to save review.');
            redirect(APP_URL . "/bookings/{$bookingId}/review");
        }
    }
}
