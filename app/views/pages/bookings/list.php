<!-- Bookings List Content Container -->
<div class="<?= !empty($inDashboard) ? '' : 'container' ?>" style="<?= !empty($inDashboard) ? '' : 'padding: var(--space-8) 0;' ?>">
    <!-- Main page heading showing the dashboard title -->
    <h1><i class="fa-solid fa-list-check"></i> <?= e($pageTitle) ?></h1>

    <!-- Renders empty layout state if no bookings exist -->
    <?php if (empty($bookings)): ?>
        <div class="card p-8 text-center">
            <!-- Info icon -->
            <p style="font-size: 4rem; color: var(--gray-400);"><i class="fa-solid fa-calendar-xmark"></i></p>
            <h3 class="mt-4">No bookings found / Hakuna uhifadhi uliopatikana</h3>
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
                        <th><?= currentLang() === 'sw' ? 'Kumbukumbu' : 'Ref' ?></th>
                        <th><?= currentLang() === 'sw' ? 'Lori' : 'Lorry' ?></th>
                        <th><?= currentLang() === 'sw' ? 'Aina ya Mzigo' : 'Product/Goods' ?></th>
                        <th><?= currentLang() === 'sw' ? 'Kutoka' : 'Pickup' ?></th>
                        <th><?= currentLang() === 'sw' ? 'Kwenda' : 'Delivery' ?></th>
                        <th><?= currentLang() === 'sw' ? 'Gharama' : 'Price' ?></th>
                        <th><?= currentLang() === 'sw' ? 'Hali' : 'Status' ?></th>
                        <th><?= currentLang() === 'sw' ? 'Tarehe' : 'Date' ?></th>
                        <th class="text-right"><?= currentLang() === 'sw' ? 'Vitendo' : 'Actions' ?></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Iterate bookings records list -->
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <!-- Reference number -->
                        <td><strong><?= e($b['booking_ref'] ?? '#' . $b['id']) ?></strong></td>
                        <!-- Lorry Name -->
                        <td><?= e($b['lorry_name'] ?? '—') ?></td>
                        <!-- Goods description -->
                        <td><small><?= e($b['goods_description'] ?? 'General Cargo') ?></small></td>
                        <!-- Pickup location -->
                        <td><?= e($b['pickup_address'] ?? '—') ?></td>
                        <!-- Drop location -->
                        <td><?= e($b['delivery_address'] ?? '—') ?></td>
                        <!-- Quoted rate price -->
                        <td><strong><?= number_format((float)($b['quoted_price'] ?? $b['total_price'] ?? 0)) ?> TZS</strong></td>
                        <!-- Status badge -->
                        <td><span class="badge <?= bookingStatusClass($b['status'] ?? 'pending') ?>"><?= e(bookingStatusLabel($b['status'] ?? 'pending', currentLang())) ?></span></td>
                        <!-- Trip date -->
                        <td><?= formatDate($b['preferred_date'] ?? $b['pickup_date'] ?? null) ?></td>
                        <!-- Row actions -->
                        <td class="text-right">
                            <div class="flex gap-2 justify-end" style="display: flex; gap: var(--space-2); justify-content: flex-end;">
                                <!-- Base detail page view button -->
                                <button onclick="viewBookingDetails(<?= (int)$b['id'] ?>)" class="btn btn-outline btn-sm"><i class="fa-solid fa-eye"></i> View</button>
                                
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
                                        <button class="btn btn-danger btn-sm"><i class="fa-solid fa-xmark"></i> <?= currentLang() === 'sw' ? 'Ghairi' : 'Cancel' ?></button>
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
                                    <!-- Cancel booking action button -->
                                    <button onclick="confirmCancelBooking(<?= (int)$b['id'] ?>)" class="btn btn-danger btn-sm"><i class="fa-solid fa-circle-minus"></i> Cancel</button>
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

<script>
function viewBookingDetails(id) {
    fetch('<?= APP_URL ?>/api/v1/bookings/' + id)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const b = res.data;
                const statusColors = {
                    'pending': '#f59e0b',
                    'accepted': '#3b82f6',
                    'completed': '#10b981',
                    'cancelled': '#ef4444',
                    'declined': '#6b7280'
                };
                
                const detailsHtml = `
                    <div style="text-align: left; font-family: sans-serif; line-height: 1.6; color: var(--gray-800);">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--border-color); padding-bottom: 12px; margin-bottom: 16px;">
                            <h2 style="margin: 0; font-size: 1.35rem; color: var(--primary);">Booking ${b.booking_ref || '#' + b.id}</h2>
                            <span style="background: ${statusColors[b.status] || '#ccc'}; color: #fff; padding: 4px 10px; border-radius: 9999px; font-size: 0.85rem; font-weight: bold; text-transform: uppercase;">
                                ${b.status}
                            </span>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Pickup Base</strong>
                                <p style="margin: 4px 0 0 0; font-weight: 600;">${b.pickup_address}</p>
                            </div>
                            <div>
                                <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Delivery Address</strong>
                                <p style="margin: 4px 0 0 0; font-weight: 600;">${b.delivery_address}</p>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; border-top: 1px solid var(--border-color); padding-top: 12px;">
                            <div>
                                <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Distance</strong>
                                <p style="margin: 4px 0 0 0; font-weight: 600;">${Number(b.distance_km).toFixed(1)} km</p>
                            </div>
                            <div>
                                <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Preferred Date</strong>
                                <p style="margin: 4px 0 0 0; font-weight: 600;">${b.preferred_date || '—'}</p>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; border-top: 1px solid var(--border-color); padding-top: 12px;">
                            <div>
                                <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Goods Details</strong>
                                <p style="margin: 4px 0 0 0; font-weight: 600;">${b.goods_description || 'General Cargo'}</p>
                            </div>
                            <div>
                                <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Total Quote Price</strong>
                                <p style="margin: 4px 0 0 0; font-weight: 700; color: var(--primary); font-size: 1.1rem;">${Number(b.quoted_price || b.total_price).toLocaleString()} TZS</p>
                            </div>
                        </div>
                        
                        <div style="background: var(--gray-50); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); margin-top: 16px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 0.9rem; color: var(--primary);"><i class="fa-solid fa-truck"></i> Vehicle Details</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 0.85rem; color: var(--gray-700);">
                                <div><strong>Lorry:</strong> ${b.name || b.lorry_name || 'Vehicle Listing'}</div>
                                <div><strong>Plate Number:</strong> <code>${b.plate_number || '—'}</code></div>
                                <div><strong>Capacity:</strong> ${b.capacity_tonnes || '—'} Tons</div>
                                <div><strong>Price/km:</strong> ${Number(b.price_per_km).toLocaleString()} TZS</div>
                            </div>
                        </div>
                    </div>
                `;
                
                Swal.fire({
                    html: detailsHtml,
                    showCloseButton: true,
                    showConfirmButton: false,
                    width: '600px',
                    padding: '24px',
                    background: 'var(--card-bg)'
                });
            } else {
                Swal.fire('Error', 'Failed to retrieve booking information.', 'error');
            }
        });
}

function confirmCancelBooking(id) {
    Swal.fire({
        title: '<?= currentLang() === "sw" ? "Je, una uhakika wa kufuta uhifadhi?" : "Are you sure you want to cancel?" ?>',
        text: '<?= currentLang() === "sw" ? "Uhifadhi huu utafutwa kabisa!" : "This booking will be cancelled!" ?>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<?= currentLang() === "sw" ? "Ndiyo, Futa!" : "Yes, Cancel!" ?>',
        cancelButtonText: '<?= currentLang() === "sw" ? "Hapana" : "Keep Booking" ?>'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
            
            fetch('<?= APP_URL ?>/bookings/' + id + '/cancel', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    Swal.fire({
                        title: 'Cancelled! / Imefutwa!',
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
}
</script>
