<?php
/**
 * app/controllers/WalletController.php
 * Handles wallet actions for lorry owners.
 */

declare(strict_types=1);

class WalletController
{
    public function __construct()
    {
        requireLogin();
    }

    /**
     * GET /wallet — Display wallet details and history.
     */
    public function index(): void
    {
        $userId = currentUserId();
        
        $userModel = new User();
        $user = $userModel->findById($userId);
        
        $paymentModel = new Payment();

        $pageTitle = 'My Wallet';
        $currentPage = 'wallet';

        require_once VIEW_PATH . '/layouts/header.php';
        if (currentUserRole() === 'customer') {
            $transactions = $paymentModel->getCustomerHistory($userId);
            require_once VIEW_PATH . '/pages/wallet/customer.php';
        } else {
            $withdrawalModel = new Withdrawal();
            $withdrawals = $withdrawalModel->getHistory($userId);
            $earnings = $paymentModel->getOwnerEarnings($userId);
            require_once VIEW_PATH . '/pages/wallet/index.php';
        }
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /wallet/deposit — Submit a deposit (Tigo Pesa / Bank simulation).
     */
    public function deposit(): void
    {
        verifyCsrf();
        $userId = currentUserId();
        $amount = (float)post('amount');
        $channel = post('deposit_channel') ?: 'Tigo Pesa Mobile Money';
        $reference = post('deposit_reference') ?: 'TIGO-' . strtoupper(bin2hex(random_bytes(3)));

        if ($amount < 1000) {
            flashMessage('error', 'Minimum deposit amount is TZS 1,000.');
            redirect(APP_URL . '/wallet');
        }

        $userModel = new User();
        if ($userModel->creditWallet($userId, $amount)) {
            $notifModel = new Notification();
            $notifModel->notify(
                $userId,
                'wallet_deposit_success',
                'Deposit Successful / Kuweka Pesa Imefanikiwa',
                'Your wallet has been credited with TZS ' . number_format($amount) . ' via ' . $channel . ' (' . $reference . ').',
                APP_URL . '/wallet'
            );

            logSystemAction('wallet_deposit', "User ID {$userId} deposited TZS " . number_format($amount) . " via {$channel}. Ref: {$reference}");
            flashMessage('success', 'Successfully deposited ' . formatTZS($amount) . ' into your Wallet via ' . e($channel) . ' Simulation!');
        } else {
            flashMessage('error', 'Deposit failed. Please try again.');
        }
        redirect(APP_URL . '/wallet');
    }

    /**
     * POST /wallet/withdraw — Submit a withdrawal request.
     */
    public function withdraw(): void
    {
        requireRole('lorry_owner');
        verifyCsrf();
        $userId = currentUserId();
        $amount = (float)post('amount');
        $phone = sanitizePhone(post('phone'));

        if ($amount < MIN_WITHDRAWAL) {
            flashMessage('error', 'Minimum withdrawal amount is ' . formatTZS(MIN_WITHDRAWAL) . '.');
            redirect(APP_URL . '/wallet');
        }

        if (empty($phone)) {
            flashMessage('error', 'Valid destination mobile money phone number is required.');
            redirect(APP_URL . '/wallet');
        }

        $userModel = new User();
        $user = $userModel->findById($userId);
        $currentBalance = (float)($user['wallet_balance'] ?? 0.00);

        if ($currentBalance < $amount) {
            flashMessage('error', 'Insufficient wallet balance for withdrawal.');
            redirect(APP_URL . '/wallet');
        }

        // Process debit
        if ($userModel->debitWallet($userId, $amount)) {
            // Create withdrawal request in withdrawals table
            $withdrawalModel = new Withdrawal();
            $withdrawalId = $withdrawalModel->create([
                'owner_id' => $userId,
                'amount' => $amount,
                'mobile_number' => $phone,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Notify owner
            $notifModel = new Notification();
            $notifModel->notify(
                $userId,
                'withdrawal_requested',
                'Withdrawal Requested / Ombi la Kutoa Pesa',
                'Your request to withdraw TZS ' . number_format($amount) . ' to ' . $phone . ' has been submitted.',
                APP_URL . '/wallet'
            );

            // Notify super admins
            $superAdmins = $userModel->findBy('role', 'super_admin');
            foreach ($superAdmins as $sa) {
                $notifModel->notify(
                    (int)$sa['id'],
                    'withdrawal_pending',
                    'Pending Withdrawal Request',
                    'Owner ' . $user['full_name'] . ' requested a withdrawal of ' . formatTZS($amount),
                    APP_URL . '/admin'
                );
            }

            logSystemAction('withdrawal_requested', "Owner requested withdrawal of TZS " . number_format($amount, 0) . " to {$phone}. Request ID: {$withdrawalId}");
            flashMessage('success', 'Withdrawal request submitted successfully and is pending approval.');
        } else {
            flashMessage('error', 'Transaction failed. Please try again.');
        }
        redirect(APP_URL . '/wallet');
    }
}
