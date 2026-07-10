<?php
// Parse current request URI relative to app root
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
$basePath = parse_url(APP_URL, PHP_URL_PATH) ?? '';
if ($basePath !== '' && str_starts_with($requestUri, $basePath)) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestUri = trim($requestUri, '/');
// Determine if we are on an authentication page
$isAuthPage = str_starts_with($requestUri, 'auth');

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
    <!-- Load premium typography fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Inline script to apply dark/light theme instantly without flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <!-- Import FontAwesome icon library for reviews and navigation indicators -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Load custom layout styling custom.css stylesheet -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/custom.css">
    <!-- Load SweetAlert2 library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Inject controller specific styling stylesheets -->
    <?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="<?= APP_URL ?>/public/css/<?= $css ?>">
    <?php endforeach; endif; ?>
</head><body class="<?= !empty($inDashboard) ? 'dashboard-active' : '' ?>">
<!-- ─── Navbar ─────────────────────────────────────────────── -->
<!-- sticky top navigation bar element -->
<?php if (!$isAuthPage): ?>
<nav class="navbar <?= $inDashboard ? 'navbar-dashboard' : '' ?>" id="main-navbar">
    <div class="container">
        <!-- Logo brand pointing to home page -->
        <a href="<?= APP_URL ?>/" class="navbar-brand">
            <span class="logo-icon"><i class="fa-solid fa-truck"></i></span> OLHS
        </a>

        <!-- Mobile hamburger drawer toggle button (Omitted on auth pages) -->
        <?php if (!$isAuthPage): ?>
        <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars"></i>
        </button>
        <?php endif; ?>

        <!-- Main links list element (Omitted on auth pages) -->
        <?php if (!$isAuthPage): ?>
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
            <?php if (($currentPage ?? '') === 'home'): ?>
            <!-- Language Switcher -->
            <li style="display: inline-flex; align-items: center;">
                <span class="lang-switcher" style="font-size: 0.825rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: var(--gray-100); border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
                    <i class="fa-solid fa-globe" style="color: var(--primary); font-size: 0.875rem;"></i>
                    <a href="<?= APP_URL ?>/lang/switch/en" style="color: <?= currentLang() === 'en' ? 'var(--primary)' : 'var(--gray-500)' ?>; text-decoration: none; font-weight: <?= currentLang() === 'en' ? '800' : '500' ?>;">EN</a>
                    <span style="color: var(--gray-300);">|</span>
                    <a href="<?= APP_URL ?>/lang/switch/sw" style="color: <?= currentLang() === 'sw' ? 'var(--primary)' : 'var(--gray-500)' ?>; text-decoration: none; font-weight: <?= currentLang() === 'sw' ? '800' : '500' ?>;">SW</a>
                </span>
            </li>
            <!-- Theme Toggle Switcher -->
            <li style="display: inline-flex; align-items: center;">
                <button id="theme-toggle" class="btn btn-outline btn-sm" style="padding: 6px 10px; border-radius: var(--radius-md); display: inline-flex; align-items: center; justify-content: center; height: 32px; width: 36px; border-color: var(--gray-300); color: var(--gray-600); background: transparent; cursor: pointer; box-shadow: none;" aria-label="Toggle dark mode">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </li>
            <?php endif; ?>
        </ul>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>

<!-- ─── Flash Messages ────────────────────────────────────── -->
<?php $flash = getFlash(); if ($flash): ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            title: '<?= $flash['type'] === 'success' ? (currentLang() === 'sw' ? 'Hongera!' : 'Success!') : ($flash['type'] === 'warning' ? (currentLang() === 'sw' ? 'Angalizo!' : 'Warning!') : (currentLang() === 'sw' ? 'Hitilafu!' : 'Error!')) ?>',
            text: '<?= e($flash['message']) ?>',
            icon: '<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'info' || $flash['type'] === 'warning' ? 'info' : 'error') ?>',
            confirmButtonColor: '#2563eb'
        });
    });
</script>
<?php endif; ?>

<!-- ─── Main Content ──────────────────────────────────────── -->
<?php if ($inDashboard): ?>
<div class="dashboard">
    <!-- Left Column: Premium Interactive Sidebar -->
    <aside class="sidebar">
        <!-- Sidebar Brand / Logo Header -->
        <div class="sidebar-brand-wrapper">
            <a href="<?= APP_URL ?>/" class="sidebar-brand">
                <span class="logo-icon"><i class="fa-solid fa-truck"></i></span> OLHS
            </a>
        </div>

        <!-- Sidebar User Profile Info -->
        <div class="sidebar-user">
            <div class="sidebar-avatar">
                <i class="fa-solid fa-circle-user"></i>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= e(currentUserName()) ?></div>
                <div class="sidebar-user-role">
                    <?php 
                    if (in_array(currentUserRole(), ['admin', 'super_admin'])) {
                        echo currentLang() === 'sw' ? 'Msimamizi' : 'Administrator';
                    } elseif (currentUserRole() === 'customer') {
                        echo currentLang() === 'sw' ? 'Mteja' : 'Customer';
                    } else {
                        echo currentLang() === 'sw' ? 'Mmiliki wa Lori' : 'Lorry Owner';
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Navigation Menu Links list -->
        <nav class="sidebar-menu">
            <?php if (in_array(currentUserRole(), ['admin', 'super_admin'])): ?>
                <!-- Admin Dashboard navigation links -->
                <a href="<?= APP_URL ?>/admin/" class="sidebar-link <?= ($currentPage ?? '') === 'admin_dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> <span class="link-text">Admin Dashboard</span></a>
                <a href="<?= APP_URL ?>/admin/lorries" class="sidebar-link <?= ($currentPage ?? '') === 'admin_lorries' ? 'active' : '' ?>"><i class="fa-solid fa-truck"></i> <span class="link-text">Manage Lorries</span></a>
                <a href="<?= APP_URL ?>/admin/users" class="sidebar-link <?= ($currentPage ?? '') === 'admin_users' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> <span class="link-text">Manage Users</span></a>
                <a href="<?= APP_URL ?>/admin/bookings" class="sidebar-link <?= ($currentPage ?? '') === 'admin_bookings' ? 'active' : '' ?>"><i class="fa-solid fa-clipboard-list"></i> <span class="link-text">Manage Bookings</span></a>
                <a href="<?= APP_URL ?>/admin/payments" class="sidebar-link <?= ($currentPage ?? '') === 'admin_payments' ? 'active' : '' ?>"><i class="fa-solid fa-credit-card"></i> <span class="link-text">Manage Payments</span></a>
                <a href="<?= APP_URL ?>/admin/logs" class="sidebar-link <?= ($currentPage ?? '') === 'admin_logs' ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left"></i> <span class="link-text">System Logs</span></a>
                <a href="<?= APP_URL ?>/profile" class="sidebar-link <?= ($currentPage ?? '') === 'profile' ? 'active' : '' ?>"><i class="fa-solid fa-user-gear"></i> <span class="link-text">My Profile</span></a>
            <?php elseif (currentUserRole() === 'customer'): ?>
                <!-- Customer Dashboard navigation links -->
                <a href="<?= APP_URL ?>/dashboard/customer" class="sidebar-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> <span class="link-text">Customer Dashboard</span></a>
                <a href="<?= APP_URL ?>/lorries/search" class="sidebar-link <?= ($requestUri === 'lorries/search') ? 'active' : '' ?>"><i class="fa-solid fa-truck"></i> <span class="link-text">Search Lorries</span></a>
                <a href="<?= APP_URL ?>/bookings/mine" class="sidebar-link <?= (str_starts_with($requestUri, 'bookings/mine') || str_starts_with($requestUri, 'bookings/detail') || str_starts_with($requestUri, 'bookings/create')) ? 'active' : '' ?>"><i class="fa-solid fa-calendar-check"></i> <span class="link-text">My Bookings</span></a>
                <a href="<?= APP_URL ?>/payments/history" class="sidebar-link <?= ($requestUri === 'payments/history' || str_starts_with($requestUri, 'payments/success')) ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left"></i> <span class="link-text">Payment History</span></a>
                <a href="<?= APP_URL ?>/profile" class="sidebar-link <?= ($currentPage ?? '') === 'profile' ? 'active' : '' ?>"><i class="fa-solid fa-user-gear"></i> <span class="link-text">My Profile</span></a>
            <?php else: ?>
                <!-- Owner Dashboard navigation links -->
                <a href="<?= APP_URL ?>/dashboard/owner" class="sidebar-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> <span class="link-text">Owner Dashboard</span></a>
                <a href="<?= APP_URL ?>/lorries/mine" class="sidebar-link <?= ($requestUri === 'lorries/mine' || preg_match('#^lorries/[0-9]+/edit$#i', $requestUri)) ? 'active' : '' ?>"><i class="fa-solid fa-truck-ramp-box"></i> <span class="link-text">My Lorries</span></a>
                <a href="<?= APP_URL ?>/lorries/add" class="sidebar-link <?= ($requestUri === 'lorries/add') ? 'active' : '' ?>"><i class="fa-solid fa-circle-plus"></i> <span class="link-text">Add Lorry</span></a>
                <a href="<?= APP_URL ?>/bookings/owner" class="sidebar-link <?= (str_starts_with($requestUri, 'bookings/owner') || str_starts_with($requestUri, 'bookings/detail')) ? 'active' : '' ?>"><i class="fa-solid fa-clipboard-list"></i> <span class="link-text">Booking Requests</span></a>
                <a href="<?= APP_URL ?>/wallet" class="sidebar-link <?= ($currentPage ?? '') === 'wallet' ? 'active' : '' ?>"><i class="fa-solid fa-wallet"></i> <span class="link-text">My Wallet</span></a>
                <a href="<?= APP_URL ?>/payments/history" class="sidebar-link <?= ($requestUri === 'payments/history') ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left"></i> <span class="link-text">Payment History</span></a>
                <a href="<?= APP_URL ?>/profile" class="sidebar-link <?= ($currentPage ?? '') === 'profile' ? 'active' : '' ?>"><i class="fa-solid fa-user-gear"></i> <span class="link-text">My Profile</span></a>
            <?php endif; ?>
            
        </nav>

        <!-- Sidebar Footer containing Logout pinned to bottom -->
        <div class="sidebar-footer" style="margin-top: auto; padding-top: var(--space-4); border-top: 1px solid var(--border-color);">
            <a href="<?= APP_URL ?>/auth/logout" class="sidebar-link sidebar-logout" style="color: var(--danger);"><i class="fa-solid fa-right-from-bracket"></i> <span class="link-text"><?= currentLang() === 'sw' ? 'Toka' : 'Logout' ?></span></a>
        </div>
    </aside>

    <!-- Right Column: Main Content Area -->
    <main class="dashboard-main">
<?php else: ?>
<main>
<?php endif; ?>
