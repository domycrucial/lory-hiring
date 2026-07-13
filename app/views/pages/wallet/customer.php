<?php
$totalDeposited = 0;
$totalBookingPaid = 0;
foreach ($transactions as $t) {
    if (!empty($t['booking_ref'])) {
        $totalBookingPaid += (float)$t['amount'];
    } else {
        $totalDeposited += (float)$t['amount'];
    }
}
?>
<h1><i class="fa-solid fa-wallet"></i> <?= currentLang() === 'sw' ? 'Mkoba Wangu' : 'My Wallet' ?></h1>
<p class="text-muted mb-6"><?= currentLang() === 'sw' ? 'Hifadhi pesa kwenye mkoba wako kwa ajili ya malipo rahisi na ya haraka ya safari zote za malori.' : 'Store money securely to settle all transaction payments associated with booking lorries across Tanzania.' ?></p>

<div class="grid grid-3 mb-8">
    <!-- Available Balance Card -->
    <div class="card p-6" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border: none; box-shadow: var(--shadow-md);">
        <div class="text-xs uppercase tracking-wider opacity-75 mb-2"><?= currentLang() === 'sw' ? 'Salio Linalopatikana' : 'Available Balance' ?></div>
        <div class="text-3xl font-extrabold mb-4" id="live-wallet-balance"><?= formatTZS($user['wallet_balance'] ?? 0) ?></div>
        <div class="text-xs opacity-90"><i class="fa-solid fa-shield-halved"></i> <?= currentLang() === 'sw' ? 'Inatumika kulipia uhifadhi wa malori yote' : 'Ready for instant lorry booking checkout' ?></div>
    </div>
    
    <!-- Total Deposited Card -->
    <div class="card p-6">
        <div class="text-xs uppercase tracking-wider text-muted mb-2"><?= currentLang() === 'sw' ? 'Jumla ya Amana' : 'Total Deposited' ?></div>
        <div class="text-2xl font-bold text-success mb-4"><?= formatTZS($totalDeposited) ?></div>
        <div class="text-xs text-muted"><i class="fa-solid fa-mobile-screen-button"></i> <?= currentLang() === 'sw' ? 'Amana kupitia Tigo Pesa na Benki' : 'Deposits via Tigo Pesa & Bank simulation' ?></div>
    </div>

    <!-- Total Lorry Booking Payments Card -->
    <div class="card p-6">
        <div class="text-xs uppercase tracking-wider text-muted mb-2"><?= currentLang() === 'sw' ? 'Malipo ya Safari za Malori' : 'Lorry Booking Payments' ?></div>
        <div class="text-2xl font-bold text-primary mb-4"><?= formatTZS($totalBookingPaid) ?></div>
        <div class="text-xs text-muted"><i class="fa-solid fa-truck-fast"></i> <?= currentLang() === 'sw' ? 'Malipo ya uhifadhi wa malori' : 'Settled transport payments' ?></div>
    </div>
</div>

<div class="grid grid-2" style="gap: var(--space-6); align-items: start;">
    <!-- Left Column: Deposit Form (Tigo Pesa & Bank Simulation) -->
    <div class="card p-6">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <h3 style="margin-bottom: 0;"><i class="fa-solid fa-circle-plus text-primary"></i> <?= currentLang() === 'sw' ? 'Weka Pesa Kwenye Mkoba' : 'Deposit Money to Wallet' ?></h3>
            <span class="badge badge-info"><i class="fa-solid fa-lock"></i> Live Simulation</span>
        </div>

        <!-- Channel Selection Tabs -->
        <div style="display: flex; gap: var(--space-2); margin-bottom: var(--space-5); border-bottom: 2px solid var(--border-color); padding-bottom: var(--space-2);">
            <button type="button" id="tab-btn-tigopesa" onclick="switchDepositTab('tigopesa')" class="btn btn-sm" style="background: #0033a0; color: white; font-weight: 700; border: none; flex: 1;">
                <i class="fa-solid fa-mobile-screen-button"></i> Tigo Pesa
            </button>
            <button type="button" id="tab-btn-bank" onclick="switchDepositTab('bank')" class="btn btn-outline btn-sm" style="font-weight: 600; flex: 1;">
                <i class="fa-solid fa-building-columns"></i> Bank Transfer
            </button>
        </div>

        <!-- Tigo Pesa Mobile Money Form Panel -->
        <div id="panel-deposit-tigopesa">
            <form action="<?= APP_URL ?>/wallet/deposit" method="POST" id="form-deposit-tigopesa">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="deposit_channel" value="Tigo Pesa Mobile Money">
                <input type="hidden" name="deposit_reference" id="ref_tigopesa" value="">

                <div class="form-group mb-4">
                    <label class="form-label" style="font-weight: 600;">
                        <?= currentLang() === 'sw' ? 'Kiasi cha Kuweka (TZS)' : 'Deposit Amount (TZS)' ?>
                    </label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-2); margin-bottom: var(--space-3);">
                        <button type="button" onclick="setDepositAmount('tigopesa', 100000)" class="btn btn-outline btn-sm font-bold">100,000</button>
                        <button type="button" onclick="setDepositAmount('tigopesa', 350000)" class="btn btn-outline btn-sm font-bold">350,000</button>
                        <button type="button" onclick="setDepositAmount('tigopesa', 750000)" class="btn btn-outline btn-sm font-bold">750,000</button>
                        <button type="button" onclick="setDepositAmount('tigopesa', 1500000)" class="btn btn-outline btn-sm font-bold">1,500,000</button>
                        <button type="button" onclick="setDepositAmount('tigopesa', 3000000)" class="btn btn-outline btn-sm font-bold">3,000,000</button>
                        <button type="button" onclick="setDepositAmount('tigopesa', 5000000)" class="btn btn-outline btn-sm font-bold">5,000,000</button>
                    </div>
                    <input type="number" name="amount" id="amount_tigopesa" class="form-control" min="1000" step="1000" placeholder="e.g. 500000" required>
                </div>

                <div class="form-group mb-6">
                    <label class="form-label" for="phone_tigopesa" style="font-weight: 600;">
                        <?= currentLang() === 'sw' ? 'Namba ya Simu (Tigo Pesa)' : 'Tigo Pesa Mobile Number' ?>
                    </label>
                    <input type="text" id="phone_tigopesa" class="form-control" value="<?= e($user['phone'] ?? '0655123456') ?>" placeholder="065X XXX XXX" required>
                    <p class="text-xs text-muted mt-1">
                        <i class="fa-solid fa-circle-info"></i> <?= currentLang() === 'sw' ? 'Tigo Pesa itatuma ujumbe wa uthibitisho (USSD push).' : 'Simulates an automated Tigo Pesa USSD confirmation prompt.' ?>
                    </p>
                </div>

                <button type="button" onclick="simulateTigoPesaDeposit()" class="btn btn-primary w-full" style="background: #0033a0; border-color: #0033a0; font-weight: 700;">
                    <i class="fa-solid fa-mobile-screen-button"></i> <?= currentLang() === 'sw' ? 'Weka Pesa (Tigo Pesa)' : 'Deposit via Tigo Pesa Simulation' ?>
                </button>
            </form>
        </div>

        <!-- Bank Transfer Form Panel -->
        <div id="panel-deposit-bank" style="display: none;">
            <form action="<?= APP_URL ?>/wallet/deposit" method="POST" id="form-deposit-bank">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="deposit_channel" id="channel_bank" value="CRDB Bank Transfer">
                <input type="hidden" name="deposit_reference" id="ref_bank" value="">

                <div class="form-group mb-4">
                    <label class="form-label" style="font-weight: 600;">Select Tanzania Bank</label>
                    <select class="form-control" id="bank_selector" onchange="updateBankChannel()">
                        <option value="CRDB Bank Transfer">CRDB Bank (SimBanking / Branch)</option>
                        <option value="NMB Bank Transfer">NMB Bank (NMB Mkononi)</option>
                        <option value="NBC Bank Transfer">NBC Bank Tanzania</option>
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label" style="font-weight: 600;">Deposit Amount (TZS)</label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-2); margin-bottom: var(--space-3);">
                        <button type="button" onclick="setDepositAmount('bank', 500000)" class="btn btn-outline btn-sm font-bold">500,000</button>
                        <button type="button" onclick="setDepositAmount('bank', 1500000)" class="btn btn-outline btn-sm font-bold">1,500,000</button>
                        <button type="button" onclick="setDepositAmount('bank', 5000000)" class="btn btn-outline btn-sm font-bold">5,000,000</button>
                    </div>
                    <input type="number" name="amount" id="amount_bank" class="form-control" min="1000" step="1000" placeholder="e.g. 1500000" required>
                </div>

                <div class="form-group mb-6">
                    <label class="form-label" style="font-weight: 600;">Account or Slip Reference Number</label>
                    <input type="text" class="form-control" value="AC-<?= rand(100000, 999999) ?>" placeholder="Enter Bank Account / Ref">
                </div>

                <button type="button" onclick="simulateBankDeposit()" class="btn btn-primary w-full" style="font-weight: 700;">
                    <i class="fa-solid fa-building-columns"></i> <?= currentLang() === 'sw' ? 'Weka kupitia Benki' : 'Simulate Bank Transfer Deposit' ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Wallet Transaction History -->
    <div class="card p-6">
        <h3 class="mb-4"><i class="fa-solid fa-clock-rotate-left"></i> <?= currentLang() === 'sw' ? 'Historia ya Miamala' : 'Transaction History' ?></h3>
        <?php if (empty($transactions)): ?>
            <div class="text-center py-8 text-muted">
                <i class="fa-solid fa-receipt fa-2x mb-2" style="display: block; opacity: 0.5;"></i>
                <?= currentLang() === 'sw' ? 'Bado hakuna miamala iliyofanyika.' : 'No transactions recorded yet.' ?>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="table text-sm">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Detail / Lorry</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td>
                                <strong class="<?= !empty($t['booking_ref']) ? 'text-primary' : 'text-success' ?>">
                                    <?= e(!empty($t['booking_ref']) ? 'Booking Payment' : 'Deposit') ?>
                                </strong>
                            </td>
                            <td>
                                <?= !empty($t['lorry_name']) ? ('<i class="fa-solid fa-truck"></i> ' . e($t['lorry_name'])) : 'Account Wallet Top-Up' ?>
                            </td>
                            <td>
                                <strong><?= formatTZS($t['amount']) ?></strong>
                            </td>
                            <td><?= formatDate($t['payment_date'] ?? $t['created_at']) ?></td>
                            <td><span class="badge badge-success">Completed</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function switchDepositTab(tab) {
    const btnTigo = document.getElementById('tab-btn-tigopesa');
    const btnBank = document.getElementById('tab-btn-bank');
    const panelTigo = document.getElementById('panel-deposit-tigopesa');
    const panelBank = document.getElementById('panel-deposit-bank');

    if (tab === 'tigopesa') {
        btnTigo.style.background = '#0033a0';
        btnTigo.style.color = '#fff';
        btnBank.style.background = 'transparent';
        btnBank.style.color = 'var(--text-color)';
        panelTigo.style.display = 'block';
        panelBank.style.display = 'none';
    } else {
        btnBank.style.background = 'var(--primary)';
        btnBank.style.color = '#fff';
        btnTigo.style.background = 'transparent';
        btnTigo.style.color = 'var(--text-color)';
        panelBank.style.display = 'block';
        panelTigo.style.display = 'none';
    }
}

function setDepositAmount(tab, amount) {
    if (tab === 'tigopesa') {
        document.getElementById('amount_tigopesa').value = amount;
    } else {
        document.getElementById('amount_bank').value = amount;
    }
}

function updateBankChannel() {
    const selector = document.getElementById('bank_selector');
    document.getElementById('channel_bank').value = selector.value;
}

function simulateTigoPesaDeposit() {
    const amountInput = document.getElementById('amount_tigopesa');
    const phoneInput = document.getElementById('phone_tigopesa');
    const amount = parseFloat(amountInput.value);
    const phone = phoneInput.value.trim();

    if (!amount || amount < 1000) {
        Swal.fire('Validation Error', 'Please enter a valid deposit amount (minimum TZS 1,000).', 'warning');
        return;
    }
    if (!phone) {
        Swal.fire('Validation Error', 'Please enter your Tigo Pesa mobile phone number.', 'warning');
        return;
    }

    Swal.fire({
        title: 'Tigo Pesa USSD Simulation',
        html: `
            <div style="text-align: center; font-family: inherit;">
                <div style="background: #0033a0; color: white; padding: 12px; border-radius: 8px 8px 0 0; font-weight: 800; font-size: 1.1rem;">
                    <i class="fa-solid fa-mobile-screen-button"></i> TIGO PESA - LIPA KWA SIMU
                </div>
                <div style="padding: 20px; border: 2px solid #0033a0; border-top: none; border-radius: 0 0 8px 8px; background: #f8fafc;">
                    <p style="font-size: 0.95rem; margin-bottom: 12px;">USSD push payment prompt sent to <strong>${phone}</strong>.</p>
                    <div style="font-size: 1.5rem; font-weight: 900; color: #0033a0; margin-bottom: 16px;">
                        ${Number(amount).toLocaleString()} TZS
                    </div>
                    <label style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 6px;">Enter Simulated Tigo Pesa PIN:</label>
                    <input type="password" id="sim_tigo_pin" class="form-control" placeholder="****" maxlength="4" style="text-align: center; letter-spacing: 4px; font-size: 1.25rem; max-width: 180px; margin: 0 auto;">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#0033a0',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fa-solid fa-check"></i> Confirm Tigo Pesa PIN',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const pin = document.getElementById('sim_tigo_pin').value;
            if (!pin || pin.length < 4) {
                Swal.showValidationMessage('Please enter a 4-digit Tigo Pesa PIN');
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('ref_tigopesa').value = 'TIGO-' + Math.floor(100000 + Math.random() * 900000);
            document.getElementById('form-deposit-tigopesa').submit();
        }
    });
}

function simulateBankDeposit() {
    const amountInput = document.getElementById('amount_bank');
    const amount = parseFloat(amountInput.value);
    const bankChannel = document.getElementById('bank_selector').value;

    if (!amount || amount < 1000) {
        Swal.fire('Validation Error', 'Please enter a valid deposit amount (minimum TZS 1,000).', 'warning');
        return;
    }

    Swal.fire({
        title: 'Bank Transfer Simulation',
        html: `Simulating instant electronic wire from <strong>${bankChannel}</strong> of <strong>${Number(amount).toLocaleString()} TZS</strong> into your Wallet...`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Execute Bank Transfer',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('ref_bank').value = 'BANK-' + Math.floor(100000 + Math.random() * 900000);
            document.getElementById('form-deposit-bank').submit();
        }
    });
}
</script>
