
        <!-- Main welcome heading -->
        <h1><i class="fa-solid fa-chart-pie"></i> <?= currentLang() === 'sw' ? 'Dawati la Mmiliki' : 'Owner Dashboard' ?></h1>
        <p class="text-muted mb-6"><?= currentLang() === 'sw' ? 'Karibu tena' : 'Welcome back' ?>, <?= e(currentUserName()) ?>!</p>

        <!-- Stats Metric Cards Row -->
        <div class="grid grid-4 mb-8">
            <div class="stat-card">
                <div class="stat-value"><?= (int)($stats['total_lorries'] ?? 0) ?></div>
                <div class="stat-label"><?= currentLang() === 'sw' ? 'Malori Yangu' : 'My Lorries' ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= (int)($stats['total_bookings'] ?? 0) ?></div>
                <div class="stat-label"><?= currentLang() === 'sw' ? 'Jumla ya Uhifadhi' : 'Total Bookings' ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--warning);"><?= (int)($stats['pending'] ?? 0) ?></div>
                <div class="stat-label"><?= currentLang() === 'sw' ? 'Maombi Yanayosubiri' : 'Pending Requests' ?></div>
            </div>
            <div class="stat-card" style="border: 1px solid var(--primary-light); background: var(--primary-bg);">
                <div class="stat-value" style="color: var(--primary); font-size: 1.35rem; font-weight: 800; padding: 4px 0;">
                    <?= number_format((float)($stats['wallet_balance'] ?? 0.00)) ?> TZS
                </div>
                <div class="stat-label"><a href="<?= APP_URL ?>/wallet" style="color: var(--primary); font-weight: 600;"><i class="fa-solid fa-wallet"></i> <?= currentLang() === 'sw' ? 'Mkoba Wangu' : 'My Wallet' ?> →</a></div>
            </div>
        </div>

        <?php if (!empty($recentBookings)): ?>
        <!-- Chart.js Revenue Trend -->
        <div class="card p-6 mb-8">
            <h3><i class="fa-solid fa-chart-line text-primary"></i> <?= currentLang() === 'sw' ? 'Mwenendo wa Mapato (Uhifadhi wa Karibuni)' : 'Earnings Trend (Recent Bookings)' ?></h3>
            <div style="height: 220px; position: relative;" class="mt-4">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode(array_reverse(array_map(fn($b) => $b['booking_ref'], $recentBookings))) ?>,
                    datasets: [{
                        label: '<?= currentLang() === 'sw' ? 'Thamani ya Safari (TZS)' : 'Trip Value (TZS)' ?>',
                        data: <?= json_encode(array_reverse(array_map(fn($b) => (float)$b['quoted_price'], $recentBookings))) ?>,
                        borderColor: 'rgb(37, 99, 235)',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + ' TZS';
                                }
                            }
                        }
                    }
                }
            });
        });
        </script>
        <?php endif; ?>

        <!-- Recent Booking Requests section -->
        <h2><i class="fa-solid fa-bell"></i> <?= currentLang() === 'sw' ? 'Maombi ya Hivi Karibuni ya Uhifadhi' : 'Recent Booking Requests' ?></h2>
        <?php if (empty($recentBookings)): ?>
            <div class="card p-6 text-center mt-4">
                <p class="text-muted"><i class="fa-solid fa-inbox"></i> <?= currentLang() === 'sw' ? 'Bado haujapokea maombi yoyote ya uhifadhi. Weka malori yako ili kuanza kupokea!' : 'No booking requests received yet. Add your lorries to start receiving bookings!' ?></p>
            </div>
        <?php else: ?>
            <div class="table-wrapper mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= currentLang() === 'sw' ? 'Kumbukumbu' : 'Ref' ?></th>
                            <th><?= currentLang() === 'sw' ? 'Kuchukua' : 'Pickup' ?></th>
                            <th><?= currentLang() === 'sw' ? 'Kufikisha' : 'Delivery' ?></th>
                            <th><?= currentLang() === 'sw' ? 'Gharama' : 'Price' ?></th>
                            <th><?= currentLang() === 'sw' ? 'Hali' : 'Status' ?></th>
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
                            <td><i class="fa-solid fa-location-dot text-primary" style="font-size: 0.875rem;"></i> <?= e($b['pickup_address'] ?? '—') ?></td>
                            <td><i class="fa-solid fa-flag text-accent" style="font-size: 0.875rem;"></i> <?= e($b['delivery_address'] ?? '—') ?></td>
                            <td><strong><?= number_format((float)($b['quoted_price'] ?? 0)) ?> TZS</strong></td>
                            <td>
                                <!-- Status badge -->
                                <span class="badge status-<?= e($b['status'] ?? 'pending') ?>">
                                    <?= e(bookingStatusLabel($b['status'] ?? 'pending', currentLang())) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

