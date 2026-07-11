<h1><i class="fa-solid fa-user-shield"></i> <?= currentLang() === 'sw' ? 'Dawati la Usimamizi' : 'Admin Dashboard' ?></h1>
<p class="text-muted mb-6"><?= currentLang() === 'sw' ? 'Karibu kwenye jopo la usimamizi wa mfumo.' : 'Welcome to the administration panel.' ?></p>

<!-- Stats Grid -->
<div class="grid grid-4 mb-8">
    <div class="stat-card">
        <div class="stat-value"><?= (int)($userStats['total'] ?? 0) ?></div>
        <div class="stat-label"><?= currentLang() === 'sw' ? 'Jumla ya Watumiaji' : 'Total Users' ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int)($lorryStats['active'] ?? 0) ?></div>
        <div class="stat-label"><?= currentLang() === 'sw' ? 'Malori Yanayofanya Kazi' : 'Active Lorries' ?></div>
    </div>
    <div class="stat-card" style="border: 1px solid var(--warning-light);">
        <div class="stat-value" style="color: var(--warning);"><?= (int)($lorryStats['pending'] ?? 0) ?></div>
        <div class="stat-label"><a href="<?= APP_URL ?>/admin/lorries" style="color: var(--warning);"><?= currentLang() === 'sw' ? 'Uidhinishaji Unaosubiri →' : 'Pending Approvals →' ?></a></div>
    </div>
    <div class="stat-card" style="border: 1px solid var(--primary-light); background: var(--primary-bg);">
        <div class="stat-value" style="color: var(--primary); font-size: 1.35rem; font-weight: 800; padding: 4px 0;">
            <?= formatTZS($bookingStats['revenue_month'] ?? 0) ?>
        </div>
        <div class="stat-label"><?= currentLang() === 'sw' ? 'Kamisheni ya Jukwaa (Mwezi Huu)' : 'Platform Commission (This Month)' ?></div>
    </div>
</div>

<!-- Analytics Section -->
<div class="grid grid-2 mb-8" style="margin-bottom: var(--space-8);">
    <!-- Bookings by Status (Pie Chart) -->
    <div class="card p-6">
        <h3><i class="fa-solid fa-chart-pie text-primary"></i> <?= currentLang() === 'sw' ? 'Uhifadhi kwa Hali (Muda Halisi)' : 'Bookings by Status (Real-time)' ?></h3>
        <div style="height: 250px; position: relative;" class="mt-4">
            <canvas id="bookingsPieChart"></canvas>
        </div>
    </div>
    <!-- Bookings Trend (Line Chart) -->
    <div class="card p-6">
        <h3><i class="fa-solid fa-chart-line text-accent"></i> <?= currentLang() === 'sw' ? 'Jumla ya Uhifadhi kwa Siku (Siku 7 Zilizopita)' : 'Total Bookings per Day (Last 7 Days)' ?></h3>
        <div style="height: 250px; position: relative;" class="mt-4">
            <canvas id="bookingsLineChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pie Chart for Bookings Status
    const statusCtx = document.getElementById('bookingsPieChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_map(fn($item) => ucfirst(bookingStatusLabel($item['label'], currentLang())), $bookingsStatus)) ?>,
            datasets: [{
                data: <?= json_encode(array_map(fn($item) => (int)$item['count'], $bookingsStatus)) ?>,
                backgroundColor: [
                    '#f59e0b', // pending - Amber
                    '#2563eb', // approved - Blue
                    '#10b981', // completed - Green
                    '#ef4444', // cancelled - Red
                    '#6b7280'  // others/declined - Gray
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Line Chart for Bookings per Day
    const dayCtx = document.getElementById('bookingsLineChart').getContext('2d');
    new Chart(dayCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_map(fn($item) => $item['label'], $bookingsPerDay)) ?>,
            datasets: [{
                label: '<?= currentLang() === 'sw' ? 'Jumla ya Uhifadhi' : 'Total Bookings' ?>',
                data: <?= json_encode(array_map(fn($item) => (int)$item['count'], $bookingsPerDay)) ?>,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
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
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>

<!-- Live Delivery Tracking Map Section -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="card p-6 mb-8" style="margin-bottom: var(--space-8);">
    <h3><i class="fa-solid fa-map-location-dot text-success"></i> <?= currentLang() === 'sw' ? 'Ramani ya Safari Zinazoendelea (Muda Halisi)' : 'Live Delivery Tracking Map (Real-time)' ?></h3>
    <p class="text-muted text-sm"><?= currentLang() === 'sw' ? 'Ufuatiliaji wa muda halisi wa malori yote yanayosafirisha mizigo nchini Tanzania.' : 'Real-time trace of all active cargo shipments across Tanzania.' ?></p>
    <div id="adminTrackingMap" style="height: 400px; border-radius: var(--radius-lg); margin-top: var(--space-4); border: 1px solid var(--border-color); z-index: 1;"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tanzania Location Database for instant matching
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

    // Initialize Map centered on Tanzania
    const map = L.map('adminTrackingMap').setView([-6.3690, 34.8888], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Active Bookings Data from PHP
    const activeBookings = <?= json_encode($activeBookings) ?>;

    if (activeBookings.length === 0) {
        // Show default marker if no active trips are running
        L.marker([-6.7924, 39.2083]).addTo(map)
            .bindPopup("<b>Dar es Salaam Hub</b><br>No active shipments to track right now.")
            .openPopup();
    } else {
        activeBookings.forEach(function(b) {
            const start = getCoords(b.pickup_address);
            const end = getCoords(b.delivery_address);

            // Draw route line
            const routeLine = L.polyline([start, end], {
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
            }).addTo(map).bindPopup(`<b>Pickup / Mwanzo</b><br>${b.pickup_address}`);

            // End Marker
            L.circleMarker(end, {
                radius: 8,
                fillColor: '#ef4444',
                color: '#fff',
                weight: 2,
                fillOpacity: 1
            }).addTo(map).bindPopup(`<b>Destination / Mwisho</b><br>${b.delivery_address}`);

            // Simulated Live Lorry Marker along the route
            let progress = b.status === 'in_transit' ? 0.45 : 0.05;
            const currentLat = start[0] + (end[0] - start[0]) * progress;
            const currentLng = start[1] + (end[1] - start[1]) * progress;

            // Custom Lorry Icon with real lorry photo circular layout
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
                    <strong>Owner:</strong> ${b.owner_name} (<a href="tel:${b.owner_phone}">${b.owner_phone}</a>)<br>
                    <strong>Distance:</strong> ${b.distance_km} km<br>
                    <strong>Est. Duration:</strong> ${estimateDurationJS(b.distance_km, '<?= currentLang() ?>')}<br>
                    <strong>Status:</strong> <span style="text-transform: capitalize; color: ${borderColor}; font-weight: bold;">${b.status}</span><br>
                    <strong>Goods:</strong> ${b.goods_description || 'General Cargo'}
                </div>
            `;
            lorryMarker.bindPopup(popupHtml);

            // Animate lorry icon movement if trip is in transit
            if (b.status === 'in_transit') {
                let direction = 1;
                setInterval(() => {
                    progress += 0.005 * direction;
                    if (progress >= 0.9) { direction = -1; }
                    if (progress <= 0.1) { direction = 1; }
                    const nextLat = start[0] + (end[0] - start[0]) * progress;
                    const nextLng = start[1] + (end[1] - start[1]) * progress;
                    lorryMarker.setLatLng([nextLat, nextLng]);
                }, 1500);
            }
        });
    }
});
</script>

<!-- Pending Withdrawals -->
<h2><i class="fa-solid fa-money-bill-transfer"></i> <?= currentLang() === 'sw' ? 'Maombi ya Kutoa Pesa Yanayosubiri' : 'Pending Withdrawal Requests' ?></h2>
<?php if (empty($pendingWithdrawals)): ?>
    <div class="card p-6 text-center mt-4">
        <p class="text-muted"><i class="fa-solid fa-inbox"></i> <?= currentLang() === 'sw' ? 'Hakuna maombi ya kutoa pesa yanayosubiri.' : 'No pending withdrawal requests.' ?></p>
    </div>
<?php else: ?>
    <div class="table-wrapper mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th><?= currentLang() === 'sw' ? 'Mmiliki' : 'Owner' ?></th>
                    <th><?= currentLang() === 'sw' ? 'Email / Simu' : 'Email / Phone' ?></th>
                    <th><?= currentLang() === 'sw' ? 'Kiasi Kilichoombwa' : 'Amount Requested' ?></th>
                    <th><?= currentLang() === 'sw' ? 'Namba ya Muamala' : 'Mobile Wallet No.' ?></th>
                    <th><?= currentLang() === 'sw' ? 'Tarehe ya Ombi' : 'Requested At' ?></th>
                    <th class="text-right"><?= currentLang() === 'sw' ? 'Hatua' : 'Actions' ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingWithdrawals as $w): ?>
                <tr>
                    <td><strong><?= e($w['owner_name']) ?></strong></td>
                    <td><?= e($w['owner_email']) ?> / <?= e($w['owner_phone']) ?></td>
                    <td><strong class="text-success"><?= formatTZS($w['amount']) ?></strong></td>
                    <td><code><?= e($w['mobile_number']) ?></code></td>
                    <td><?= formatDateTime($w['created_at']) ?></td>
                    <td class="text-right">
                        <div class="flex gap-2 justify-end">
                            <form action="<?= APP_URL ?>/admin/withdrawals/<?= (int)$w['id'] ?>/approve" method="POST" class="confirm-withdrawal-form" data-message="<?= currentLang() === 'sw' ? 'Je, una uhakika unataka kuidhinisha kutoa pesa kwa ' . e($w['owner_name']) . '?' : 'Are you sure you want to approve this withdrawal for ' . e($w['owner_name']) . '?' ?>">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <button type="submit" class="btn btn-primary btn-sm"><?= currentLang() === 'sw' ? 'Idhinisha' : 'Approve' ?></button>
                            </form>
                            <button class="btn btn-outline btn-sm btn-danger-trigger" onclick="triggerRejectModal(<?= (int)$w['id'] ?>)"><?= currentLang() === 'sw' ? 'Kataa' : 'Reject' ?></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Reject Withdrawal Modal -->
<div id="rejectModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
    <div class="card p-6" style="width: 450px; max-width: 90%;">
        <h3><?= currentLang() === 'sw' ? 'Kataa Ombi la Kutoa Pesa' : 'Reject Withdrawal Request' ?></h3>
        <form id="rejectForm" action="" method="POST" class="mt-4">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <div class="form-group mb-4">
                <label for="reject_reason" class="form-label"><?= currentLang() === 'sw' ? 'Sababu ya Kukataa' : 'Reason for Rejection' ?></label>
                <textarea id="reject_reason" name="reason" rows="3" class="form-control" placeholder="e.g. Invalid mobile number format" required></textarea>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" class="btn btn-outline" onclick="closeRejectModal()"><?= currentLang() === 'sw' ? 'Futa' : 'Cancel' ?></button>
                <button type="submit" class="btn btn-danger"><?= currentLang() === 'sw' ? 'Thibitisha Kukataa' : 'Confirm Reject' ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function triggerRejectModal(id) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = '<?= APP_URL ?>/admin/withdrawals/' + id + '/reject';
    modal.style.display = 'flex';
}
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.confirm-withdrawal-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-message');
            Swal.fire({
                title: '<?= currentLang() === "sw" ? "Thibitisha Kutoa Pesa" : "Confirm Withdrawal" ?>',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#ef4444',
                confirmButtonText: '<?= currentLang() === "sw" ? "Ndiyo, Idhinisha" : "Yes, Approve" ?>',
                cancelButtonText: '<?= currentLang() === "sw" ? "Hapana" : "Cancel" ?>'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
