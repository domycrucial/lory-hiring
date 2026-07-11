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
                    <div style="display: flex; gap: 8px;">
                        <input type="text" name="pickup_address" id="pickup_address" class="form-control" placeholder="e.g. Kariakoo Market, Dar es Salaam" required style="flex: 1;">
                        <button type="button" id="btn-pick-map" class="btn btn-outline" style="display: flex; align-items: center; gap: 6px; padding: 0 16px;" title="Pin location on map"><i class="fa-solid fa-map-pin"></i> Pin</button>
                    </div>
                </div>

                <!-- Input for delivery destination -->
                <div class="form-group">
                    <label class="form-label" for="delivery_address"><i class="fa-solid fa-location-crosshairs" style="color: var(--primary);"></i> Delivery Address</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" name="delivery_address" id="delivery_address" class="form-control" placeholder="e.g. Arusha Central" required style="flex: 1;">
                        <button type="button" id="btn-deliver-map" class="btn btn-outline" style="display: flex; align-items: center; gap: 6px; padding: 0 16px;" title="Pin location on map"><i class="fa-solid fa-map-pin"></i> Pin</button>
                    </div>
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

                <!-- Route Map display and instructions -->
                <div id="mapInstructions" style="font-size: 0.85rem; color: var(--gray-600); margin-bottom: var(--space-2); background: var(--gray-50); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: var(--space-2) var(--space-3); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    <span><strong>Tip:</strong> Click "Pin" next to an input then click on the map to set location, or drag markers to update the route!</span>
                </div>
                <div id="bookingRouteMap" style="height: 350px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: var(--space-4); z-index: 1;"></div>

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
        "kariakoo": [-6.8163, 39.2755],
        "kigamboni": [-6.8277, 39.3175],
        "ubungo": [-6.7887, 39.2083],
        "temeke": [-6.8524, 39.2678],
        "kinondoni": [-6.7824, 39.2244],
        "dar es salaam": [-6.7924, 39.2083],
        "dar": [-6.7924, 39.2083],
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

    const btnPick = document.getElementById('btn-pick-map');
    const btnDeliver = document.getElementById('btn-deliver-map');

    let map = null;
    let routePolyline = null;
    let startMarker = null;
    let endMarker = null;
    let startCoords = null;
    let endCoords = null;
    let pinMode = null; // 'pickup' or 'delivery'

    // Initialize map immediately centered on the lorry's location or Dar es Salaam
    const lorryLat = <?= (float)($lorry['latitude'] ?? -6.7924) ?>;
    const lorryLon = <?= (float)($lorry['longitude'] ?? 39.2083) ?>;
    map = L.map('bookingRouteMap').setView([lorryLat, lorryLon], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Green FontAwesome Icon for pickup marker
    const greenIcon = L.divIcon({
        html: '<i class="fa-solid fa-location-dot fa-2x" style="color: #10b981; text-shadow: 0 1px 3px rgba(0,0,0,0.3);"></i>',
        iconSize: [24, 24],
        iconAnchor: [12, 24],
        popupAnchor: [0, -24],
        className: 'custom-map-icon-pickup'
    });

    // Red FontAwesome Icon for delivery marker
    const redIcon = L.divIcon({
        html: '<i class="fa-solid fa-location-dot fa-2x" style="color: #ef4444; text-shadow: 0 1px 3px rgba(0,0,0,0.3);"></i>',
        iconSize: [24, 24],
        iconAnchor: [12, 24],
        popupAnchor: [0, -24],
        className: 'custom-map-icon-delivery'
    });

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

    function updateMarkers() {
        if (startCoords) {
            if (!startMarker) {
                startMarker = L.marker(startCoords, { icon: greenIcon, draggable: true }).addTo(map);
                startMarker.bindPopup('<b>Pickup / Pa Kuchukulia</b><br>Drag me to change location.');
                startMarker.on('dragend', function(e) {
                    const latlng = e.target.getLatLng();
                    startCoords = [latlng.lat, latlng.lng];
                    pickupInput.value = `📍 Map Location (${latlng.lat.toFixed(5)}, ${latlng.lng.toFixed(5)})`;
                    updateRoute();
                });
            } else {
                startMarker.setLatLng(startCoords);
            }
        } else if (startMarker) {
            map.removeLayer(startMarker);
            startMarker = null;
        }

        if (endCoords) {
            if (!endMarker) {
                endMarker = L.marker(endCoords, { icon: redIcon, draggable: true }).addTo(map);
                endMarker.bindPopup('<b>Delivery / Mahali pa Kupeleka</b><br>Drag me to change location.');
                endMarker.on('dragend', function(e) {
                    const latlng = e.target.getLatLng();
                    endCoords = [latlng.lat, latlng.lng];
                    deliveryInput.value = `📍 Map Location (${latlng.lat.toFixed(5)}, ${latlng.lng.toFixed(5)})`;
                    updateRoute();
                });
            } else {
                endMarker.setLatLng(endCoords);
            }
        } else if (endMarker) {
            map.removeLayer(endMarker);
            endMarker = null;
        }
    }

    async function updateRoute() {
        if (!startCoords || !endCoords) return;

        priceEl.innerHTML = '<span class="text-muted"><i class="fa-solid fa-spinner fa-spin"></i> Calculating route...</span>';
        durationEl.innerHTML = '<span class="text-muted"><i class="fa-solid fa-spinner fa-spin"></i> Calculating route...</span>';

        const osrmUrl = `https://router.projectosrm.org/route/v1/driving/${startCoords[1]},${startCoords[0]};${endCoords[1]},${endCoords[0]}?overview=full&geometries=geojson`;

        try {
            const res = await fetch(osrmUrl);
            const data = await res.json();
            if (data.code === 'Ok' && data.routes && data.routes[0]) {
                const route = data.routes[0];
                const distanceKm = (route.distance / 1000).toFixed(1);
                const durationSec = route.duration;

                distInput.value = distanceKm;
                priceEl.innerHTML = '<strong>' + (distanceKm * rate).toLocaleString() + ' TZS</strong>';
                durationEl.innerHTML = '<strong>' + formatDurationJS(durationSec) + '</strong>';

                if (routePolyline) map.removeLayer(routePolyline);
                
                const geojsonLayer = L.geoJSON(route.geometry, {
                    style: { color: '#2563eb', weight: 4, opacity: 0.8 }
                });
                routePolyline = geojsonLayer.addTo(map);

                updateMarkers();
                map.fitBounds(routePolyline.getBounds(), { padding: [40, 40] });
            } else {
                throw new Error("OSRM routing failed");
            }
        } catch (err) {
            console.error("OSRM error, falling back to Haversine straight line:", err);
            const r = 6371;
            const dLat = (endCoords[0] - startCoords[0]) * Math.PI / 180;
            const dLng = (endCoords[1] - startCoords[1]) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(startCoords[0]*Math.PI/180) * Math.cos(endCoords[0]*Math.PI/180) *
                      Math.sin(dLng/2) * Math.sin(dLng/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const distanceKm = (r * c * 1.3).toFixed(1);
            const durationSec = (distanceKm / 55) * 3600;

            distInput.value = distanceKm;
            priceEl.innerHTML = '<strong>' + (distanceKm * rate).toLocaleString() + ' TZS</strong>';
            durationEl.innerHTML = '<strong>' + formatDurationJS(durationSec) + '</strong>';

            if (routePolyline) map.removeLayer(routePolyline);
            routePolyline = L.polyline([startCoords, endCoords], { color: '#ef4444', weight: 4, dashArray: '5, 10', opacity: 0.8 }).addTo(map);
            
            updateMarkers();
            map.fitBounds(routePolyline.getBounds(), { padding: [40, 40] });
        }
    }

    async function handleTextInputChange() {
        const pVal = pickupInput.value.trim();
        const dVal = deliveryInput.value.trim();
        
        let changed = false;

        if (pVal && !pVal.startsWith('📍')) {
            let start = await geocodeAddress(pVal);
            if (!start) start = getCoords(pVal);
            if (start) {
                startCoords = start;
                changed = true;
            }
        }

        if (dVal && !dVal.startsWith('📍')) {
            let end = await geocodeAddress(dVal);
            if (!end) end = getCoords(dVal);
            if (end) {
                endCoords = end;
                changed = true;
            }
        }

        if (changed) {
            updateMarkers();
            if (startCoords && endCoords) {
                updateRoute();
            } else if (startCoords) {
                map.setView(startCoords, 10);
            } else if (endCoords) {
                map.setView(endCoords, 10);
            }
        }
    }

    pickupInput.addEventListener('blur', handleTextInputChange);
    deliveryInput.addEventListener('blur', handleTextInputChange);

    // Map pinning controls
    btnPick.addEventListener('click', function(e) {
        e.preventDefault();
        if (pinMode === 'pickup') {
            resetPinMode();
        } else {
            setPinMode('pickup');
        }
    });

    btnDeliver.addEventListener('click', function(e) {
        e.preventDefault();
        if (pinMode === 'delivery') {
            resetPinMode();
        } else {
            setPinMode('delivery');
        }
    });

    function setPinMode(mode) {
        resetPinMode();
        pinMode = mode;
        const mapDiv = document.getElementById('bookingRouteMap');
        mapDiv.style.cursor = 'crosshair';
        
        if (mode === 'pickup') {
            btnPick.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Click Map';
            btnPick.classList.remove('btn-outline');
            btnPick.classList.add('btn-accent');
        } else {
            btnDeliver.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Click Map';
            btnDeliver.classList.remove('btn-outline');
            btnDeliver.classList.add('btn-accent');
        }
    }

    function resetPinMode() {
        pinMode = null;
        const mapDiv = document.getElementById('bookingRouteMap');
        mapDiv.style.cursor = '';
        
        btnPick.innerHTML = '<i class="fa-solid fa-map-pin"></i> Pin';
        btnPick.classList.remove('btn-accent');
        btnPick.classList.add('btn-outline');

        btnDeliver.innerHTML = '<i class="fa-solid fa-map-pin"></i> Pin';
        btnDeliver.classList.remove('btn-accent');
        btnDeliver.classList.add('btn-outline');
    }

    map.on('click', function(e) {
        if (!pinMode) return;
        
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        if (pinMode === 'pickup') {
            startCoords = [lat, lng];
            pickupInput.value = `📍 Map Location (${lat.toFixed(5)}, ${lng.toFixed(5)})`;
        } else if (pinMode === 'delivery') {
            endCoords = [lat, lng];
            deliveryInput.value = `📍 Map Location (${lat.toFixed(5)}, ${lng.toFixed(5)})`;
        }
        
        resetPinMode();
        updateMarkers();
        
        if (startCoords && endCoords) {
            updateRoute();
        } else if (startCoords) {
            map.panTo(startCoords);
        } else if (endCoords) {
            map.panTo(endCoords);
        }
    });

    // Form submit validation
    const form = document.getElementById('booking-form');
    form.addEventListener('submit', function(e) {
        const distance = parseFloat(distInput.value);
        if (!distance || distance <= 0) {
            e.preventDefault();
            alert("Please calculate the distance by selecting valid pickup and delivery locations first. / Tafadhali chagua maeneo sahihi ya kuchukua na kupeleka mzigo kwanza.");
        }
    });
});
</script>
