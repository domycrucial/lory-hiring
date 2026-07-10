
        <!-- Main welcome heading -->
        <h1><i class="fa-solid fa-chart-pie"></i> <?= currentLang() === 'sw' ? 'Dawati la Mteja' : 'Customer Dashboard' ?></h1>
        <p class="text-muted mb-6"><?= currentLang() === 'sw' ? 'Karibu tena' : 'Welcome back' ?>, <?= e(currentUserName()) ?>!</p>

        <!-- Stats Metric Cards Row -->
        <div class="grid grid-3 mb-8">
            <div class="stat-card">
                <div class="stat-value"><?= (int)($stats['total_bookings'] ?? 0) ?></div>
                <div class="stat-label"><?= currentLang() === 'sw' ? 'Jumla ya Uhifadhi' : 'Total Bookings' ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--warning);"><?= (int)($stats['pending'] ?? 0) ?></div>
                <div class="stat-label"><?= currentLang() === 'sw' ? 'Inayosubiri' : 'Pending Bookings' ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: var(--success);"><?= (int)($stats['completed'] ?? 0) ?></div>
                <div class="stat-label"><?= currentLang() === 'sw' ? 'Safari Zilizokamilika' : 'Completed Trips' ?></div>
            </div>
        </div>

        <!-- Live Tracking Map Section -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <div class="card p-6 mb-8" style="margin-bottom: var(--space-8);">
            <h3><i class="fa-solid fa-map-location-dot text-primary"></i> <?= currentLang() === 'sw' ? 'Ramani ya Ufuatiliaji (Muda Halisi)' : 'Live Delivery Tracking Map (Real-time)' ?></h3>
            <p class="text-muted text-sm">
                <?php if (!empty($activeBookings)): ?>
                    <?= currentLang() === 'sw' ? 'Fuatilia mizigo yako ya sasa inaposafirishwa kwenda unakotaka.' : 'Track your active cargo transits as they travel to their destinations.' ?>
                <?php else: ?>
                    <?= currentLang() === 'sw' ? 'Tazama malori yanayopatikana karibu nawe nchini Tanzania. Bonyeza alama kuona maelezo.' : 'View available transport vehicles stationed across Tanzania. Click on a marker to see details.' ?>
                <?php endif; ?>
            </p>
            <div id="customerTrackingMap" style="height: 400px; border-radius: var(--radius-lg); margin-top: var(--space-4); border: 1px solid var(--border-color); z-index: 1;"></div>
        </div>

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
                const addr = address.toLowerCase();
                for (const [key, coords] of Object.entries(tanzaniaLocations)) {
                    if (addr.includes(key)) {
                        return [coords[0] + (Math.random() - 0.5) * 0.02, coords[1] + (Math.random() - 0.5) * 0.02];
                    }
                }
                return [-6.7924 + (Math.random() - 0.5) * 0.1, 39.2083 + (Math.random() - 0.5) * 0.1];
            }

            const map = L.map('customerTrackingMap').setView([-6.3690, 34.8888], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const activeBookings = <?= json_encode($activeBookings) ?>;
            const availableLorries = <?= json_encode($availableLorries) ?>;

            if (activeBookings.length > 0) {
                // Track Customer's active trips
                activeBookings.forEach(function(b) {
                    const start = getCoords(b.pickup_address);
                    const end = getCoords(b.delivery_address);

                    // Draw route line
                    L.polyline([start, end], {
                        color: b.status === 'in_transit' ? '#2563eb' : '#f59e0b',
                        weight: 4,
                        opacity: 0.8,
                        dashArray: '5, 10'
                    }).addTo(map);

                    // Start Marker
                    L.circleMarker(start, {
                        radius: 8,
                        fillColor: '#10b981',
                        color: '#fff',
                        weight: 2,
                        fillOpacity: 1
                    }).addTo(map).bindPopup(`<b>Pickup / Pa Kuanzia</b><br>${b.pickup_address}`);

                    // End Marker
                    L.circleMarker(end, {
                        radius: 8,
                        fillColor: '#ef4444',
                        color: '#fff',
                        weight: 2,
                        fillOpacity: 1
                    }).addTo(map).bindPopup(`<b>Destination / Unapokwenda</b><br>${b.delivery_address}`);

                    // Simulated Live Lorry Marker with real lorry photo circular layout
                    let progress = b.status === 'in_transit' ? 0.35 : 0.05;
                    const currentLat = start[0] + (end[0] - start[0]) * progress;
                    const currentLng = start[1] + (end[1] - start[1]) * progress;

                    let imgUrl = b.lorry_photo ? ('<?= APP_URL ?>/storage/lorries/' + b.lorry_photo) : '<?= APP_URL ?>/public/images/default-lorry.png';
                    if (b.lorry_photo && (b.lorry_photo.indexOf('http') === 0 || b.lorry_photo.indexOf('/storage/') === 0)) {
                        imgUrl = b.lorry_photo.indexOf('http') === 0 ? b.lorry_photo : ('<?= APP_URL ?>' + b.lorry_photo);
                    }
                    const borderColor = b.status === 'in_transit' ? '#2563eb' : '#f59e0b';
                    const lorryIcon = L.divIcon({
                        html: `<div style="width: 38px; height: 38px; border-radius: 50%; border: 3px solid ${borderColor}; background: url('${imgUrl}') center/cover no-repeat; box-shadow: 0 2px 6px rgba(0,0,0,0.35); outline: none;"></div>`,
                        iconSize: [38, 38],
                        className: 'lorry-map-icon'
                    });

                    const lorryMarker = L.marker([currentLat, currentLng], { icon: lorryIcon }).addTo(map);
                    
                    function estimateDurationJS(distanceKm, lang = 'en') {
                        const seconds = (distanceKm / 55) * 3600;
                        const hours = Math.floor(seconds / 3600);
                        const minutes = Math.floor((seconds % 3600) / 60);
                        const secs = Math.floor(seconds % 60);
                        if (lang === 'sw') {
                            let parts = [];
                            if (hours > 0) parts.push(hours + " saa");
                            if (minutes > 0) parts.push(minutes + " dak");
                            if (secs > 0 || parts.length === 0) parts.push(secs + " sek");
                            return parts.join(", ");
                        } else {
                            let parts = [];
                            if (hours > 0) parts.push(hours + " hr" + (hours > 1 ? "s" : ""));
                            if (minutes > 0) parts.push(minutes + " min" + (minutes > 1 ? "s" : ""));
                            if (secs > 0 || parts.length === 0) parts.push(secs + " sec" + (secs > 1 ? "s" : ""));
                            return parts.join(", ");
                        }
                    }

                    const popupHtml = `
                        <div style="font-family: sans-serif; font-size: 0.85rem; line-height: 1.4; min-width: 180px;">
                            <strong style="color: var(--primary); font-size: 0.9rem;">Booking Ref: ${b.booking_ref}</strong><br>
                            <strong>Lorry:</strong> ${b.lorry_name} (<code>${b.plate_number}</code>)<br>
                            <strong>Driver/Owner:</strong> ${b.owner_name} (<a href="tel:${b.owner_phone}">${b.owner_phone}</a>)<br>
                            <strong>Distance:</strong> ${b.distance_km} km<br>
                            <strong>Est. Duration:</strong> ${estimateDurationJS(b.distance_km, '<?= currentLang() ?>')}<br>
                            <strong>Status:</strong> <span style="text-transform: capitalize; color: ${borderColor}; font-weight: bold;">${b.status}</span><br>
                            <strong>Goods:</strong> ${b.goods_description || 'General Cargo'}
                        </div>
                    `;
                    lorryMarker.bindPopup(popupHtml).openPopup();

                    // Animate lorry movement
                    if (b.status === 'in_transit') {
                        let direction = 1;
                        setInterval(() => {
                            progress += 0.005 * direction;
                            if (progress >= 0.9) { direction = -1; }
                            if (progress <= 0.1) { direction = 1; }
                            const nextLat = start[0] + (end[0] - start[0]) * progress;
                            const nextLng = start[1] + (end[1] - start[1]) * progress;
                            lorryMarker.setLatLng([nextLat, nextLng]);
                        }, 2000);
                    }
                });
            } else {
                // No active bookings - plot available trucks across major Tanzania hubs
                const hubs = Object.keys(tanzaniaLocations);
                availableLorries.forEach(function(l, index) {
                    const hubName = hubs[index % hubs.length];
                    const baseCoords = tanzaniaLocations[hubName];
                    const coords = [baseCoords[0] + (Math.random() - 0.5) * 0.2, baseCoords[1] + (Math.random() - 0.5) * 0.2];

                    const truckIcon = L.divIcon({
                        html: '<i class="fa-solid fa-truck-moving" style="color: #10b981; font-size: 18px; text-shadow: 0 0 3px #fff;"></i>',
                        iconSize: [24, 24],
                        className: 'lorry-avail-icon'
                    });

                    L.marker(coords, { icon: truckIcon }).addTo(map)
                        .bindPopup(`
                            <div style="font-family: sans-serif; font-size: 0.85rem;">
                                <strong style="color: var(--success); font-size: 0.95rem;">${l.name}</strong><br>
                                <strong>Rate/km:</strong> ${Number(l.price_per_km).toLocaleString()} TZS<br>
                                <strong>Owner:</strong> ${l.owner_name}<br>
                                <strong>Phone:</strong> <a href="tel:${l.owner_phone}">${l.owner_phone}</a><br>
                                <hr style="margin: 8px 0; border: 0; border-top: 1px solid #ddd;">
                                <a href="<?= APP_URL ?>/lorries/search" style="display: block; text-align: center; background: var(--primary); color: #fff; text-decoration: none; padding: 4px 8px; border-radius: 4px; font-weight: bold;">Book Now</a>
                            </div>
                        `);
                });
            }
        });
        </script>

        <!-- Recent Bookings section -->
        <h2><i class="fa-solid fa-clock"></i> <?= currentLang() === 'sw' ? 'Uhifadhi wa Hivi Karibuni' : 'Recent Bookings' ?></h2>
        <?php if (empty($recentBookings)): ?>
            <div class="card p-6 text-center mt-4">
                <p class="text-muted"><i class="fa-solid fa-inbox"></i> <?= currentLang() === 'sw' ? 'Bado haujafanya uhifadhi wowote.' : "You haven't made any bookings yet." ?> <a href="<?= APP_URL ?>/lorries/search"><?= currentLang() === 'sw' ? 'Tafuta lori' : 'Search for a lorry' ?></a> <?= currentLang() === 'sw' ? 'ili kuanza!' : 'to get started!' ?></p>
            </div>
        <?php else: ?>
            <div class="table-wrapper mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= currentLang() === 'sw' ? 'Kumbukumbu' : 'Ref' ?></th>
                            <th><?= currentLang() === 'sw' ? 'Mahali pa kuchukua' : 'Pickup Address' ?></th>
                            <th><?= currentLang() === 'sw' ? 'Gharama' : 'Price' ?></th>
                            <th><?= currentLang() === 'sw' ? 'Hali' : 'Status' ?></th>
                            <th><?= currentLang() === 'sw' ? 'Tarehe' : 'Date' ?></th>
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
                            <td><strong><?= number_format((float)($b['quoted_price'] ?? 0)) ?> TZS</strong></td>
                            <td>
                                <!-- Status badge -->
                                <span class="badge status-<?= e($b['status'] ?? 'pending') ?>">
                                    <?= e(bookingStatusLabel($b['status'] ?? 'pending', currentLang())) ?>
                                </span>
                            </td>
                            <td><?= formatDate($b['preferred_date'] ?? null) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

