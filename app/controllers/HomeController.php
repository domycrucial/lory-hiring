<?php
/**
 * app/controllers/HomeController.php
 * Handles homepage and user dashboards.
 */

declare(strict_types=1);

class HomeController
{
    /**
     * GET / — Public homepage with hero, search CTA, and featured lorries.
     */
    public function index(): void
    {
        $pageTitle = 'Home';
        $currentPage = 'home';

        // Get a few featured/approved lorries for the homepage
        $lorryModel = new Lorry();
        $featuredLorries = $lorryModel->search([], 'avg_rating', 6, 0);

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/home.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * GET /dashboard/customer — Customer dashboard.
     */
    public function customerDashboard(): void
    {
        requireRole('customer');

        $pageTitle = 'Customer Dashboard';
        $currentPage = 'dashboard';
        $userId = currentUserId();

        $bookingModel = new Booking();
        $allBookings = $bookingModel->getByCustomer($userId);
        $recentBookings = array_slice($allBookings, 0, 5);

        $userModel = new User();
        $userData  = $userModel->findById($userId);
        $userWalletBalance = (float)($userData['wallet_balance'] ?? 0.00);

        // Simple stats
        $stats = ['total_bookings' => count($allBookings), 'pending' => 0, 'completed' => 0, 'wallet_balance' => $userWalletBalance];
        $activeBookings = [];
        foreach ($allBookings as $b) {
            if ($b['status'] === 'pending') $stats['pending']++;
            if ($b['status'] === 'completed') $stats['completed']++;
            if (in_array($b['status'], ['accepted', 'in_transit'])) {
                $activeBookings[] = $b;
            }
        }

        // Fetch available approved lorries for background map pins
        $lorryModel = new Lorry();
        $availableLorries = $lorryModel->rawQuery("SELECT l.name, l.price_per_km, o.full_name as owner_name, o.phone as owner_phone
                                                   FROM lorries l 
                                                   INNER JOIN users o ON o.id = l.owner_id 
                                                   WHERE l.approval_status = 'approved'");

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/dashboard_customer.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * GET /dashboard/owner — Lorry owner dashboard.
     */
    public function ownerDashboard(): void
    {
        requireRole('lorry_owner');

        $pageTitle = 'Owner Dashboard';
        $currentPage = 'dashboard';
        $userId = currentUserId();

        $lorryModel   = new Lorry();
        $bookingModel = new Booking();
        $userModel    = new User();

        $myLorries      = $lorryModel->getByOwner($userId);
        $allBookings    = $bookingModel->getByOwner($userId);
        $recentBookings = array_slice($allBookings, 0, 5);
        $currentUserData = $userModel->findById($userId);

        $stats = [
            'total_lorries'  => count($myLorries),
            'total_bookings' => count($allBookings),
            'pending'        => 0,
            'wallet_balance' => (float)($currentUserData['wallet_balance'] ?? 0.00),
        ];
        foreach ($allBookings as $b) {
            if ($b['status'] === 'pending') $stats['pending']++;
        }

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/dashboard_owner.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * GET /lang/switch/{lang} — Switch language preference.
     */
    public function switchLang(array $params): void
    {
        $lang = $params['lang'] ?? 'en';
        if (in_array($lang, ['en', 'sw'], true)) {
            $_SESSION['user_lang'] = $lang;
            // If logged in, also update in DB
            if (isLoggedIn()) {
                $userModel = new User();
                $userModel->update(currentUserId(), ['preferred_lang' => $lang]);
            }
        }
        
        // Redirect back to referring page, or home if referer is empty
        $referer = $_SERVER['HTTP_REFERER'] ?? APP_URL . '/';
        redirect($referer);
    }
}
