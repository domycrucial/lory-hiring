<?php
/**
 * app/controllers/PaymentController.php
 * Handles simulated mobile money payment processing.
 */

declare(strict_types=1);

class PaymentController
{
    /**
     * GET /payments/checkout/{booking_id} — Show payment form.
     */
    public function checkoutForm(array $params): void
    {
        requireRole('customer');
        $bookingId = (int)($params['booking_id'] ?? 0);
        $bookingModel = new Booking();
        $booking = $bookingModel->findById($bookingId);

        if (!$booking || (int)$booking['customer_id'] !== currentUserId()) {
            flashMessage('error', 'Booking not found.');
            redirect(APP_URL . '/bookings/mine');
        }

        $pageTitle = 'Checkout';
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/payments/checkout.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /payments/checkout — Process simulated payment.
     */
    public function checkout(): void
    {
        requireRole('customer');
        verifyCsrf();

        $bookingId = postInt('booking_id');
        $method    = post('payment_method');
        $phone     = sanitizePhone(post('phone'));

        $bookingModel = new Booking();
        $booking = $bookingModel->findById($bookingId);

        if (!$booking) {
            flashMessage('error', 'Booking not found.');
            redirect(APP_URL . '/bookings/mine');
        }

        // Use the Payment model's processSimulated method
        $paymentModel = new Payment();
        $result = $paymentModel->processSimulated([
            'booking_id'    => $bookingId,
            'payer_id'      => currentUserId(),
            'amount'        => (float)$booking['quoted_price'],
            'payment_method'=> $method,
            'mobile_number' => $phone,
        ]);

        if ($result['success']) {
            // Mark booking status as accepted (payment done)
            $bookingModel->update($bookingId, ['status' => 'accepted']);
            flashMessage('success', 'Payment successful! Transaction: ' . $result['transaction_id'] . ' / Malipo yamefanikiwa!');
            redirect(APP_URL . '/payments/success?txn=' . $result['transaction_id']);
        } else {
            flashMessage('error', 'Payment failed. Please try again.');
            redirect(APP_URL . '/payments/checkout/' . $bookingId);
        }
    }

    /**
     * GET /payments/success — Payment success page.
     */
    public function success(): void
    {
        requireLogin();
        $pageTitle = 'Payment Success';
        $txnId = get('txn');

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/payments/success.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * GET /payments/history — Payment history.
     */
    public function history(): void
    {
        requireLogin();
        $pageTitle = 'Payment History';

        $paymentModel = new Payment();
        $payments = $paymentModel->getCustomerHistory(currentUserId());

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/payments/history.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }
}
