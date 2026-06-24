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

        // Simple stats
        $stats = ['total_bookings' => count($allBookings), 'pending' => 0, 'completed' => 0];
        foreach ($allBookings as $b) {
            if ($b['status'] === 'pending') $stats['pending']++;
            if ($b['status'] === 'completed') $stats['completed']++;
        }

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

        $myLorries      = $lorryModel->getByOwner($userId);
        $allBookings    = $bookingModel->getByOwner($userId);
        $recentBookings = array_slice($allBookings, 0, 5);

        $stats = [
            'total_lorries'  => count($myLorries),
            'total_bookings' => count($allBookings),
            'pending'        => 0,
        ];
        foreach ($allBookings as $b) {
            if ($b['status'] === 'pending') $stats['pending']++;
        }

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/dashboard_owner.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }
}
