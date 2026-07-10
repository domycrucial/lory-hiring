<h1><i class="fa-solid fa-clipboard-list"></i> Booking Oversight</h1>
<p class="text-muted mb-6">Monitor all lorry booking requests and transit statuses across the platform.</p>

<?php if (empty($bookingsList)): ?>
    <div class="card p-6 text-center mt-4">
        <p class="text-muted"><i class="fa-solid fa-inbox"></i> No bookings have been created on the platform yet.</p>
    </div>
<?php else: ?>
    <div class="table-wrapper mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Customer</th>
                    <th>Lorry Nickname (Plate)</th>
                    <th>Lorry Owner</th>
                    <th>Route (From → To)</th>
                    <th>Quoted Price</th>
                    <th>Status</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookingsList as $b): ?>
                <tr>
                    <td>
                        <a href="<?= APP_URL ?>/bookings/detail/<?= (int)$b['id'] ?>"><strong><?= e($b['booking_ref']) ?></strong></a>
                    </td>
                    <td><?= e($b['customer_name']) ?></td>
                    <td><?= e($b['lorry_name']) ?> (<code><?= e($b['plate_number']) ?></code>)</td>
                    <td><?= e($b['owner_name']) ?> (<a href="tel:<?= e($b['owner_phone']) ?>"><?= e($b['owner_phone']) ?></a>)</td>
                    <td><?= e($b['pickup_address']) ?> <i class="fa-solid fa-arrow-right text-xs text-muted"></i> <?= e($b['delivery_address']) ?></td>
                    <td><strong><?= formatTZS($b['quoted_price']) ?></strong></td>
                    <td>
                        <span class="badge <?= bookingStatusClass($b['status']) ?>">
                            <?= e(bookingStatusLabel($b['status'], currentLang())) ?>
                        </span>
                    </td>
                    <td><?= formatDateTime($b['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
