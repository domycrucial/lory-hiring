<?php $pageTitle = '404 Not Found'; ?>
<?php require_once VIEW_PATH . '/layouts/header.php'; ?>
<div class="auth-wrapper">
    <div class="auth-card text-center">
        <h1 style="font-size: 4rem; color: var(--primary);">404</h1>
        <p class="text-lg mb-6">Ukurasa haupatikani / Page not found.</p>
        <a href="<?= APP_URL ?>/" class="btn btn-primary">← Back to Home</a>
    </div>
</div>
<?php require_once VIEW_PATH . '/layouts/footer.php'; ?>
