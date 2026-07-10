<h1><i class="fa-solid fa-truck"></i> <?= currentLang() === 'sw' ? 'Marekebisho ya Ruhusa za Malori' : 'Manage Lorry Approvals' ?></h1>
<p class="text-muted mb-6"><?= currentLang() === 'sw' ? 'Hakiki orodha za malori yanayosubiri zilizowasilishwa na wamiliki.' : 'Review pending lorry listings submitted by owners.' ?></p>

<?php if (empty($pendingLorries)): ?>
    <div class="card p-6 text-center mt-4">
        <p class="text-muted"><i class="fa-solid fa-circle-check" style="color: var(--success); font-size: 2rem; display: block; margin-bottom: 12px;"></i> <?= currentLang() === 'sw' ? 'Hakuna malori yanayosubiri kuidhinishwa kwa sasa!' : 'All clear! No pending lorry approvals.' ?></p>
    </div>
<?php else: ?>
    <div class="grid grid-2 mt-4">
        <?php foreach ($pendingLorries as $l): ?>
        <div class="card p-4 flex flex-col gap-3">
            <div class="flex gap-4">
                <div style="width: 120px; height: 90px; background: var(--gray-100); border-radius: 8px; overflow: hidden; flex-shrink: 0;">
                    <?php if (!empty($l['primary_photo'])): ?>
                        <img src="<?= getLorryPhotoUrl($l['primary_photo']) ?>" alt="<?= e($l['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--gray-400);"><i class="fa-solid fa-image fa-2x"></i></div>
                    <?php endif; ?>
                </div>
                <div class="flex-grow">
                    <h3 class="mb-1"><?= e($l['name']) ?></h3>
                    <p class="text-xs text-muted mb-2"><strong><?= currentLang() === 'sw' ? 'Mmiliki' : 'Owner' ?>:</strong> <?= e($l['owner_name']) ?> (<?= e($l['owner_phone']) ?>)</p>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="badge badge-secondary"><?= e(ucfirst($l['lorry_type'])) ?></span>
                        <span class="badge badge-info"><?= e($l['capacity_tonnes']) ?> <?= currentLang() === 'sw' ? 'Tani' : 'Tonnes' ?></span>
                        <span class="badge badge-dark"><?= e($l['plate_number']) ?></span>
                    </div>
                </div>
            </div>
            
            <div class="text-sm border-top pt-2">
                <p><strong><?= currentLang() === 'sw' ? 'Gharama kwa KM' : 'Rate per KM' ?>:</strong> <?= formatTZS($l['price_per_km']) ?></p>
                <p><strong><?= currentLang() === 'sw' ? 'Mahali' : 'Location' ?>:</strong> <?= e($l['current_location']) ?></p>
                <p class="text-muted text-xs mt-1"><?= e($l['description']) ?></p>
            </div>

            <div class="flex gap-2 justify-end border-top pt-2 mt-auto">
                <form action="<?= APP_URL ?>/admin/lorries/<?= (int)$l['id'] ?>/approve" method="POST" class="confirm-approve-form" data-message="<?= currentLang() === 'sw' ? 'Je, una uhakika unataka kuidhinisha lori hili?' : 'Are you sure you want to approve this lorry?' ?>">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <button type="submit" class="btn btn-primary btn-sm"><?= currentLang() === 'sw' ? 'Idhinisha' : 'Approve' ?></button>
                </form>
                <button class="btn btn-outline btn-sm btn-danger-trigger" onclick="triggerRejectLorryModal(<?= (int)$l['id'] ?>)"><?= currentLang() === 'sw' ? 'Kataa' : 'Reject' ?></button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Reject Lorry Modal -->
<div id="rejectLorryModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
    <div class="card p-6" style="width: 450px; max-width: 90%;">
        <h3><?= currentLang() === 'sw' ? 'Kataa Ombi la Lori' : 'Reject Lorry Listing' ?></h3>
        <form id="rejectLorryForm" action="" method="POST" class="mt-4">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <div class="form-group mb-4">
                <label for="reject_lorry_reason" class="form-label"><?= currentLang() === 'sw' ? 'Sababu ya Kukataa' : 'Reason for Rejection' ?></label>
                <textarea id="reject_lorry_reason" name="reason" rows="3" class="form-control" placeholder="e.g. Invalid plate number, low quality photo" required></textarea>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" class="btn btn-outline" onclick="closeRejectLorryModal()"><?= currentLang() === 'sw' ? 'Futa' : 'Cancel' ?></button>
                <button type="submit" class="btn btn-danger"><?= currentLang() === 'sw' ? 'Thibitisha Kukataa' : 'Confirm Reject' ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function triggerRejectLorryModal(id) {
    const modal = document.getElementById('rejectLorryModal');
    const form = document.getElementById('rejectLorryForm');
    form.action = '<?= APP_URL ?>/admin/lorries/' + id + '/reject';
    modal.style.display = 'flex';
}
function closeRejectLorryModal() {
    document.getElementById('rejectLorryModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.confirm-approve-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-message');
            Swal.fire({
                title: '<?= currentLang() === "sw" ? "Thibitisha Kuidhinisha" : "Confirm Approval" ?>',
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
