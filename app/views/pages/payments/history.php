<!-- Payment History Container -->
<div class="<?= !empty($inDashboard) ? '' : 'container' ?>" style="<?= !empty($inDashboard) ? '' : 'padding: var(--space-8) 0;' ?>">
    <!-- Main page heading -->
    <h1><i class="fa-solid fa-clock-rotate-left"></i> Payment History</h1>

    <!-- Renders empty layout state if no payments exist -->
    <?php if (empty($payments)): ?>
        <div class="card p-8 text-center">
            <!-- credit card check symbol -->
            <p style="font-size: 4rem; color: var(--gray-400);"><i class="fa-solid fa-credit-card"></i></p>
            <h3 class="mt-4">No payments yet</h3>
            <p class="text-muted">Your payment transaction records history will appear here once you make bookings.</p>
        </div>
    <?php else: ?>
        <!-- Table container wrapper -->
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <!-- Table header columns descriptors -->
                        <th>Transaction ID</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Iterate payment transaction entries -->
                    <?php foreach ($payments as $p): ?>
                    <tr>
                        <!-- Transaction ID reference (monospaced format) -->
                        <td><strong style="font-family: var(--font-mono); color: var(--gray-800);"><?= e($p['transaction_id'] ?? '—') ?></strong></td>
                        <!-- Amount paid -->
                        <td><strong><?= number_format((float)($p['amount'] ?? 0)) ?> TZS</strong></td>
                        <!-- Payment gateway provider name -->
                        <td><?= e(PAYMENT_METHODS[$p['payment_method']]['en'] ?? $p['payment_method'] ?? '—') ?></td>
                        <!-- Payment transaction status badge -->
                        <td>
                            <span class="badge status-<?= e($p['status'] === 'completed' ? 'completed' : 'cancelled') ?>">
                                <?= e(ucfirst($p['status'] ?? 'completed')) ?>
                            </span>
                        </td>
                        <!-- Date transaction timestamp -->
                        <td><?= formatDateTime($p['created_at'] ?? null) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
