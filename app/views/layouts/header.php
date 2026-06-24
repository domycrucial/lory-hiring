<?php
// Parse current request URI relative to app root
$requestUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');
$basePath = rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/');
if ($basePath !== '' && str_starts_with($requestUri, $basePath)) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestUri = trim($requestUri, '/');

// Determine if we should wrap pages in the sidebar dashboard layout
$inDashboard = isLoggedIn();
?>
<!DOCTYPE html>
<!-- HTML5 Document structure -->
<html lang="en">
<head>
    <!-- Define document character set encoding -->
    <meta charset="UTF-8">
    <!-- Responsive layout viewport setting for tablets & phones -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SEO meta description tag -->
    <meta name="description" content="Online Lorries Hiring System — Find and book lorries across Tanzania.">
    <!-- Center window page title -->
    <title><?= e($pageTitle ?? 'OLHS') ?> | <?= e(APP_NAME) ?></title>
    <!-- Preconnect to Google Fonts server API -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Load premium typography fonts (Plus Jakarta Sans only) -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Import FontAwesome icon library for reviews and navigation indicators -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Load custom layout styling custom.css stylesheet -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/custom.css">
    <!-- Inject controller specific styling stylesheets -->
    <?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="<?= APP_URL ?>/public/css/<?= $css ?>">
    <?php endforeach; endif; ?>
</head><body class="<?= !empty($inDashboard) ? 'dashboard-active' : '' ?>">
<!-- ─── Navbar ─────────────────────────────────────────────── -->
<!-- sticky top navigation bar element -->
<nav class="navbar" id="main-navbar">
    <div class="container">
        <!-- Logo brand pointing to home page -->
        <a href="<?= APP_URL ?>/" class="navbar-brand">
            <span class="logo-icon">🚛</span> OLHS
        </a>

        <!-- Mobile hamburger drawer toggle button -->
        <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- Main links list element -->
        <ul class="nav-links" id="nav-links">
            <?php if (isLoggedIn()): ?>
                <?php if ($inDashboard): ?>
                    <!-- When inside the dashboard, all navigation is in the sidebar. Show greeting only -->
                    <li>
                        <span class="user-greeting" style="font-weight: 600; color: var(--gray-700); font-size: 0.95rem; display: flex; align-items: center; gap: var(--space-2);">
                            <i class="fa-solid fa-user-circle"></i> Hello, <strong><?= e(currentUserName()) ?></strong>
                        </span>
                    </li>
                <?php else: ?>
                    <!-- Logged in, but on public pages (Home/Search/Detail) -->
                    <li><a href="<?= APP_URL ?>/" class="<?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Home</a></li>
                    <li><a href="<?= APP_URL ?>/lorries/search" class="<?= ($currentPage ?? '') === 'search' ? 'active' : '' ?>"><i class="fa-solid fa-truck"></i> Search Lorries</a></li>
                    <?php if (currentUserRole() === 'customer'): ?>
                        <li><a href="<?= APP_URL ?>/dashboard/customer"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                    <?php elseif (currentUserRole() === 'lorry_owner'): ?>
                        <li><a href="<?= APP_URL ?>/dashboard/owner"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                    <?php elseif (in_array(currentUserRole(), ['admin', 'super_admin'])): ?>
                        <li><a href="<?= APP_URL ?>/admin/"><i class="fa-solid fa-user-shield"></i> Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="<?= APP_URL ?>/auth/logout" class="btn btn-outline btn-sm"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                <?php endif; ?>
            <?php else: ?>
                <!-- Guest action buttons -->
                <li><a href="<?= APP_URL ?>/" class="<?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="<?= APP_URL ?>/lorries/search" class="<?= ($currentPage ?? '') === 'search' ? 'active' : '' ?>"><i class="fa-solid fa-truck"></i> Search Lorries</a></li>
                <li><a href="<?= APP_URL ?>/auth/login" class="btn btn-outline btn-sm"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
                <li><a href="<?= APP_URL ?>/auth/register" class="btn btn-primary btn-sm"><i class="fa-solid fa-user-plus"></i> Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- ─── Flash Messages ────────────────────────────────────── -->
<?php $flash = getFlash(); if ($flash): ?>
<div class="container mt-4">
    <div class="alert alert-<?= e($flash['type']) ?>" id="flash-alert">
        <?= e($flash['message']) ?>
    </div>
</div>
<?php endif; ?>

<!-- ─── Main Content ──────────────────────────────────────── -->
<?php if ($inDashboard): ?>
<div class="dashboard">
    <!-- Left Column: Dark Navigation Sidebar -->
    <aside class="sidebar">
        <!-- Sidebar Category Header -->
        <div class="sidebar-brand-title"><?= currentUserRole() === 'customer' ? 'Customer Portal' : 'Lorry Owner Portal' ?></div>
        
        <!-- Navigation Menu Links list -->
        <nav class="flex flex-col gap-1">
            <?php if (currentUserRole() === 'customer'): ?>
                <!-- Customer Dashboard navigation links (Dashboard is first, Home is removed, Search is inside sidebar) -->
                <a href="<?= APP_URL ?>/dashboard/customer" class="sidebar-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line" style="width: 20px;"></i> <span class="link-text">Customer Dashboard</span></a>
                <a href="<?= APP_URL ?>/lorries/search" class="sidebar-link <?= ($requestUri === 'lorries/search') ? 'active' : '' ?>"><i class="fa-solid fa-truck" style="width: 20px;"></i> <span class="link-text">Search Lorries</span></a>
                <a href="<?= APP_URL ?>/bookings/mine" class="sidebar-link <?= (str_starts_with($requestUri, 'bookings/mine') || str_starts_with($requestUri, 'bookings/detail') || str_starts_with($requestUri, 'bookings/create')) ? 'active' : '' ?>"><i class="fa-solid fa-calendar-check" style="width: 20px;"></i> <span class="link-text">My Bookings</span></a>
                <a href="<?= APP_URL ?>/payments/history" class="sidebar-link <?= ($requestUri === 'payments/history' || str_starts_with($requestUri, 'payments/success')) ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left" style="width: 20px;"></i> <span class="link-text">Payment History</span></a>
            <?php else: ?>
                <!-- Owner Dashboard navigation links (Dashboard is first, Home is removed, Search is inside sidebar) -->
                <a href="<?= APP_URL ?>/dashboard/owner" class="sidebar-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line" style="width: 20px;"></i> <span class="link-text">Owner Dashboard</span></a>
                <a href="<?= APP_URL ?>/lorries/search" class="sidebar-link <?= ($requestUri === 'lorries/search') ? 'active' : '' ?>"><i class="fa-solid fa-truck" style="width: 20px;"></i> <span class="link-text">Search Lorries</span></a>
                <a href="<?= APP_URL ?>/lorries/mine" class="sidebar-link <?= ($requestUri === 'lorries/mine' || preg_match('#^lorries/[0-9]+/edit$#i', $requestUri)) ? 'active' : '' ?>"><i class="fa-solid fa-truck-ramp-box" style="width: 20px;"></i> <span class="link-text">My Lorries</span></a>
                <a href="<?= APP_URL ?>/lorries/add" class="sidebar-link <?= ($requestUri === 'lorries/add') ? 'active' : '' ?>"><i class="fa-solid fa-circle-plus" style="width: 20px;"></i> <span class="link-text">Add Lorry</span></a>
                <a href="<?= APP_URL ?>/bookings/owner" class="sidebar-link <?= (str_starts_with($requestUri, 'bookings/owner') || str_starts_with($requestUri, 'bookings/detail')) ? 'active' : '' ?>"><i class="fa-solid fa-clipboard-list" style="width: 20px;"></i> <span class="link-text">Booking Requests</span></a>
                <a href="<?= APP_URL ?>/payments/history" class="sidebar-link <?= ($requestUri === 'payments/history') ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left" style="width: 20px;"></i> <span class="link-text">Payment History</span></a>
            <?php endif; ?>
            
            <div style="border-top: 1px solid rgba(255,255,255,0.05); margin: var(--space-3) 0;"></div>
            
            <!-- Logout link -->
            <a href="<?= APP_URL ?>/auth/logout" class="sidebar-link" style="color: var(--danger);"><i class="fa-solid fa-right-from-bracket" style="width: 20px;"></i> <span class="link-text">Logout</span></a>
        </nav>
    </aside>

    <!-- Right Column: Main Content Area -->
    <main class="dashboard-main">
<?php else: ?>
<main>
<?php endif; ?>
