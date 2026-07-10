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
                        <input type="number" name="distance_km" id="distance_km" class="form-control" min="1" step="0.5" placeholder="Calculated automatically..." readonly required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="pickup_date"><i class="fa-solid fa-calendar-day" style="color: var(--primary);"></i> Preferred Date</label>
                        <input type="date" name="pickup_date" id="pickup_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <!-- Live Price Estimate Widget -->
                <div class="card p-4 mb-4" style="background: var(--primary-bg); border: 1px dashed var(--primary-light);">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                        <div>
                            <p class="text-sm text-muted mb-1"><i class="fa-solid fa-calculator"></i> Estimated Price / Gharama:</p>
                            <p class="text-lg font-bold text-primary" id="price-estimate">Enter addresses...</p>
                            <p class="text-xs text-muted">Rate: <?= number_format((float)($lorry['price_per_km'] ?? 0)) ?> TZS/km</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted mb-1"><i class="fa-solid fa-clock"></i> Est. Duration / Muda wa Safari:</p>
                            <p class="text-lg font-bold text-accent" id="duration-estimate">Enter addresses...</p>
                        </div>
                    </div>
                </div>

                <!-- Route Map display -->
                <div id="bookingRouteMap" style="height: 220px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: var(--space-4); display: none; z-index: 1;"></div>

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

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tanzaniaLocations = {
        "dar es salaam": [-6.7924, 39.2083],
        "dar": [-6.7924, 39.2083],
        "kariakoo": [-6.8163, 39.2755],
        "kigamboni": [-6.8277, 39.3175],
        "ubungo": [-6.7887, 39.2083],
        "temeke": [-6.8524, 39.2678],
        "kinondoni": [-6.7824, 39.2244],
        "dodoma": [-6.1731, 35.7419],
        "arusha": [-3.3869, 36.6830],
        "mwanza": [-2.5183, 32.9003],
        "morogoro": [-6.8278, 37.6636],
        "tanga": [-5.0689, 39.0988],
        "moshi": [-3.3406, 37.3428],
        "kilimanjaro": [-3.3406, 37.3428],
        "zanzibar": [-6.1659, 39.2026],
        "mbeya": [-8.9094, 33.4608],
        "iringa": [-7.7731, 35.6988],
        "tabora": [-5.0167, 32.8000],
        "singida": [-4.8167, 34.7500],
        "kigoma": [-4.8769, 29.6267],
        "shinyanga": [-3.6667, 33.4167],
        "bukoba": [-1.3333, 31.8167],
        "musoma": [-1.5000, 33.8000],
        "mtwara": [-10.2744, 40.1806],
        "lindi": [-9.9972, 39.7144],
        "songea": [-10.6833, 35.6500],
        "sumbawanga": [-7.9667, 31.6167],
        "mpanda": [-6.3500, 31.0667],
        "geita": [-2.8667, 32.2333],
        "bariadi": [-2.8000, 33.9833],
        "babati": [-4.2167, 35.7500],
        "kibaha": [-6.7667, 38.9667],
        "njombe": [-9.3333, 34.7667]
    };

    function getCoords(address) {
        const addr = address.toLowerCase().trim();
        for (const [key, coords] of Object.entries(tanzaniaLocations)) {
            if (addr.includes(key)) {
                return coords;
            }
        }
        return null;
    }

    async function geocodeAddress(address) {
        if (!address || address.trim().length < 3) return null;
        // Limit query to Tanzania (tz) to ensure relevant results
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&countrycodes=tz&limit=1`;
        try {
            const res = await fetch(url, { headers: { 'Accept-Language': 'sw,en' } });
            const data = await res.json();
            if (data && data.length > 0) {
                return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
            }
        } catch (e) {
            console.error("Nominatim Geocoding error:", e);
        }
        return null;
    }

    const pickupInput = document.getElementById('pickup_address');
    const deliveryInput = document.getElementById('delivery_address');
    const distInput = document.getElementById('distance_km');
    const priceEl = document.getElementById('price-estimate');
    const durationEl = document.getElementById('duration-estimate');
    const rate = <?= (float)($lorry['price_per_km'] ?? 0) ?>;

    let map = null;
    let routePolyline = null;
    let startMarker = null;
    let endMarker = null;

    function formatDurationJS(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);
        
        let parts = [];
        if (hours > 0) parts.push(hours + " hr" + (hours > 1 ? "s" : ""));
        if (minutes > 0) parts.push(minutes + " min" + (minutes > 1 ? "s" : ""));
        if (secs > 0 || parts.length === 0) parts.push(secs + " sec" + (secs > 1 ? "s" : ""));
        
        return parts.join(", ");
    }

    async function calculateRoute() {
        const pVal = pickupInput.value;
        const dVal = deliveryInput.value;
        if (!pVal || !dVal) return;

        priceEl.innerHTML = '<span class="text-muted"><i class="fa-solid fa-spinner fa-spin"></i> Geocoding...</span>';
        durationEl.innerHTML = '<span class="text-muted"><i class="fa-solid fa-spinner fa-spin"></i> Geocoding...</span>';

        let start = await geocodeAddress(pVal);
        let end = await geocodeAddress(dVal);

        // Fallback to dictionary
        if (!start) start = getCoords(pVal) || [-6.7924, 39.2083];
        if (!end) end = getCoords(dVal) || [-3.3869, 36.6830];

        // Query OSRM API for driving routing
        const osrmUrl = `https://router.projectosrm.org/route/v1/driving/${start[1]},${start[0]};${end[1]},${end[0]}?overview=full&geometries=geojson`;

        fetch(osrmUrl)
            .then(res => res.json())
            .then(data => {
                if (data.code === 'Ok' && data.routes && data.routes[0]) {
                    const route = data.routes[0];
                    const distanceKm = (route.distance / 1000).toFixed(1);
                    const durationSec = route.duration;

                    distInput.value = distanceKm;
                    priceEl.innerHTML = '<strong>' + (distanceKm * rate).toLocaleString() + ' TZS</strong>';
                    durationEl.innerHTML = '<strong>' + formatDurationJS(durationSec) + '</strong>';

                    // Initialize map
                    const mapDiv = document.getElementById('bookingRouteMap');
                    mapDiv.style.display = 'block';
                    if (!map) {
                        map = L.map('bookingRouteMap');
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                    }

                    // Reset layers
                    if (routePolyline) map.removeLayer(routePolyline);
                    if (startMarker) map.removeLayer(startMarker);
                    if (endMarker) map.removeLayer(endMarker);

                    startMarker = L.circleMarker(start, { radius: 6, fillColor: '#10b981', color: '#fff', weight: 2, fillOpacity: 1 }).addTo(map).bindPopup('Pickup: ' + pVal);
                    endMarker = L.circleMarker(end, { radius: 6, fillColor: '#ef4444', color: '#fff', weight: 2, fillOpacity: 1 }).addTo(map).bindPopup('Delivery: ' + dVal);
                    
                    const geojsonLayer = L.geoJSON(route.geometry, {
                        style: { color: '#2563eb', weight: 4, opacity: 0.8 }
                    });
                    routePolyline = geojsonLayer.addTo(map);

                    map.fitBounds(routePolyline.getBounds(), { padding: [20, 20] });
                }
            })
            .catch(err => {
                // Fallback Haversine straight line if OSRM is offline
                const r = 6371;
                const dLat = (end[0] - start[0]) * Math.PI / 180;
                const dLng = (end[1] - start[1]) * Math.PI / 180;
                const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                          Math.cos(start[0]*Math.PI/180) * Math.cos(end[0]*Math.PI/180) *
                          Math.sin(dLng/2) * Math.sin(dLng/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                const distanceKm = (r * c * 1.3).toFixed(1);
                const durationSec = (distanceKm / 55) * 3600;

                distInput.value = distanceKm;
                priceEl.innerHTML = '<strong>' + (distanceKm * rate).toLocaleString() + ' TZS</strong>';
                durationEl.innerHTML = '<strong>' + formatDurationJS(durationSec) + '</strong>';
            });
    }

    pickupInput.addEventListener('blur', calculateRoute);
    deliveryInput.addEventListener('blur', calculateRoute);
});
</script>
