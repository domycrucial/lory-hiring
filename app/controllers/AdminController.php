<?php
/**
 * app/controllers/AdminController.php
 * Handles administration actions.
 */

declare(strict_types=1);

class AdminController
{
    public function __construct()
    {
        // Enforce admin/super_admin roles
        requireRole(['admin', 'super_admin']);
    }

    /**
     * GET /admin — Stats and Overview dashboard.
     */
    public function index(): void
    {
        $userModel = new User();
        $lorryModel = new Lorry();
        $bookingModel = new Booking();
        $withdrawalModel = new Withdrawal();

        $userStats = $userModel->getUserStats();
        $lorryStats = [
            'active' => $lorryModel->countActive(),
            'pending' => count($lorryModel->getPendingApprovals())
        ];
        $bookingStats = $bookingModel->getStats();
        $pendingWithdrawals = $withdrawalModel->getPending();

        // Retrieve statistics data for the dashboard charts
        $bookingsPerDay = $bookingModel->getBookingsPerDay();
        $bookingsStatus = $bookingModel->getBookingsStatusStats();

        // Retrieve active bookings for live tracking map
        $sqlActiveBookings = "SELECT b.*, u.full_name AS customer_name, l.name AS lorry_name, l.plate_number,
                                     o.full_name AS owner_name, o.phone AS owner_phone,
                                     p.photo_path AS lorry_photo
                              FROM bookings b
                              INNER JOIN users u ON u.id = b.customer_id
                              INNER JOIN lorries l ON l.id = b.lorry_id
                              INNER JOIN users o ON o.id = l.owner_id
                              LEFT JOIN lorry_photos p ON p.lorry_id = l.id AND p.is_primary = 1
                              WHERE b.status IN ('accepted', 'in_transit')";
        $activeBookings = $bookingModel->rawQuery($sqlActiveBookings);

        $pageTitle = 'Admin Dashboard';
        $currentPage = 'admin_dashboard';

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/admin/dashboard.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * GET /admin/lorries — Lorry approval queue.
     */
    public function lorries(): void
    {
        $lorryModel = new Lorry();
        $pendingLorries = $lorryModel->getPendingApprovals();

        $pageTitle = 'Lorry Approvals';
        $currentPage = 'admin_lorries';

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/admin/lorries.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /admin/lorries/{id}/approve
     */
    public function approve(array $params): void
    {
        verifyCsrf();
        $id = (int)($params['id'] ?? 0);
        $lorryModel = new Lorry();
        $lorry = $lorryModel->findById($id);

        if ($lorry && $lorryModel->approve($id)) {
            logSystemAction('admin_lorry_approved', "Admin approved lorry listing ID: {$id}, Plate: {$lorry['plate_number']}");
            // Notify the owner
            $notifModel = new Notification();
            $notifModel->notify(
                (int)$lorry['owner_id'],
                'lorry_approved',
                'Lorry Approved / Lori Limepitishwa',
                'Your lorry ' . $lorry['name'] . ' (' . $lorry['plate_number'] . ') has been approved by the administrator.',
                APP_URL . '/lorries/mine'
            );
            flashMessage('success', 'Lorry approved successfully. / Lori limepitishwa.');
        } else {
            flashMessage('error', 'Failed to approve lorry.');
        }
        redirect(APP_URL . '/admin/lorries');
    }

    /**
     * POST /admin/lorries/{id}/reject
     */
    public function reject(array $params): void
    {
        verifyCsrf();
        $id = (int)($params['id'] ?? 0);
        $reason = post('reason');
        if (empty($reason)) {
            flashMessage('error', 'Rejection reason is required.');
            redirect(APP_URL . '/admin/lorries');
        }

        $lorryModel = new Lorry();
        $lorry = $lorryModel->findById($id);

        if ($lorry && $lorryModel->reject($id, $reason)) {
            logSystemAction('admin_lorry_rejected', "Admin rejected lorry listing ID: {$id}, Reason: {$reason}");
            // Notify owner
            $notifModel = new Notification();
            $notifModel->notify(
                (int)$lorry['owner_id'],
                'lorry_rejected',
                'Lorry Rejected / Lori Limekataliwa',
                'Your lorry ' . $lorry['name'] . ' (' . $lorry['plate_number'] . ') was rejected. Reason: ' . $reason,
                APP_URL . '/lorries/mine'
            );
            flashMessage('warning', 'Lorry listing rejected. / Lori limekataliwa.');
        } else {
            flashMessage('error', 'Failed to reject lorry.');
        }
        redirect(APP_URL . '/admin/lorries');
    }

    /**
     * GET /admin/users — User management.
     */
    public function users(): void
    {
        $userModel = new User();
        $roleFilter = get('role', '');
        $search = get('search', '');

        // Enforce pagination limit of 5 records
        $page = max(1, (int)get('page', '1'));
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $usersList = $userModel->adminGetUsers($roleFilter, $search, $limit, $offset);
        $totalUsers = $userModel->countUsers($roleFilter, $search);
        $totalPages = (int)ceil($totalUsers / $limit);

        $pageTitle = 'User Management';
        $currentPage = 'admin_users';

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/admin/users.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /admin/users/{id}/suspend
     */
    public function suspend(array $params): void
    {
        verifyCsrf();
        $id = (int)($params['id'] ?? 0);
        $userModel = new User();
        $user = $userModel->findById($id);

        if ($user) {
            $userModel->update($id, ['status' => 'suspended']);
            logSystemAction('admin_user_suspended', "Admin suspended user ID: {$id}, Email: {$user['email']}");
            flashMessage('warning', 'User account suspended. / Mtumiaji amesimamishwa.');
        } else {
            flashMessage('error', 'User not found.');
        }
        redirect(APP_URL . '/admin/users');
    }

    /**
     * POST /admin/users/{id}/activate
     */
    public function activate(array $params): void
    {
        verifyCsrf();
        $id = (int)($params['id'] ?? 0);
        $userModel = new User();
        $user = $userModel->findById($id);

        if ($user) {
            $userModel->update($id, ['status' => 'active']);
            logSystemAction('admin_user_activated', "Admin activated user ID: {$id}, Email: {$user['email']}");
            flashMessage('success', 'User account activated. / Mtumiaji ameruhusiwa.');
        } else {
            flashMessage('error', 'User not found.');
        }
        redirect(APP_URL . '/admin/users');
    }

    /**
     * GET /admin/bookings — Bookings monitoring.
     */
    public function bookings(): void
    {
        $bookingModel = new Booking();
        $sql = "SELECT b.*, u.full_name AS customer_name, l.name AS lorry_name, l.plate_number,
                       o.full_name AS owner_name, o.phone AS owner_phone
                FROM bookings b
                INNER JOIN users u ON u.id = b.customer_id
                INNER JOIN lorries l ON l.id = b.lorry_id
                INNER JOIN users o ON o.id = l.owner_id
                ORDER BY b.created_at DESC";
        $bookingsList = $bookingModel->rawQuery($sql);

        $pageTitle = 'Booking Oversight';
        $currentPage = 'admin_bookings';

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/admin/bookings.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * GET /admin/payments — Revenue overview.
     */
    public function payments(): void
    {
        $paymentModel = new Payment();
        $sql = "SELECT p.*, b.booking_ref, u.full_name AS customer_name
                FROM payments p
                INNER JOIN bookings b ON b.id = p.booking_id
                INNER JOIN users u ON u.id = p.payer_id
                ORDER BY p.created_at DESC";
        $paymentsList = $paymentModel->rawQuery($sql);

        $pageTitle = 'Revenue & Payments';
        $currentPage = 'admin_payments';

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/admin/payments.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /admin/withdrawals/{id}/approve
     */
    public function approveWithdrawal(array $params): void
    {
        verifyCsrf();
        $id = (int)($params['id'] ?? 0);
        $withdrawalModel = new Withdrawal();
        $w = $withdrawalModel->findById($id);

        if ($w && $w['status'] === 'pending') {
            $txnId = 'PAY-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $withdrawalModel->update($id, [
                'status' => 'completed',
                'mpesa_ref' => $txnId,
                'processed_at' => date('Y-m-d H:i:s'),
                'notes' => 'Approved and processed by Admin.'
            ]);
            logSystemAction('admin_withdrawal_approved', "Admin approved withdrawal ID: {$id}, Amount: TZS " . number_format((float)$w['amount']));

            // Notify owner
            $notifModel = new Notification();
            $notifModel->notify(
                (int)$w['owner_id'],
                'withdrawal_completed',
                'Withdrawal Successful / Kutoa Pesa Kumekamilika',
                'Your withdrawal request of TZS ' . number_format((float)$w['amount'], 0) . ' has been approved. Transaction: ' . $txnId,
                APP_URL . '/wallet'
            );

            flashMessage('success', 'Withdrawal request approved and processed.');
        } else {
            flashMessage('error', 'Withdrawal request not found or not pending.');
        }
        redirect(APP_URL . '/admin');
    }

    /**
     * POST /admin/withdrawals/{id}/reject
     */
    public function rejectWithdrawal(array $params): void
    {
        verifyCsrf();
        $id = (int)($params['id'] ?? 0);
        $reason = post('reason', 'Rejected by administrator.');
        $withdrawalModel = new Withdrawal();
        $w = $withdrawalModel->findById($id);

        if ($w && $w['status'] === 'pending') {
            // Refund the amount back to owner wallet!
            $userModel = new User();
            $userModel->creditWallet((int)$w['owner_id'], (float)$w['amount']);

            $withdrawalModel->update($id, [
                'status' => 'rejected',
                'processed_at' => date('Y-m-d H:i:s'),
                'notes' => $reason
            ]);
            logSystemAction('admin_withdrawal_rejected', "Admin rejected withdrawal ID: {$id}, Amount: TZS " . number_format((float)$w['amount']) . ", Reason: {$reason}");

            // Notify owner
            $notifModel = new Notification();
            $notifModel->notify(
                (int)$w['owner_id'],
                'withdrawal_rejected',
                'Withdrawal Rejected / Kutoa Pesa Kumekataliwa',
                'Your withdrawal request of TZS ' . number_format((float)$w['amount'], 0) . ' was rejected. Reason: ' . $reason,
                APP_URL . '/wallet'
            );

            flashMessage('warning', 'Withdrawal request rejected. Funds returned to owner wallet.');
        } else {
            flashMessage('error', 'Withdrawal request not found or not pending.');
        }
        redirect(APP_URL . '/admin');
    }

    /**
     * GET /admin/logs — Paginated real-time audit logs view.
     */
    public function systemLogs(): void
    {
        $db = getDB();

        // Enforce 5 logs per page
        $page = max(1, (int)get('page', '1'));
        $limit = 5;
        $offset = ($page - 1) * $limit;

        // Query total log count
        $totalLogs = (int)$db->query("SELECT COUNT(*) FROM system_logs")->fetchColumn();
        $totalPages = (int)ceil($totalLogs / $limit);

        // Fetch logs with associated user email/name details
        $logs = $db->query("
            SELECT l.*, u.full_name AS user_name, u.email AS user_email 
            FROM system_logs l 
            LEFT JOIN users u ON u.id = l.user_id 
            ORDER BY l.created_at DESC 
            LIMIT {$limit} OFFSET {$offset}
        ")->fetchAll();

        $pageTitle = 'System Logs';
        $currentPage = 'admin_logs';

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/admin/logs.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /admin/users/create — Admin adds a new user directly.
     */
    public function createUser(): void
    {
        verifyCsrf();

        $fullName = sanitizeString(post('full_name'));
        $email    = sanitizeEmail(post('email'));
        $phone    = sanitizePhone(post('phone'));
        $password = post('password');
        $role     = post('role');

        $errors = [];

        if (empty($fullName)) {
            $errors[] = 'Full name is required.';
        }
        if (!$email) {
            $errors[] = 'Valid email is required.';
        }
        if (empty($phone)) {
            $errors[] = 'Phone number is required.';
        }
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if (!in_array($role, ['customer', 'lorry_owner', 'admin'])) {
            $errors[] = 'Invalid role selection.';
        }

        if (!empty($errors)) {
            flashMessage('error', implode(' ', $errors));
            redirect(APP_URL . '/admin/users');
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            flashMessage('error', 'User with this email already exists.');
            redirect(APP_URL . '/admin/users');
        }

        $userId = $userModel->create([
            'full_name'     => $fullName,
            'email'         => $email,
            'phone'         => $phone,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role'          => $role,
            'status'        => 'active',
        ]);

        if ($userId) {
            logSystemAction('admin_user_created', "Admin created user account: {$email} (Role: {$role})");
            flashMessage('success', 'User added successfully! / Mtumiaji ameongezwa!');
        } else {
            flashMessage('error', 'Failed to create user. Please try again.');
        }

        redirect(APP_URL . '/admin/users');
    }
}
