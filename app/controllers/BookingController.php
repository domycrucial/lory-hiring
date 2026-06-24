<?php
/**
 * app/controllers/BookingController.php
 * Handles the full booking lifecycle.
 */

declare(strict_types=1);

class BookingController
{
    /**
     * GET /bookings/create/{lorry_id} — Show booking form.
     */
    public function createForm(array $params): void
    {
        requireRole('customer');
        $lorryId = (int)($params['lorry_id'] ?? 0);
        $lorryModel = new Lorry();
        $lorry = $lorryModel->findById($lorryId);

        if (!$lorry || ($lorry['approval_status'] ?? '') !== 'approved') {
            flashMessage('error', 'Lorry not available for booking.');
            redirect(APP_URL . '/lorries/search');
        }

        $pageTitle = 'Book Lorry';
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/bookings/create.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /bookings/create — Process new booking.
     */
    public function create(): void
    {
        requireRole('customer');
        verifyCsrf();

        $lorryId      = postInt('lorry_id');
        $pickupAddr   = sanitizeString(post('pickup_address'));
        $deliveryAddr = sanitizeString(post('delivery_address'));
        $distance     = (float)post('distance_km');
        $pickupDate   = post('pickup_date');

        $lorryModel = new Lorry();
        $lorry = $lorryModel->findById($lorryId);

        if (!$lorry) {
            flashMessage('error', 'Lorry not found.');
            redirect(APP_URL . '/lorries/search');
        }

        // Calculate total price
        $pricePerKm = (float)($lorry['price_per_km'] ?? 0);
        $totalPrice = $pricePerKm * max($distance, 1);

        $bookingModel = new Booking();
        $bookingId = $bookingModel->createBooking([
            'customer_id'       => currentUserId(),
            'lorry_id'          => $lorryId,
            'pickup_address'    => $pickupAddr,
            'delivery_address'  => $deliveryAddr,
            'distance_km'       => $distance,
            'preferred_date'    => $pickupDate,
            'goods_description' => sanitizeString(post('goods_description', 'General cargo')),
            'quoted_price'      => $totalPrice,
        ]);

        if ($bookingId) {
            flashMessage('success', 'Booking created! Waiting for owner confirmation. / Uhifadhi umeundwa!');
            redirect(APP_URL . '/bookings/mine');
        } else {
            flashMessage('error', 'Booking failed. Try again.');
            redirect(APP_URL . '/bookings/create/' . $lorryId);
        }
    }

    /**
     * GET /bookings/mine — Customer's booking list.
     */
    public function myBookings(): void
    {
        requireRole('customer');
        $pageTitle = 'My Bookings';

        $bookingModel = new Booking();
        $bookings = $bookingModel->getByCustomer(currentUserId());

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/bookings/list.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * GET /bookings/owner — Owner's received bookings.
     */
    public function ownerBookings(): void
    {
        requireRole('lorry_owner');
        $pageTitle = 'Booking Requests';

        $bookingModel = new Booking();
        $bookings = $bookingModel->getByOwner(currentUserId());

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/bookings/list.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * GET /bookings/detail/{id} — Booking detail page.
     */
    public function detail(array $params): void
    {
        requireLogin();
        $id = (int)($params['id'] ?? 0);
        $bookingModel = new Booking();
        $booking = $bookingModel->getFullDetail($id);

        if (!$booking) {
            // Fallback to simple findById
            $booking = $bookingModel->findById($id);
        }

        if (!$booking) {
            flashMessage('error', 'Booking not found.');
            redirect(APP_URL . '/');
        }

        // Only allow access to the customer or owner of this booking
        $uid = currentUserId();
        $ownerId = (int)($booking['owner_id'] ?? 0);
        if ((int)$booking['customer_id'] !== $uid && $ownerId !== $uid && !in_array(currentUserRole(), ['admin', 'super_admin'])) {
            flashMessage('error', 'Access denied.');
            redirect(APP_URL . '/');
        }

        $pageTitle = 'Booking #' . e($booking['booking_ref'] ?? $id);
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/bookings/detail.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /bookings/{id}/accept — Owner accepts booking.
     */
    public function accept(array $params): void
    {
        requireRole('lorry_owner');
        verifyCsrf();
        $id = (int)($params['id'] ?? 0);
        $bookingModel = new Booking();

        if ($bookingModel->accept($id, currentUserId())) {
            flashMessage('success', 'Booking accepted! / Uhifadhi umekubaliwa!');
        } else {
            flashMessage('error', 'Could not accept booking.');
        }
        redirect(APP_URL . '/bookings/owner');
    }

    /**
     * POST /bookings/{id}/decline — Owner declines booking.
     */
    public function decline(array $params): void
    {
        requireRole('lorry_owner');
        verifyCsrf();
        $id = (int)($params['id'] ?? 0);
        $bookingModel = new Booking();

        $bookingModel->decline($id, currentUserId(), 'Declined by owner');
        flashMessage('info', 'Booking declined.');
        redirect(APP_URL . '/bookings/owner');
    }

    /**
     * POST /bookings/{id}/complete — Mark booking as completed.
     */
    public function complete(array $params): void
    {
        requireRole('lorry_owner');
        verifyCsrf();
        $id = (int)($params['id'] ?? 0);
        $bookingModel = new Booking();

        if ($bookingModel->complete($id, currentUserId())) {
            flashMessage('success', 'Booking completed! / Uhifadhi umekamilika!');
        } else {
            flashMessage('error', 'Could not complete booking.');
        }
        redirect(APP_URL . '/bookings/owner');
    }

    /**
     * POST /bookings/{id}/cancel — Customer cancels booking.
     */
    public function cancel(array $params): void
    {
        requireRole('customer');
        verifyCsrf();
        $id = (int)($params['id'] ?? 0);
        $bookingModel = new Booking();

        if ($bookingModel->cancelByCustomer($id, currentUserId())) {
            flashMessage('info', 'Booking cancelled. / Uhifadhi umefutwa.');
        }
        redirect(APP_URL . '/bookings/mine');
    }
}
