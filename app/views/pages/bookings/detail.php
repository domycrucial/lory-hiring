<!-- Booking Detail Page Container -->
<div class="<?= !empty($inDashboard) ? '' : 'container' ?>" style="<?= !empty($inDashboard) ? '' : 'padding: var(--space-8) 0;' ?>">
    <!-- Main page heading showing the booking reference -->
    <h1><i class="fa-solid fa-file-invoice"></i> Booking #<?= e($booking['booking_ref'] ?? $booking['id']) ?></h1>

    <!-- Two-column grid layout: Left details table, Right action panel -->
    <div class="responsive-grid-2-1">
        
        <!-- Left Column: Booking Details Table -->
        <div class="card p-6">
            <h3 class="mb-4"><i class="fa-solid fa-list-ul"></i> Booking Details</h3>
            <table class="table">
                <!-- Reference row -->
                <tr><td class="font-bold">Reference</td><td><strong><?= e($booking['booking_ref'] ?? '#' . $booking['id']) ?></strong></td></tr>
                <!-- Status row with dynamic badge -->
                <tr><td class="font-bold">Status</td><td><span class="badge status-<?= e($booking['status']) ?>"><?= e(ucfirst($booking['status'])) ?></span></td></tr>
                <!-- Pickup address row -->
                <tr><td class="font-bold">Pickup Address</td><td>📍 <?= e($booking['pickup_address'] ?? '—') ?></td></tr>
                <!-- Delivery address row -->
                <tr><td class="font-bold">Delivery Address</td><td>📍 <?= e($booking['delivery_address'] ?? '—') ?></td></tr>
                <!-- Calculated distance row -->
                <tr><td class="font-bold">Distance</td><td><?= e($booking['distance_km'] ?? '—') ?> km</td></tr>
                <!-- Booked date row -->
                <tr><td class="font-bold">Preferred Date</td><td><?= formatDate($booking['preferred_date']) ?></td></tr>
                <!-- Final price quotation row -->
                <tr><td class="font-bold">Total Price</td><td><strong class="text-primary"><?= number_format((float)($booking['quoted_price'] ?? 0)) ?> TZS</strong></td></tr>
                
                <!-- Conditionally render Lorry Nickname -->
                <?php if (!empty($booking['lorry_name'])): ?>
                <tr><td class="font-bold">Lorry</td><td><?= e($booking['lorry_name']) ?></td></tr>
                <?php endif; ?>
                
                <!-- Conditionally render Customer Name -->
                <?php if (!empty($booking['customer_name'])): ?>
                <tr><td class="font-bold">Customer</td><td><?= e($booking['customer_name']) ?></td></tr>
                <?php endif; ?>
                
                <!-- Booking creation date row -->
                <tr><td class="font-bold">Created At</td><td><?= formatDateTime($booking['created_at'] ?? null) ?></td></tr>
            </table>
        </div>

        <!-- Right Column: Interactive Side Panel -->
        <div>
            <!-- Checkout CTA card shown only to customer when booking is accepted by owner -->
            <?php if (currentUserRole() === 'customer' && $booking['status'] === 'accepted'): ?>
                <div class="card p-6 text-center mb-4" style="background: var(--accent-light); border: 2px solid var(--accent);">
                    <h3><i class="fa-solid fa-credit-card"></i> Payment Required</h3>
                    <p class="text-sm mt-2">This booking has been accepted. Please pay to finalize your booking reservation.</p>
                    <a href="<?= APP_URL ?>/payments/checkout/<?= (int)$booking['id'] ?>" class="btn btn-accent btn-lg btn-block mt-4"><i class="fa-solid fa-wallet"></i> Pay Now</a>
                </div>
            <?php endif; ?>

            <!-- Action Controls Card -->
            <div class="card p-6">
                <h3><i class="fa-solid fa-sliders"></i> Actions</h3>
                <div class="flex flex-col gap-3 mt-4">
                    
                    <!-- Lorry Owner Options on Pending Booking -->
                    <?php if (currentUserRole() === 'lorry_owner' && $booking['status'] === 'pending'): ?>
                        <!-- Accept booking action form -->
                        <form method="POST" action="<?= APP_URL ?>/bookings/<?= (int)$booking['id'] ?>/accept" class="w-full">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-circle-check"></i> Accept Booking</button>
                        </form>
                        <!-- Decline booking action form -->
                        <form method="POST" action="<?= APP_URL ?>/bookings/<?= (int)$booking['id'] ?>/decline" class="w-full">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-danger btn-block"><i class="fa-solid fa-circle-xmark"></i> Decline Booking</button>
                        </form>
                    
                    <!-- Lorry Owner Options on Accepted Booking -->
                    <?php elseif (currentUserRole() === 'lorry_owner' && $booking['status'] === 'accepted'): ?>
                        <!-- Mark as complete action form -->
                        <form method="POST" action="<?= APP_URL ?>/bookings/<?= (int)$booking['id'] ?>/complete" class="w-full">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-square-check"></i> Mark as Completed</button>
                        </form>
                    
                    <!-- Customer Options on Pending Booking -->
                    <?php elseif (currentUserRole() === 'customer' && $booking['status'] === 'pending'): ?>
                        <!-- Cancel booking action form with alert confirmation -->
                        <form method="POST" action="<?= APP_URL ?>/bookings/<?= (int)$booking['id'] ?>/cancel" class="w-full" onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-danger btn-block"><i class="fa-solid fa-circle-minus"></i> Cancel Booking</button>
                        </form>
                    <?php endif; ?>

                    <!-- Navigation back links based on user role -->
                    <?php if (currentUserRole() === 'customer'): ?>
                        <a href="<?= APP_URL ?>/bookings/mine" class="btn btn-outline btn-block"><i class="fa-solid fa-arrow-left"></i> My Bookings</a>
                    <?php else: ?>
                        <a href="<?= APP_URL ?>/bookings/owner" class="btn btn-outline btn-block"><i class="fa-solid fa-arrow-left"></i> Booking Requests</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
