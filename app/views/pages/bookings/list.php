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
                                
                                <!-- Actions shown to Customer on accepted and unpaid bookings -->
                                <?php if (currentUserRole() === 'customer' && $b['status'] === 'accepted' && empty($b['payment_status'])): ?>
                                    <!-- Pay Now action button -->
                                    <button onclick="openPaymentModal(<?= (int)$b['id'] ?>, '<?= e($b['booking_ref']) ?>', <?= (float)$b['quoted_price'] ?>, '<?= e($b['owner_phone'] ?? '') ?>', '<?= e(addslashes($b['lorry_name'] ?? 'Hired Lorry')) ?>')" class="btn btn-accent btn-sm"><i class="fa-solid fa-wallet"></i> Pay Now</button>
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

function openPaymentModal(bookingId, bookingRef, quotedPrice, customerPhone, lorryName = 'Hired Lorry') {
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
                   <div style="background: var(--primary-light); border: 1px solid var(--primary); padding: 12px 14px; border-radius: 8px; margin-bottom: 18px;">
                       <div style="font-size: 0.75rem; color: var(--primary); font-weight: 800; text-transform: uppercase;">Lorry Hired for Delivery</div>
                       <div style="font-size: 1.15rem; font-weight: 900; color: var(--text-color); margin: 4px 0;"><i class="fa-solid fa-truck text-primary"></i> ${lorryName}</div>
                       <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-top: 6px;">
                           <span>Ref: <strong>${bookingRef}</strong></span>
                           <span>Due: <strong class="text-primary">${Number(quotedPrice).toLocaleString()} TZS</strong></span>
                       </div>
                   </div>
                   
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
                           <div style="font-size: 0.85rem; color: var(--gray-500); margin-top: 4px;">Tigo Pesa / M-Pesa</div>
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
                               <a href="<?= APP_URL ?>/wallet" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Deposit via Tigo Pesa</a>
                           </div>
                           ` : ''}
                       </div>
                   </div>

                   <!-- Mobile Payment Panel -->
                   <div id="panel-mobile" style="display: none; background: var(--gray-50); border: 1px solid var(--border-color); padding: 16px; border-radius: 8px; text-align: center; margin-bottom: 20px;">
                       <p style="font-size: 0.85rem; margin-top: 0; margin-bottom: 12px;">Scan the QR code below or use Lipa Namba to complete the payment via Tigo Pesa / M-Pesa.</p>
                       <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent('LIPA KWA SIMU | Lorry Hired: ' + lorryName + ' | Ref: ' + bookingRef + ' | Amount: ' + quotedPrice + ' TZS | Merchant: OLHS System')}" alt="Lipa kwa Simu QR Code" style="border: 4px solid white; box-shadow: var(--shadow-sm); border-radius: 6px; margin-bottom: 12px; width: 180px; height: 180px;">
                       
                       <div style="background: white; border: 1px dashed var(--border-color); border-radius: 6px; padding: 10px; margin-bottom: 12px; text-align: left; font-size: 0.82rem;">
                           <div><strong>Hired Lorry:</strong> ${lorryName}</div>
                           <div><strong>Booking Ref:</strong> ${bookingRef}</div>
                           <div><strong>Payment Due:</strong> ${Number(quotedPrice).toLocaleString()} TZS</div>
                           <div><strong>Settlement Purpose:</strong> Lorry Transport & Cargo Booking</div>
                       </div>

                       <div style="font-weight: 800; font-size: 1.15rem; color: var(--primary);">LIPA NAMBA: 556677</div>
                       <div style="font-size: 0.8rem; color: var(--gray-600); margin-top: 4px;">Merchant Name: <strong>OLHS Lorry Hiring</strong></div>
                       
                       <div class="form-group" style="margin-top: 16px; text-align: left; margin-bottom: 0;">
                           <label style="font-weight: 600; font-size: 0.9rem; display: block; margin-bottom: 6px;">Your Mobile Number</label>
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
