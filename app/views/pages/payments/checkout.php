<!-- Checkout Page Container -->
<div class="<?= !empty($inDashboard) ? '' : 'container' ?>" style="<?= !empty($inDashboard) ? '' : 'padding: var(--space-8) 0;' ?>">
    <!-- Main page heading -->
    <h1><i class="fa-solid fa-cash-register"></i> Checkout</h1>

    <!-- Two-column grid layout: Left form inputs, Right summary details -->
    <div class="responsive-grid-equal">
        
        <!-- Left Column: Checkout Payment Method Form -->
        <div class="card p-6">
            <h3 class="mb-4"><i class="fa-solid fa-credit-card"></i> Payment Method</h3>
            <form method="POST" action="<?= APP_URL ?>/payments/checkout" id="checkout-form">
                <?= csrfField() ?>
                <!-- Hidden booking identification input -->
                <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">

                <!-- Selection selector for payment gateway -->
                <div class="form-group">
                    <label class="form-label"><i class="fa-solid fa-wallet" style="color: var(--primary);"></i> Select Payment Method</label>
                    <select name="payment_method" class="form-control" required>
                        <?php foreach (PAYMENT_METHODS as $key => $labels): ?>
                            <!-- Loop over supported mobile money and card gateways -->
                            <option value="<?= e($key) ?>"><?= e($labels['en']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Phone number for mobile money push messages -->
                <div class="form-group">
                    <label class="form-label" for="pay_phone"><i class="fa-solid fa-phone" style="color: var(--primary);"></i> Phone Number (for mobile money)</label>
                    <input type="tel" name="phone" id="pay_phone" class="form-control" placeholder="+255 7XX XXX XXX">
                </div>

                <!-- Simulation informational notice -->
                <div class="alert alert-info" style="border-radius: var(--radius-md);">
                    <i class="fa-solid fa-circle-info"></i> <span><strong>Simulated Payment:</strong> In local development mode, all payments are instantly processed and marked as successful.</span>
                </div>

                <!-- Checkout submit action button -->
                <button type="submit" class="btn btn-accent btn-lg btn-block"><i class="fa-solid fa-circle-check"></i> Pay <?= number_format((float)($booking['quoted_price'] ?? 0)) ?> TZS</button>
            </form>
        </div>

        <!-- Right Column: Order Summary details card -->
        <div class="card p-6">
            <h3 class="mb-4"><i class="fa-solid fa-receipt"></i> Order Summary</h3>
            <table class="table">
                <tr><td>Booking Ref</td><td><strong><?= e($booking['booking_ref'] ?? '#' . $booking['id']) ?></strong></td></tr>
                <tr><td>Pickup</td><td>📍 <?= e($booking['pickup_address'] ?? '—') ?></td></tr>
                <tr><td>Delivery</td><td>📍 <?= e($booking['delivery_address'] ?? '—') ?></td></tr>
                <tr><td>Distance</td><td><?= e($booking['distance_km'] ?? '—') ?> km</td></tr>
                <!-- Final highlighted price total -->
                <tr style="font-size:1.1rem;"><td class="font-bold">Total</td><td class="font-bold text-primary"><?= number_format((float)($booking['quoted_price'] ?? 0)) ?> TZS</td></tr>
            </table>
        </div>
    </div>
</div>
