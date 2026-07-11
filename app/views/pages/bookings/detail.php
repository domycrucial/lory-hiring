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
                
                <!-- Goods description row -->
                <tr><td class="font-bold"><?= currentLang() === 'sw' ? 'Maelezo ya Mzigo' : 'Goods Description' ?></td><td><?= e($booking['goods_description'] ?? 'General Cargo') ?></td></tr>
                
                <!-- Conditionally render Lorry Nickname -->
                <?php if (!empty($booking['lorry_name'])): ?>
                <tr><td class="font-bold"><?= currentLang() === 'sw' ? 'Lori' : 'Lorry' ?></td><td><?= e($booking['lorry_name']) ?> (<code><?= e($booking['plate_number']) ?></code>)</td></tr>
                <?php endif; ?>
                
                <!-- Conditionally render Lorry Owner details for Customer -->
                <?php if (currentUserRole() === 'customer' && !empty($booking['owner_name'])): ?>
                <tr><td class="font-bold"><?= currentLang() === 'sw' ? 'Mmiliki wa Lori' : 'Lorry Owner' ?></td><td><strong><?= e($booking['owner_name']) ?></strong> (<a href="tel:<?= e($booking['owner_phone']) ?>"><?= e($booking['owner_phone']) ?></a>)</td></tr>
                <?php endif; ?>
                
                <!-- Conditionally render Customer Name for Owner/Admin -->
                <?php if (!empty($booking['customer_name']) && currentUserRole() !== 'customer'): ?>
                <tr><td class="font-bold"><?= currentLang() === 'sw' ? 'Mteja' : 'Customer' ?></td><td><?= e($booking['customer_name']) ?> (<a href="tel:<?= e($booking['customer_phone']) ?>"><?= e($booking['customer_phone']) ?></a>)</td></tr>
                <?php endif; ?>
                
                <!-- Booking creation date row -->
                <tr><td class="font-bold">Created At</td><td><?= formatDateTime($booking['created_at'] ?? null) ?></td></tr>
            </table>

            <!-- Interactive Map -->
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <div style="margin-top: 24px;">
                <h4 class="mb-2"><i class="fa-solid fa-map-location-dot"></i> <?= currentLang() === 'sw' ? 'Njia ya Usafirishaji' : 'Delivery Route Map' ?></h4>
                <div id="bookingDetailMap" style="height: 300px; border-radius: var(--radius-md); border: 1px solid var(--border-color); z-index: 1;"></div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', async function() {
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
                    const addr = address.toLowerCase();
                    for (const [key, coords] of Object.entries(tanzaniaLocations)) {
                        if (addr.includes(key)) {
                            return [coords[0] + (Math.random() - 0.5) * 0.02, coords[1] + (Math.random() - 0.5) * 0.02];
                        }
                    }
                    return [-6.7924 + (Math.random() - 0.5) * 0.1, 39.2083 + (Math.random() - 0.5) * 0.1];
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

                const pickupAddr = <?= json_encode($booking['pickup_address']) ?>;
                const deliveryAddr = <?= json_encode($booking['delivery_address']) ?>;

                let start = await geocodeAddress(pickupAddr);
                let end = await geocodeAddress(deliveryAddr);

                if (!start) start = getCoords(pickupAddr);
                if (!end) end = getCoords(deliveryAddr);

                // Initialize map centered between start and end
                const map = L.map('bookingDetailMap').setView([
                    (start[0] + end[0]) / 2,
                    (start[1] + end[1]) / 2
                ], 7);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Start marker
                L.circleMarker(start, {
                    radius: 8,
                    fillColor: '#10b981',
                    color: '#fff',
                    weight: 2,
                    fillOpacity: 1
                }).addTo(map).bindPopup(`<b>Pickup / Pa Kuanzia</b><br>${pickupAddr}`);

                // End marker
                L.circleMarker(end, {
                    radius: 8,
                    fillColor: '#ef4444',
                    color: '#fff',
                    weight: 2,
                    fillOpacity: 1
                }).addTo(map).bindPopup(`<b>Destination / Mwisho</b><br>${deliveryAddr}`);

                // Check booking status
                const status = <?= json_encode($booking['status']) ?>;
                const photoPath = <?= json_encode($booking['lorry_photo'] ?? null) ?>;
                const distanceKm = <?= (float)($booking['distance_km'] ?? 0) ?>;

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

                // Query OSRM API for real road routing
                const osrmUrl = `https://router.projectosrm.org/route/v1/driving/${start[1]},${start[0]};${end[1]},${end[0]}?overview=full&geometries=geojson`;

                fetch(osrmUrl)
                    .then(res => res.json())
                    .then(data => {
                        if (data.code === 'Ok' && data.routes && data.routes[0]) {
                            const route = data.routes[0];
                            const geometry = route.geometry;

                            // Draw real route
                            const routePolyline = L.geoJSON(geometry, {
                                style: { color: '#2563eb', weight: 4, opacity: 0.8 }
                            }).addTo(map);

                            map.fitBounds(routePolyline.getBounds(), { padding: [30, 30] });

                            if (status === 'accepted' || status === 'in_transit' || status === 'completed') {
                                let progress = status === 'completed' ? 1.0 : (status === 'in_transit' ? 0.45 : 0.05);
                                const coordinates = geometry.coordinates; // Array of [lng, lat]
                                const pointCount = coordinates.length;
                                const targetIndex = Math.floor((pointCount - 1) * progress);
                                const currentCoords = [coordinates[targetIndex][1], coordinates[targetIndex][0]];

                                // Custom Lorry Icon with real photo
                                let imgUrl = photoPath ? ('<?= APP_URL ?>/storage/lorries/' + photoPath) : '<?= APP_URL ?>/public/images/default-lorry.png';
                                if (photoPath && (photoPath.indexOf('http') === 0 || photoPath.indexOf('/storage/') === 0)) {
                                    imgUrl = photoPath.indexOf('http') === 0 ? photoPath : ('<?= APP_URL ?>' + photoPath);
                                }
                                const borderColor = status === 'in_transit' ? '#2563eb' : '#f59e0b';
                                const lorryIcon = L.divIcon({
                                    html: `<div style="width: 38px; height: 38px; border-radius: 50%; border: 3px solid ${borderColor}; background: url('${imgUrl}') center/cover no-repeat; box-shadow: 0 2px 6px rgba(0,0,0,0.35); outline: none;"></div>`,
                                    iconSize: [38, 38],
                                    className: 'lorry-map-icon'
                                });

                                const lorryMarker = L.marker(currentCoords, { icon: lorryIcon }).addTo(map);
                                
                                const popupHtml = `
                                    <div style="font-family: sans-serif; font-size: 0.85rem; line-height: 1.4; min-width: 150px;">
                                        <strong>Lorry:</strong> <?= e($booking['lorry_name'] ?? 'Transit Truck') ?><br>
                                        <strong>Distance:</strong> ${distanceKm} km<br>
                                        <strong>Est. Time:</strong> ${estimateDurationJS(distanceKm, '<?= currentLang() ?>')}<br>
                                        <strong>Status:</strong> <span style="text-transform: capitalize; color: ${borderColor}; font-weight: bold;">${status}</span>
                                    </div>
                                `;
                                lorryMarker.bindPopup(popupHtml).openPopup();

                                // Animate movement along real roads
                                if (status === 'in_transit') {
                                    let index = targetIndex;
                                    let dir = 1;
                                    setInterval(() => {
                                        index += dir;
                                        if (index >= pointCount - 1) { dir = -1; }
                                        if (index <= 0) { dir = 1; }
                                        const nextCoords = [coordinates[index][1], coordinates[index][0]];
                                        lorryMarker.setLatLng(nextCoords);
                                    }, 1000);
                                }
                            }
                        }
                    })
                    .catch(err => {
                        // Fallback to straight line
                        const routeLine = L.polyline([start, end], {
                            color: '#2563eb',
                            weight: 4,
                            opacity: 0.8,
                            dashArray: '5, 10'
                        }).addTo(map);

                        if (status === 'accepted' || status === 'in_transit' || status === 'completed') {
                            let progress = status === 'completed' ? 1.0 : (status === 'in_transit' ? 0.45 : 0.05);
                            const currentLat = start[0] + (end[0] - start[0]) * progress;
                            const currentLng = start[1] + (end[1] - start[1]) * progress;

                            let imgUrl = photoPath ? ('<?= APP_URL ?>/storage/lorries/' + photoPath) : '<?= APP_URL ?>/public/images/default-lorry.png';
                            if (photoPath && (photoPath.indexOf('http') === 0 || photoPath.indexOf('/storage/') === 0)) {
                                imgUrl = photoPath.indexOf('http') === 0 ? photoPath : ('<?= APP_URL ?>' + photoPath);
                            }
                            const borderColor = status === 'in_transit' ? '#2563eb' : '#f59e0b';
                            const lorryIcon = L.divIcon({
                                html: `<div style="width: 38px; height: 38px; border-radius: 50%; border: 3px solid ${borderColor}; background: url('${imgUrl}') center/cover no-repeat; box-shadow: 0 2px 6px rgba(0,0,0,0.35); outline: none;"></div>`,
                                iconSize: [38, 38],
                                className: 'lorry-map-icon'
                            });

                            const lorryMarker = L.marker([currentLat, currentLng], { icon: lorryIcon }).addTo(map);
                            lorryMarker.bindPopup(`
                                <strong>Lorry:</strong> <?= e($booking['lorry_name'] ?? 'Transit Truck') ?><br>
                                <strong>Distance:</strong> ${distanceKm} km<br>
                                <strong>Est. Time:</strong> ${estimateDurationJS(distanceKm, '<?= currentLang() ?>')}<br>
                                <strong>Status:</strong> <span style="text-transform: capitalize; color: ${borderColor}; font-weight: bold;">${status}</span>
                            `).openPopup();
                        }
                    });
            });

            let selectedMethod = null;
            let currentWalletBalance = 0;

            function selectPaymentOption(method) {
                selectedMethod = method;
                const optWallet = document.getElementById('pay-opt-wallet');
                const optMobile = document.getElementById('pay-opt-mobile');
                const panelWallet = document.getElementById('panel-wallet');
                const panelMobile = document.getElementById('panel-mobile');

                if (method === 'wallet') {
                    optWallet.style.borderColor = 'var(--primary)';
                    optWallet.style.background = 'var(--primary-bg)';
                    optMobile.style.borderColor = 'var(--border-color)';
                    optMobile.style.background = '';
                    panelWallet.style.display = 'block';
                    panelMobile.style.display = 'none';
                } else {
                    optMobile.style.borderColor = 'var(--primary)';
                    optMobile.style.background = 'var(--primary-bg)';
                    optWallet.style.borderColor = 'var(--border-color)';
                    optWallet.style.background = '';
                    panelMobile.style.display = 'block';
                    panelWallet.style.display = 'none';
                }
            }

            function depositSimulation(requiredAmount) {
                const depositAmount = requiredAmount - currentWalletBalance;
                const toDeposit = depositAmount > 0 ? depositAmount : 100000;
                
                Swal.showLoading();
                
                const formData = new FormData();
                formData.append('amount', toDeposit);
                
                fetch('<?= APP_URL ?>/api/v1/wallet/deposit', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        currentWalletBalance = res.balance;
                        const balanceEl = document.getElementById('modal-wallet-balance');
                        if (balanceEl) balanceEl.innerText = Number(res.balance).toLocaleString() + ' TZS';
                        
                        const panelWallet = document.getElementById('panel-wallet');
                        if (panelWallet) {
                            panelWallet.innerHTML = `
                               <div class="form-group" style="margin-bottom: 0;">
                                   <label style="font-weight: 600; font-size: 0.9rem; display: block; margin-bottom: 6px;">Enter Amount to Pay (TZS)</label>
                                   <input type="number" id="pay-wallet-amount" class="form-control" value="${requiredAmount}" readonly style="background: var(--gray-100);">
                                   <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 6px; margin-bottom: 0;">Your balance after payment: <strong id="val-balance-after">${Number(res.balance - requiredAmount).toLocaleString()} TZS</strong></p>
                               </div>
                            `;
                        }
                        Swal.hideLoading();
                        Swal.resetValidationMessage();
                    } else {
                        Swal.showValidationMessage(res.message);
                    }
                });
            }

            function openPaymentModal(bookingId, bookingRef, quotedPrice, customerPhone) {
                selectedMethod = null;
                currentWalletBalance = 0;
                
                Swal.fire({
                    title: 'Checking Balance...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('<?= APP_URL ?>/api/v1/wallet/balance')
                    .then(r => r.json())
                    .then(res => {
                        if (!res.success) {
                            Swal.fire('Error', 'Could not retrieve wallet balance.', 'error');
                            return;
                        }
                        
                        currentWalletBalance = res.balance;
                        
                        const modalHtml = `
                           <div class="swal-payment-modal" style="text-align: left; font-family: inherit;">
                               <p style="margin-bottom: 20px;">Ref: <strong>${bookingRef}</strong> | Amount: <strong class="text-primary">${Number(quotedPrice).toLocaleString()} TZS</strong></p>
                               
                               <div style="display: flex; gap: 16px; margin-bottom: 20px;">
                                   <!-- Wallet Option -->
                                   <div id="pay-opt-wallet" style="flex: 1; border: 2px solid var(--border-color); border-radius: 8px; padding: 16px; text-align: center; cursor: pointer; transition: all 0.2s;" onclick="selectPaymentOption('wallet')">
                                       <i class="fa-solid fa-wallet fa-2x" style="color: var(--primary); margin-bottom: 8px;"></i>
                                       <div style="font-weight: bold;">My Wallet</div>
                                       <div style="font-size: 0.85rem; color: var(--gray-500); margin-top: 4px;">Balance: <span id="modal-wallet-balance">${Number(res.balance).toLocaleString()} TZS</span></div>
                                   </div>
                                   
                                   <!-- Mobile Money Option -->
                                   <div id="pay-opt-mobile" style="flex: 1; border: 2px solid var(--border-color); border-radius: 8px; padding: 16px; text-align: center; cursor: pointer; transition: all 0.2s;" onclick="selectPaymentOption('mobile')">
                                       <i class="fa-solid fa-mobile-screen-button fa-2x" style="color: var(--accent); margin-bottom: 8px;"></i>
                                       <div style="font-weight: bold;">Lipa kwa Simu</div>
                                       <div style="font-size: 0.85rem; color: var(--gray-500); margin-top: 4px;">Vodacom M-Pesa</div>
                                   </div>
                               </div>

                               <!-- Wallet Details Panel -->
                               <div id="panel-wallet" style="display: none; background: var(--gray-50); border: 1px solid var(--border-color); padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                                   <div class="form-group" style="margin-bottom: 0;">
                                       <label style="font-weight: 600; font-size: 0.9rem; display: block; margin-bottom: 6px;">Enter Amount to Pay (TZS)</label>
                                       <input type="number" id="pay-wallet-amount" class="form-control" value="${quotedPrice}" readonly style="background: var(--gray-100);">
                                       <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 6px; margin-bottom: 0;">Your balance after payment: <strong id="val-balance-after">${Number(res.balance - quotedPrice).toLocaleString()} TZS</strong></p>
                                       ${res.balance < quotedPrice ? `
                                       <div id="insufficient-funds-warning" style="margin-top: 12px; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                           <span style="color: var(--danger); font-size: 0.8rem; font-weight: bold;"><i class="fa-solid fa-circle-exclamation"></i> Insufficient funds!</span>
                                           <button type="button" class="btn btn-primary btn-sm" onclick="depositSimulation(${quotedPrice})"><i class="fa-solid fa-plus"></i> Deposit Test Funds</button>
                                       </div>
                                       ` : ''}
                                   </div>
                               </div>

                               <!-- Mobile Payment Panel -->
                               <div id="panel-mobile" style="display: none; background: var(--gray-50); border: 1px solid var(--border-color); padding: 16px; border-radius: 8px; text-align: center; margin-bottom: 20px;">
                                   <p style="font-size: 0.85rem; margin-top: 0; margin-bottom: 12px;">Scan the QR code below or use Lipa Namba to complete the payment via Vodacom M-Pesa.</p>
                                   <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=LIPA%20KWA%20SIMU%20-%20OLHS%20-%20Booking:%20${bookingRef}%20-%20Amount:%20${quotedPrice}%20TZS" alt="Lipa kwa Simu QR Code" style="border: 4px solid white; box-shadow: var(--shadow-sm); border-radius: 4px; margin-bottom: 12px; width: 180px; height: 180px;">
                                   <div style="font-weight: 800; font-size: 1.15rem; color: var(--primary);">LIPA NAMBA: 556677</div>
                                   <div style="font-size: 0.8rem; color: var(--gray-600); margin-top: 4px;">Merchant Name: <strong>OLHS Lorry Hiring</strong></div>
                                   
                                   <div class="form-group" style="margin-top: 16px; text-align: left; margin-bottom: 0;">
                                       <label style="font-weight: 600; font-size: 0.9rem; display: block; margin-bottom: 6px;">Your Vodacom M-Pesa Number</label>
                                       <input type="text" id="pay-mobile-number" class="form-control" placeholder="e.g. +255754321098" value="${customerPhone}">
                                   </div>
                               </div>
                           </div>
                        `;

                        Swal.fire({
                            title: 'Make Payment / Fanya Malipo',
                            html: modalHtml,
                            showCancelButton: true,
                            confirmButtonColor: '#2563eb',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Confirm Payment',
                            cancelButtonText: 'Cancel',
                            width: '500px',
                            preConfirm: () => {
                                if (!selectedMethod) {
                                    Swal.showValidationMessage('Please select a payment method');
                                    return false;
                                }
                                if (selectedMethod === 'wallet') {
                                    if (currentWalletBalance < quotedPrice) {
                                        Swal.showValidationMessage('Insufficient wallet balance. Please deposit funds first.');
                                        return false;
                                    }
                                    return {
                                        booking_id: bookingId,
                                        payment_method: 'wallet'
                                    };
                                } else {
                                    const phone = document.getElementById('pay-mobile-number').value.trim();
                                    if (!phone) {
                                        Swal.showValidationMessage('Please enter your M-Pesa mobile number');
                                        return false;
                                    }
                                    return {
                                        booking_id: bookingId,
                                        payment_method: 'mpesa',
                                        phone: phone
                                    };
                                }
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: 'Processing Payment...',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                const data = result.value;
                                const formData = new FormData();
                                formData.append('booking_id', data.booking_id);
                                formData.append('payment_method', data.payment_method);
                                if (data.phone) formData.append('phone', data.phone);
                                formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');

                                fetch('<?= APP_URL ?>/api/v1/payments/checkout-ajax', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(r => r.json())
                                .then(res => {
                                    if (res.success) {
                                        Swal.fire({
                                            title: 'Success / Imekamilika!',
                                            text: res.message,
                                            icon: 'success',
                                            confirmButtonColor: '#2563eb'
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    } else {
                                        Swal.fire('Error / Hitilafu', res.message, 'error');
                                    }
                                });
                            }
                        });
                    });
            }
            </script>
        </div>

        <?php
        // Check if review exists for this booking
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM reviews WHERE booking_id = :id");
        $stmt->execute([':id' => $booking['id']]);
        $review = $stmt->fetch();
        if ($review):
        ?>
        <div class="card p-6 mt-6">
            <h3 class="mb-4"><i class="fa-solid fa-star" style="color: var(--warning);"></i> Customer Review</h3>
            <div class="mb-2">
                <?= renderStars((float)$review['rating']) ?>
                <span class="text-xs text-muted ml-2"><?= formatDate($review['created_at']) ?></span>
            </div>
            <p class="text-sm italic">"<?= e($review['comment']) ?>"</p>
            <?php if (!empty($review['owner_reply'])): ?>
                <div class="owner-reply p-3 mt-3 bg-light rounded" style="border-left: 3px solid var(--primary);">
                    <p class="text-xs font-bold mb-1">Owner Reply / Jibu la Mmiliki:</p>
                    <p class="text-xs italic">"<?= e($review['owner_reply']) ?>"</p>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Right Column: Interactive Side Panel -->
        <div>
            <!-- Checkout CTA card shown only to customer when booking is accepted by owner -->
            <?php if (currentUserRole() === 'customer' && $booking['status'] === 'accepted'): ?>
                <?php if (!$isPaid): ?>
                    <div class="card p-6 text-center mb-4" style="background: var(--accent-light); border: 2px solid var(--accent);">
                        <h3><i class="fa-solid fa-credit-card"></i> Payment Required</h3>
                        <p class="text-sm mt-2">This booking has been accepted. Please pay to finalize your booking reservation.</p>
                        <button onclick="openPaymentModal(<?= (int)$booking['id'] ?>, '<?= e($booking['booking_ref']) ?>', <?= (float)$booking['quoted_price'] ?>, '<?= e($booking['customer_phone'] ?? '') ?>')" class="btn btn-accent btn-lg btn-block mt-4"><i class="fa-solid fa-wallet"></i> Pay Now</button>
                    </div>
                <?php else: ?>
                    <div class="card p-6 text-center mb-4" style="background: var(--success-bg); border: 2px solid var(--success); color: var(--success-dark);">
                        <h3><i class="fa-solid fa-circle-check"></i> Payment Completed</h3>
                        <p class="text-sm mt-2">Your payment has been successfully received. Lorry owner will start the trip soon.</p>
                    </div>
                <?php endif; ?>
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
                            <button type="submit" class="btn btn-danger btn-block"><i class="fa-solid fa-circle-xmark"></i> <?= currentLang() === 'sw' ? 'Ghairi Uhifadhi' : 'Cancel Booking' ?></button>
                        </form>
                    
                    <!-- Lorry Owner Options on Accepted Booking -->
                    <?php elseif (currentUserRole() === 'lorry_owner' && $booking['status'] === 'accepted'): ?>
                        <?php if ($isPaid): ?>
                            <!-- Start trip action form -->
                            <form method="POST" action="<?= APP_URL ?>/bookings/<?= (int)$booking['id'] ?>/start" class="w-full">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-truck-fast"></i> Start Trip / Anza Safari</button>
                            </form>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline btn-block" disabled style="opacity: 0.6; cursor: not-allowed;"><i class="fa-solid fa-lock"></i> Start Trip (Awaiting Payment)</button>
                            <p class="text-xs text-muted text-center mt-1">Customer has not completed payment yet. / Mteja hajakamilisha malipo.</p>
                        <?php endif; ?>
                    
                    <!-- Lorry Owner Options on In Transit Booking -->
                    <?php elseif (currentUserRole() === 'lorry_owner' && $booking['status'] === 'in_transit'): ?>
                        <!-- Mark as complete action form -->
                        <form method="POST" action="<?= APP_URL ?>/bookings/<?= (int)$booking['id'] ?>/complete" class="w-full">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-success btn-block"><i class="fa-solid fa-square-check"></i> Mark as Completed / Maliza Safari</button>
                        </form>
                    
                    <!-- Customer Options on Pending Booking -->
                    <?php elseif (currentUserRole() === 'customer' && $booking['status'] === 'pending'): ?>
                        <!-- Cancel booking action form with alert confirmation -->
                        <form method="POST" action="<?= APP_URL ?>/bookings/<?= (int)$booking['id'] ?>/cancel" class="w-full" onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-danger btn-block"><i class="fa-solid fa-circle-minus"></i> Cancel Booking</button>
                        </form>
                    <?php endif; ?>

                    <!-- Customer Options on Completed Booking: Write a Review -->
                    <?php if (currentUserRole() === 'customer' && $booking['status'] === 'completed'): ?>
                        <?php 
                        $db = getDB();
                        $stmt = $db->prepare("SELECT id FROM reviews WHERE booking_id = :id");
                        $stmt->execute([':id' => $booking['id']]);
                        $hasReview = $stmt->fetch() !== false;
                        ?>
                        <?php if (!$hasReview): ?>
                            <a href="<?= APP_URL ?>/bookings/<?= (int)$booking['id'] ?>/review" class="btn btn-accent btn-block"><i class="fa-solid fa-star"></i> Write a Review / Andika Maoni</a>
                        <?php else: ?>
                            <div class="alert alert-info text-xs p-3 text-center" style="margin: 0;"><i class="fa-solid fa-circle-check"></i> Review Submitted / Umeshatoa maoni</div>
                        <?php endif; ?>
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
