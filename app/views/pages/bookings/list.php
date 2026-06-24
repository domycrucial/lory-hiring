<!-- Bookings List Content Container -->
<div class="<?= !empty($inDashboard) ? '' : 'container' ?>" style="<?= !empty($inDashboard) ? '' : 'padding: var(--space-8) 0;' ?>">
    <!-- Main page heading showing the dashboard title -->
    <h1><i class="fa-solid fa-list-check"></i> <?= e($pageTitle) ?></h1>

    <!-- Renders empty layout state if no bookings exist -->
    <?php if (empty($bookings)): ?>
        <div class="card p-8 text-center">
            <!-- Info icon -->
            <p style="font-size: 4rem; color: var(--gray-400);"><i class="fa-solid fa-calendar-xmark"></i></p>
            <h3 class="mt-4">No bookings found</h3>
            <p class="text-muted">
                <?php if (currentUserRole() === 'customer'): ?>
                    <!-- CTA link for guest/customers to find a truck -->
                    <a href="<?= APP_URL ?>/lorries/search" class="btn btn-primary mt-4"><i class="fa-solid fa-magnifying-glass"></i> Browse Lorries</a>
                <?php else: ?>
                    Your requested and current trips will appear here once customers place order requests.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <!-- Table container wrapper -->
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <!-- Table header descriptors -->
                        <th>Ref</th><th>Pickup</th><th>Delivery</th><th>Price</th><th>Status</th><th>Date</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Iterate bookings records list -->
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <!-- Reference number -->
                        <td><strong><?= e($b['booking_ref'] ?? '#' . $b['id']) ?></strong></td>
                        <!-- Pickup location -->
                        <td><?= e($b['pickup_address'] ?? '—') ?></td>
                        <!-- Drop location -->
                        <td><?= e($b['delivery_address'] ?? '—') ?></td>
                        <!-- Quoted rate price -->
                        <td><?= number_format((float)($b['quoted_price'] ?? $b['total_price'] ?? 0)) ?> TZS</td>
                        <!-- Status badge -->
                        <td><span class="badge status-<?= e($b['status'] ?? 'pending') ?>"><?= e(ucfirst($b['status'] ?? 'pending')) ?></span></td>
                        <!-- Trip date -->
                        <td><?= formatDate($b['preferred_date'] ?? $b['pickup_date'] ?? null) ?></td>
                        <!-- Row actions -->
                        <td>
                            <div class="flex gap-2">
                                <!-- Base detail page view button -->
                                <a href="<?= APP_URL ?>/bookings/detail/<?= (int)$b['id'] ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-eye"></i> View</a>
                                
                                <!-- Actions shown only to Lorry Owner on pending trips -->
                                <?php if (currentUserRole() === 'lorry_owner' && $b['status'] === 'pending'): ?>
                                    <!-- Accept booking action form -->
                                    <form method="POST" action="<?= APP_URL ?>/bookings/<?= (int)$b['id'] ?>/accept" style="display:inline;">
                                        <?= csrfField() ?>
                                        <button class="btn btn-primary btn-sm"><i class="fa-solid fa-check"></i> Accept</button>
                                    </form>
                                    <!-- Decline booking action form -->
                                    <form method="POST" action="<?= APP_URL ?>/bookings/<?= (int)$b['id'] ?>/decline" style="display:inline;">
                                        <?= csrfField() ?>
                                        <button class="btn btn-danger btn-sm"><i class="fa-solid fa-xmark"></i> Decline</button>
                                    </form>
                                <?php endif; ?>
                                
                                <!-- Actions shown to Lorry Owner on active trips -->
                                <?php if (currentUserRole() === 'lorry_owner' && $b['status'] === 'accepted'): ?>
                                    <!-- Mark as complete action form -->
                                    <form method="POST" action="<?= APP_URL ?>/bookings/<?= (int)$b['id'] ?>/complete" style="display:inline;">
                                        <?= csrfField() ?>
                                        <button class="btn btn-primary btn-sm"><i class="fa-solid fa-square-check"></i> Complete</button>
                                    </form>
                                <?php endif; ?>
                                
                                <!-- Actions shown to Customer on pending trips -->
                                <?php if (currentUserRole() === 'customer' && $b['status'] === 'pending'): ?>
                                    <!-- Cancel booking action form with alert warning dialog -->
                                    <form method="POST" action="<?= APP_URL ?>/bookings/<?= (int)$b['id'] ?>/cancel" style="display:inline;" onsubmit="return confirm('Cancel this booking?')">
                                        <?= csrfField() ?>
                                        <button class="btn btn-danger btn-sm"><i class="fa-solid fa-circle-minus"></i> Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
