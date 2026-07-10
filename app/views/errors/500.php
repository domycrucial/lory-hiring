<?php $pageTitle = '500 Internal Server Error'; ?>
<?php require_once VIEW_PATH . '/layouts/header.php'; ?>
<div class="auth-wrapper">
    <div class="auth-card text-center">
        <h1 style="font-size: 4rem; color: var(--error-color, #dc3545);"><i class="fa-solid fa-triangle-exclamation"></i> 500</h1>
        <p class="text-lg mb-6">Hitilafu ya Server / Internal Server Error.</p>
        <p class="text-muted mb-6">Something went wrong on our end. Please try again later.</p>
        <a href="<?= APP_URL ?>/" class="btn btn-primary">← Back to Home</a>
    </div>
</div>
<?php require_once VIEW_PATH . '/layouts/footer.php'; ?>
