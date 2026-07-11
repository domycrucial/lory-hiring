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
                         <?php if (currentUserRole() === 'lorry_owner'): ?>
                             <th>Booking Ref</th><th>Customer</th><th>Total Paid</th><th>Commission (10%)</th><th>Your Payout</th><th>Date</th>
                         <?php else: ?>
                             <th>Transaction ID</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th>
                         <?php endif; ?>
                     </tr>
                 </thead>
                 <tbody>
                     <!-- Iterate payment transaction entries -->
                     <?php foreach ($payments as $p): ?>
                     <tr>
                         <?php if (currentUserRole() === 'lorry_owner'): ?>
                             <td><strong><?= e($p['booking_ref'] ?? '—') ?></strong></td>
                             <td>
                                 <div><?= e($p['customer_name'] ?? '—') ?></div>
                                 <small class="text-muted"><?= e($p['customer_phone'] ?? '—') ?></small>
                             </td>
                             <td><strong><?= number_format((float)($p['amount'] ?? 0)) ?> TZS</strong></td>
                             <td class="text-danger">-<?= number_format((float)($p['platform_commission'] ?? 0)) ?> TZS</td>
                             <td><strong class="text-success"><?= number_format((float)($p['owner_payout'] ?? 0)) ?> TZS</strong></td>
                             <td><?= formatDateTime($p['created_at'] ?? null) ?></td>
                         <?php else: ?>
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
                         <?php endif; ?>
                     </tr>
                     <?php endforeach; ?>
                 </tbody>
             </table>
        </div>
    <?php endif; ?>
</div>
