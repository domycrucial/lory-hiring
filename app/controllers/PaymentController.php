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

        if ($booking['status'] !== 'accepted') {
            flashMessage('error', 'You can only pay for accepted bookings. / Unaweza kulipia booking zilizokubaliwa tu.');
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

        if (!$booking || (int)$booking['customer_id'] !== currentUserId()) {
            flashMessage('error', 'Booking not found.');
            redirect(APP_URL . '/bookings/mine');
        }

        if ($booking['status'] !== 'accepted') {
            flashMessage('error', 'You can only pay for bookings that have been accepted by the owner. / Unaweza kulipia uhifadhi uliokubaliwa tu.');
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
            // Credit the owner's wallet
            $lorryModel = new Lorry();
            $lorry = $lorryModel->findById((int)$booking['lorry_id']);
            if ($lorry) {
                $ownerId = (int)$lorry['owner_id'];
                $userModel = new User();
                $userModel->creditWallet($ownerId, (float)$result['payout']);
                
                // Notify the owner
                $notifModel = new Notification();
                $notifModel->notify(
                    $ownerId,
                    'payment_received',
                    'Payment Received / Malipo Yamepokelewa',
                    'You have received TZS ' . number_format((float)$result['payout'], 0) . ' for booking ' . $booking['booking_ref'] . '.',
                    APP_URL . '/bookings/owner'
                );

                // Notify the customer
                $notifModel->notify(
                    currentUserId(),
                    'payment_success',
                    'Payment Successful / Malipo Yamefanikiwa',
                    'Your payment of TZS ' . number_format((float)$booking['quoted_price'], 0) . ' for booking ' . $booking['booking_ref'] . ' was successful.',
                    APP_URL . '/bookings/mine'
                );
            }

            logSystemAction('payment_completed', "Customer paid TZS " . number_format((float)$booking['quoted_price'], 0) . " for booking ID: {$bookingId}. Txn: {$result['transaction_id']}");
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
        if (currentUserRole() === 'lorry_owner') {
            $payments = $paymentModel->getOwnerHistory(currentUserId());
        } else {
            $payments = $paymentModel->getCustomerHistory(currentUserId());
        }

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/payments/history.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * GET /api/v1/wallet/balance — Fetch user balance via AJAX.
     */
    public function apiBalance(): void
    {
        requireLogin();
        $userModel = new User();
        $user = $userModel->findById(currentUserId());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'balance' => (float)($user['wallet_balance'] ?? 0.00)
        ]);
        exit;
    }

    /**
     * POST /api/v1/wallet/deposit — Top up user wallet (Simulated).
     */
    public function apiDeposit(): void
    {
        requireLogin();
        $amount = (float)post('amount');
        header('Content-Type: application/json');

        if ($amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid deposit amount.']);
            exit;
        }

        $userModel = new User();
        $success = $userModel->creditWallet(currentUserId(), $amount);
        $user = $userModel->findById(currentUserId());

        echo json_encode([
            'success' => $success,
            'balance' => (float)($user['wallet_balance'] ?? 0.00),
            'message' => 'Deposited TZS ' . number_format($amount, 0) . ' successfully. / Pesa zimewekwa kwa mafanikio!'
        ]);
        exit;
    }

    /**
     * POST /api/v1/payments/checkout-ajax — Secure booking payment simulation via AJAX.
     */
    public function checkoutAjax(): void
    {
        requireRole('customer');
        
        $bookingId = postInt('booking_id');
        $method    = post('payment_method'); // 'wallet' or 'mpesa'
        $phone     = sanitizePhone(post('phone'));

        $bookingModel = new Booking();
        $booking = $bookingModel->findById($bookingId);

        header('Content-Type: application/json');

        if (!$booking || (int)$booking['customer_id'] !== currentUserId()) {
            echo json_encode(['success' => false, 'message' => 'Booking not found.']);
            exit;
        }

        if ($booking['status'] !== 'accepted') {
            echo json_encode(['success' => false, 'message' => 'You can only pay for accepted bookings. / Unaweza kulipia booking zilizokubaliwa tu.']);
            exit;
        }

        $amount = (float)$booking['quoted_price'];

        // If paying with Wallet, check balance and deduct
        if ($method === 'wallet') {
            $userModel = new User();
            $customer = $userModel->findById(currentUserId());
            $customerBalance = (float)($customer['wallet_balance'] ?? 0.00);

            if ($customerBalance < $amount) {
                echo json_encode(['success' => false, 'message' => 'Insufficient wallet balance. Please top up first. / Salio lako la wallet halitoshi.']);
                exit;
            }

            if (!$userModel->debitWallet(currentUserId(), $amount)) {
                echo json_encode(['success' => false, 'message' => 'Failed to deduct from wallet. Please try again. / Hitilafu ya kutoa fedha kwenye wallet.']);
                exit;
            }
        }

        // Process simulated payment with 10% commission rate applied automatically in Payment model
        $paymentModel = new Payment();
        $result = $paymentModel->processSimulated([
            'booking_id'     => $bookingId,
            'payer_id'       => currentUserId(),
            'amount'         => $amount,
            'payment_method' => $method === 'wallet' ? 'wallet' : 'mpesa',
            'mobile_number'  => $method === 'wallet' ? null : $phone,
        ]);

        if ($result['success']) {
            // Update booking status from accepted to paid/accepted
            // In the lifecycle of the system, payment is logged in payments table.
            logSystemAction('payment_completed', "Customer paid TZS " . number_format($amount, 0) . " via " . strtoupper($method) . " for booking ID: {$bookingId}. Txn: {$result['transaction_id']}");
            
            // Notify the owner
            $lorryModel = new Lorry();
            $lorry = $lorryModel->findById((int)$booking['lorry_id']);
            if ($lorry) {
                $ownerId = (int)$lorry['owner_id'];
                $notifModel = new Notification();
                $notifModel->notify(
                    $ownerId,
                    'payment_received',
                    'Payment Received / Malipo Yamepokelewa',
                    'You have received TZS ' . number_format((float)$result['payout'], 0) . ' (after 10% commission) for booking ' . $booking['booking_ref'] . '.',
                    APP_URL . '/bookings/owner'
                );

                // Notify the customer
                $notifModel->notify(
                    currentUserId(),
                    'payment_success',
                    'Payment Successful / Malipo Yamefanikiwa',
                    'Your payment of TZS ' . number_format($amount, 0) . ' for booking ' . $booking['booking_ref'] . ' was successful.',
                    APP_URL . '/bookings/mine'
                );
            }

            echo json_encode([
                'success' => true,
                'transaction_id' => $result['transaction_id'],
                'message' => 'Payment processed successfully! / Malipo yamefanikiwa!'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Payment processing failed. / Malipo yamefeli.']);
        }
        exit;
    }
}
