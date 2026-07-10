<!-- Payment Success Page Wrapper -->
<div class="auth-wrapper">
    <div class="auth-card text-center" style="max-width: 500px;">
        <!-- Large animated success icon -->
        <div style="font-size: 5rem; margin-bottom: var(--space-4); color: var(--success); filter: drop-shadow(0 4px 10px rgba(16, 185, 129, 0.2));">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <!-- Success status headline -->
        <h1 style="color: var(--success); margin-bottom: var(--space-2);">Payment Successful!</h1>
        <!-- Supporting text descriptions -->
        <p class="text-muted mb-6">Thank you! Your transaction has been processed successfully. The lorry owner has been notified to proceed with transit arrangements.</p>

        <!-- Conditionally render Transaction Reference details -->
        <?php if (!empty($txnId)): ?>
            <div class="card p-4 mb-6" style="background: var(--success-bg); border: 1px solid rgba(16, 185, 129, 0.2);">
                <p class="text-sm text-muted mb-1"><i class="fa-solid fa-receipt"></i> Transaction ID:</p>
                <p class="font-bold text-lg" style="color: #065f46; font-family: var(--font-mono);"><?= e($txnId) ?></p>
            </div>
        <?php endif; ?>

        <!-- Action back button to booking list -->
        <a href="<?= APP_URL ?>/bookings/mine" class="btn btn-primary btn-lg btn-block"><i class="fa-solid fa-calendar-days"></i> View My Bookings</a>
    </div>
</div>
