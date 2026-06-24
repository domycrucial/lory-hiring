
        <!-- Main welcome heading -->
        <h1><i class="fa-solid fa-chart-pie"></i> Owner Dashboard</h1>
        <p class="text-muted mb-6">Welcome back, <?= e(currentUserName()) ?>!</p>

        <!-- Stats Metric Cards Row -->
        <div class="grid grid-3 mb-8">
            <div class="stat-card">
                <div class="stat-value"><?= (int)($stats['total_lorries'] ?? 0) ?></div>
                <div class="stat-label">My Lorries</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= (int)($stats['total_bookings'] ?? 0) ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--warning);"><?= (int)($stats['pending'] ?? 0) ?></div>
                <div class="stat-label">Pending Requests</div>
            </div>
        </div>

        <!-- Recent Booking Requests section -->
        <h2><i class="fa-solid fa-bell"></i> Recent Booking Requests</h2>
        <?php if (empty($recentBookings)): ?>
            <div class="card p-6 text-center mt-4">
                <p class="text-muted"><i class="fa-solid fa-inbox"></i> No booking requests received yet. Add your lorries to start receiving bookings!</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ref</th><th>Pickup</th><th>Delivery</th><th>Price</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loop over recent bookings rows -->
                        <?php foreach ($recentBookings as $b): ?>
                        <tr>
                            <td>
                                <!-- Clickable details link -->
                                <a href="<?= APP_URL ?>/bookings/detail/<?= (int)$b['id'] ?>"><strong><?= e($b['booking_ref'] ?? '#' . $b['id']) ?></strong></a>
                            </td>
                            <td><?= e($b['pickup_address'] ?? '—') ?></td>
                            <td><?= e($b['delivery_address'] ?? '—') ?></td>
                            <td><strong><?= number_format((float)($b['quoted_price'] ?? 0)) ?> TZS</strong></td>
                            <td>
                                <!-- Status badge -->
                                <span class="badge status-<?= e($b['status'] ?? 'pending') ?>">
                                    <?= e(ucfirst($b['status'] ?? 'pending')) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

