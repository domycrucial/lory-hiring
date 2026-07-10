<h1><i class="fa-solid fa-credit-card"></i> Revenue & Payments</h1>
<p class="text-muted mb-6">Overview of all customer payments, commission deductions, and payouts.</p>

<?php if (empty($paymentsList)): ?>
    <div class="card p-6 text-center mt-4">
        <p class="text-muted"><i class="fa-solid fa-credit-card"></i> No payments recorded yet.</p>
    </div>
<?php else: ?>
    <div class="table-wrapper mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th>Txn ID</th>
                    <th>Booking Ref</th>
                    <th>Payer</th>
                    <th>Total Amount</th>
                    <th>Commission (<?= COMMISSION_RATE ?>%)</th>
                    <th>Owner Payout</th>
                    <th>Payment Method</th>
                    <th>Paid At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paymentsList as $p): ?>
                <tr>
                    <td><code><?= e($p['transaction_id']) ?></code></td>
                    <td><strong><?= e($p['booking_ref']) ?></strong></td>
                    <td><?= e($p['customer_name']) ?></td>
                    <td><strong class="text-success"><?= formatTZS($p['amount']) ?></strong></td>
                    <td class="text-danger"><?= formatTZS($p['platform_commission']) ?></td>
                    <td class="text-primary"><?= formatTZS($p['owner_payout']) ?></td>
                    <td><span class="badge badge-secondary"><?= e(strtoupper($p['payment_method'])) ?></span></td>
                    <td><?= formatDateTime($p['paid_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
