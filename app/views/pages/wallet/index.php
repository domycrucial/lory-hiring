<h1><i class="fa-solid fa-wallet"></i> My Wallet</h1>
<p class="text-muted mb-6">Manage your earnings, view transaction logs, and withdraw funds.</p>

<div class="grid grid-3 mb-8">
    <!-- Wallet Card -->
    <div class="card p-6" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border: none; box-shadow: var(--shadow-md);">
        <div class="text-xs uppercase tracking-wider opacity-75 mb-2">Available Balance</div>
        <div class="text-3xl font-extrabold mb-4"><?= formatTZS($user['wallet_balance'] ?? 0) ?></div>
        <div class="text-xs opacity-90"><i class="fa-solid fa-circle-info"></i> Minimum withdrawal amount is <?= formatTZS(MIN_WITHDRAWAL) ?>.</div>
    </div>
    
    <!-- Total Earned Card -->
    <div class="card p-6">
        <div class="text-xs uppercase tracking-wider text-muted mb-2">Total Payouts Received</div>
        <div class="text-2xl font-bold text-success mb-4"><?= formatTZS($earnings['total_earned'] ?? 0) ?></div>
        <div class="text-xs text-muted"><i class="fa-solid fa-truck-fast"></i> From <?= (int)($earnings['total_trips'] ?? 0) ?> completed trips.</div>
    </div>

    <!-- Commissions Paid Card -->
    <div class="card p-6">
        <div class="text-xs uppercase tracking-wider text-muted mb-2">Platform Commission Paid</div>
        <div class="text-2xl font-bold text-danger mb-4"><?= formatTZS($earnings['commission_paid'] ?? 0) ?></div>
        <div class="text-xs text-muted"><i class="fa-solid fa-percent"></i> 8% standard commission fee.</div>
    </div>
</div>

<div class="grid grid-3-2">
    <!-- Withdrawal Form -->
    <div class="card p-6">
        <h3 class="mb-4"><i class="fa-solid fa-money-bill-transfer"></i> Request Withdrawal</h3>
        <form action="<?= APP_URL ?>/wallet/withdraw" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            
            <div class="form-group mb-4">
                <label for="withdraw_amount" class="form-label">Withdrawal Amount (TZS)</label>
                <div style="position: relative;">
                    <input type="number" id="withdraw_amount" name="amount" class="form-control" min="<?= MIN_WITHDRAWAL ?>" max="<?= (float)($user['wallet_balance'] ?? 0.00) ?>" step="1000" placeholder="e.g. 50000" required>
                </div>
            </div>

            <div class="form-group mb-6">
                <label for="withdraw_phone" class="form-label">Mobile Money Wallet Number</label>
                <input type="text" id="withdraw_phone" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" placeholder="e.g. +255712345678" required>
                <p class="text-xs text-muted mt-1">Supports M-Pesa, Airtel Money, Halopesa, etc.</p>
            </div>

            <button type="submit" class="btn btn-primary w-full" <?= ((float)($user['wallet_balance'] ?? 0.00) < MIN_WITHDRAWAL) ? 'disabled' : '' ?>>
                <i class="fa-solid fa-paper-plane"></i> Submit Request
            </button>
        </form>
    </div>

    <!-- Withdrawal History -->
    <div class="card p-6">
        <h3 class="mb-4"><i class="fa-solid fa-clock-rotate-left"></i> Withdrawal History</h3>
        <?php if (empty($withdrawals)): ?>
            <div class="text-center py-8 text-muted">
                <i class="fa-solid fa-receipt fa-2x mb-2" style="display: block; opacity: 0.5;"></i>
                No withdrawals made yet.
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="table text-sm">
                    <thead>
                        <tr>
                            <th>Amount</th>
                            <th>Number</th>
                            <th>Status</th>
                            <th>Requested At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($withdrawals as $w): ?>
                        <tr>
                            <td><strong><?= formatTZS($w['amount']) ?></strong></td>
                            <td><code><?= e($w['mobile_number']) ?></code></td>
                            <td>
                                <span class="badge badge-<?= $w['status'] === 'completed' ? 'success' : ($w['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= e(ucfirst($w['status'])) ?>
                                </span>
                            </td>
                            <td><?= formatDate($w['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
