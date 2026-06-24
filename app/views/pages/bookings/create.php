<!-- Booking Creation Page Container -->
<div class="<?= !empty($inDashboard) ? '' : 'container' ?>" style="<?= !empty($inDashboard) ? '' : 'padding: var(--space-8) 0;' ?>">
    <!-- Main page heading -->
    <h1><i class="fa-solid fa-clipboard-list"></i> Book Lorry: <?= e($lorry['name'] ?? 'Lorry') ?></h1>

    <!-- Two-column grid layout: Left form inputs, Right summary information -->
    <div class="responsive-grid-equal">
        
        <!-- Left Column: Booking Form -->
        <div class="card p-6">
            <form method="POST" action="<?= APP_URL ?>/bookings/create" id="booking-form">
                <?= csrfField() ?>
                <!-- Hidden lorry identification input -->
                <input type="hidden" name="lorry_id" value="<?= (int)$lorry['id'] ?>">

                <!-- Input for pickup location -->
                <div class="form-group">
                    <label class="form-label" for="pickup_address"><i class="fa-solid fa-location-dot" style="color: var(--primary);"></i> Pickup Address</label>
                    <input type="text" name="pickup_address" id="pickup_address" class="form-control" placeholder="e.g. Kariakoo Market, Dar es Salaam" required>
                </div>

                <!-- Input for delivery destination -->
                <div class="form-group">
                    <label class="form-label" for="delivery_address"><i class="fa-solid fa-location-crosshairs" style="color: var(--primary);"></i> Delivery Address</label>
                    <input type="text" name="delivery_address" id="delivery_address" class="form-control" placeholder="e.g. Arusha Central" required>
                </div>

                <!-- Input for goods contents -->
                <div class="form-group">
                    <label class="form-label" for="goods_description"><i class="fa-solid fa-box-open" style="color: var(--primary);"></i> Goods Description</label>
                    <input type="text" name="goods_description" id="goods_description" class="form-control" placeholder="e.g. Building materials, furniture" value="General cargo">
                </div>

                <!-- Nested two-column grid for distance and pickup date -->
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label" for="distance_km"><i class="fa-solid fa-road" style="color: var(--primary);"></i> Distance (km)</label>
                        <input type="number" name="distance_km" id="distance_km" class="form-control" min="1" step="0.5" placeholder="e.g. 50" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="pickup_date"><i class="fa-solid fa-calendar-day" style="color: var(--primary);"></i> Preferred Date</label>
                        <input type="date" name="pickup_date" id="pickup_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <!-- Live Price Estimate Widget -->
                <div class="card p-4 mb-4" style="background: var(--primary-bg); border: 1px dashed var(--primary-light);">
                    <p class="text-sm text-muted mb-1"><i class="fa-solid fa-calculator"></i> Estimated Price:</p>
                    <p class="text-lg font-bold text-primary" id="price-estimate">Enter distance to see estimate</p>
                    <p class="text-sm text-muted">Rate: <?= number_format((float)($lorry['price_per_km'] ?? 0)) ?> TZS/km</p>
                </div>

                <!-- Confirm submit button -->
                <button type="submit" class="btn btn-accent btn-lg btn-block"><i class="fa-solid fa-circle-check"></i> Confirm Booking</button>
            </form>
        </div>

        <!-- Right Column: Lorry summary details -->
        <div class="card p-6">
            <h3>Lorry Details</h3>
            
            <!-- Dynamic lorry image card display -->
            <div class="card-img-wrapper mb-4 mt-4" style="height: 180px; border-radius: var(--radius-md);">
                <?php 
                $photoUrl = getLorryPhotoUrl($lorry['primary_photo'] ?? null);
                if ($photoUrl): 
                ?>
                    <img src="<?= $photoUrl ?>" alt="<?= e($lorry['name'] ?? 'Lorry') ?>" class="card-img">
                <?php else: ?>
                    <div style="width:100%; height:100%; display: flex; align-items: center; justify-content: center; font-size: 4rem; background: var(--gray-100); color: var(--gray-400);">
                        <i class="fa-solid fa-truck-moving"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Details data table -->
            <table class="table">
                <tr><td class="font-bold">Name</td><td><?= e($lorry['name'] ?? 'Lorry') ?></td></tr>
                <tr><td class="font-bold">Type</td><td><?= e(LORRY_TYPES[$lorry['lorry_type']]['en'] ?? $lorry['lorry_type'] ?? '—') ?></td></tr>
                <tr><td class="font-bold">Capacity</td><td><?= e($lorry['capacity_tonnes'] ?? '—') ?> tons</td></tr>
                <tr><td class="font-bold">Rate</td><td><strong><?= number_format((float)($lorry['price_per_km'] ?? 0)) ?> TZS/km</strong></td></tr>
                <tr><td class="font-bold">Location</td><td>📍 <?= e($lorry['current_location'] ?? '—') ?></td></tr>
            </table>
        </div>
    </div>
</div>

<script>
// Javascript live price estimator calculation
const distInput = document.getElementById('distance_km');
const priceEl = document.getElementById('price-estimate');
const rate = <?= (float)($lorry['price_per_km'] ?? 0) ?>;
if (distInput) {
    distInput.addEventListener('input', () => {
        const d = parseFloat(distInput.value) || 0;
        // Format as localized number string
        priceEl.innerHTML = d > 0 ? '<strong>' + (d * rate).toLocaleString() + ' TZS</strong>' : 'Enter distance to see estimate';
    });
}
</script>
