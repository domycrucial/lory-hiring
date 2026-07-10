<div class="<?= !empty($inDashboard) ? '' : 'container' ?>" style="<?= !empty($inDashboard) ? '' : 'padding: var(--space-8) 0;' ?>">
    <div class="flex justify-between items-center mb-6" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6);">
        <h1><i class="fa-solid fa-truck"></i> My Lorries / Malori Yangu</h1>
        <button class="btn btn-primary" onclick="openAddLorryModal()"><i class="fa-solid fa-circle-plus"></i> Add Lorry / Ongeza Lori</button>
    </div>

    <?php if (empty($lorries)): ?>
        <div class="card p-6 text-center">
            <p style="font-size: 3rem; color: var(--gray-400);"><i class="fa-solid fa-truck"></i></p>
            <h3>No lorries listed yet / Bado hujaorodhesha gari</h3>
            <p class="text-muted">Add your first lorry to start receiving bookings!</p>
            <button class="btn btn-accent mt-4" onclick="openAddLorryModal()">+ Add Your First Lorry</button>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name / Jina</th><th>Type / Aina</th><th>Plate / Namba</th><th>Price/km / Rate</th><th>Status / Hali</th><th class="text-right">Actions / Vitendo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lorries as $l): ?>
                    <tr>
                        <td><strong><?= e($l['name'] ?? 'Lorry') ?></strong></td>
                        <td><?= e(LORRY_TYPES[$l['lorry_type']]['en'] ?? $l['lorry_type'] ?? '—') ?></td>
                        <td><code><?= e($l['plate_number'] ?? '—') ?></code></td>
                        <td><?= number_format((float)($l['price_per_km'] ?? 0)) ?> TZS</td>
                        <td><span class="badge status-<?= e($l['approval_status'] ?? 'pending') ?>"><?= e(ucfirst($l['approval_status'] ?? 'pending')) ?></span></td>
                        <td class="text-right">
                            <div class="flex gap-2 justify-end" style="display: flex; gap: var(--space-2); justify-content: flex-end;">
                                <button onclick="openEditLorryModal(<?= (int)$l['id'] ?>)" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                <button onclick="confirmDeleteLorry(<?= (int)$l['id'] ?>)" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Delete</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Add Lorry Modal -->
<div id="addLorryModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1050;">
    <div class="card p-6" style="width: 600px; max-width: 90%; max-height: 90vh; overflow-y: auto; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); animation: modalFadeIn 0.3s ease;">
        <div class="flex justify-between items-center mb-4" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: var(--space-4);">
            <h3 style="margin-bottom: 0;"><i class="fa-solid fa-circle-plus text-primary"></i> Add New Lorry / Ongeza Lori</h3>
            <button onclick="closeAddLorryModal()" style="background: none; border: none; font-size: 1.25rem; color: var(--gray-500); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="ajax-add-lorry-form" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="form-group mb-4">
                <label class="form-label" for="add_title" style="font-weight: 600;">Lorry Title / Jina la Lori</label>
                <input type="text" name="title" id="add_title" class="form-control" placeholder="e.g. Scania Heavy Duty" required>
            </div>
            <div class="grid grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                <div class="form-group mb-4">
                    <label class="form-label" for="add_type" style="font-weight: 600;">Lorry Type / Aina ya Lori</label>
                    <select name="lorry_type" id="add_type" class="form-control" required>
                        <option value="">— Select —</option>
                        <?php foreach (LORRY_TYPES as $key => $labels): ?>
                            <option value="<?= e($key) ?>"><?= e($labels['en']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label" for="add_plate" style="font-weight: 600;">Plate Number / Namba ya Bamba</label>
                    <input type="text" name="plate_number" id="add_plate" class="form-control" placeholder="T 123 ABC" required>
                </div>
            </div>
            <div class="grid grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                <div class="form-group mb-4">
                    <label class="form-label" for="add_cap" style="font-weight: 600;">Capacity (Tons) / Uwezo (Tani)</label>
                    <input type="number" name="capacity_tons" id="add_cap" class="form-control" step="0.5" placeholder="e.g. 10" required>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label" for="add_price" style="font-weight: 600;">Price per KM (TZS) / Rate</label>
                    <input type="number" name="price_per_km" id="add_price" class="form-control" placeholder="e.g. 2000" required>
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="form-label" for="add_loc" style="font-weight: 600;">Base Location / Mahali Lipo</label>
                <input type="text" name="location" id="add_loc" class="form-control" placeholder="e.g. Dar es Salaam" required>
            </div>
            <div class="form-group mb-4">
                <label class="form-label" for="add_desc" style="font-weight: 600;">Description / Maelezo</label>
                <textarea name="description" id="add_desc" class="form-control" placeholder="Describe condition, driver info..."></textarea>
            </div>
            <div class="form-group mb-6">
                <label class="form-label" for="add_photo" style="font-weight: 600;">Photo / Picha</label>
                <input type="file" name="photo" id="add_photo" class="form-control" accept="image/*">
            </div>
            <div class="flex gap-2 justify-end" style="display: flex; gap: var(--space-2); justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeAddLorryModal()">Cancel / Futa</button>
                <button type="submit" class="btn btn-primary">Submit / Hifadhi</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Lorry Modal -->
<div id="editLorryModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1050;">
    <div class="card p-6" style="width: 600px; max-width: 90%; max-height: 90vh; overflow-y: auto; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); animation: modalFadeIn 0.3s ease;">
        <div class="flex justify-between items-center mb-4" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: var(--space-4);">
            <h3 style="margin-bottom: 0;"><i class="fa-solid fa-pen-to-square text-primary"></i> Edit Lorry Details / Hariri Lori</h3>
            <button onclick="closeEditLorryModal()" style="background: none; border: none; font-size: 1.25rem; color: var(--gray-500); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="ajax-edit-lorry-form">
            <?= csrfField() ?>
            <input type="hidden" name="lorry_id" id="edit_lorry_id">
            <div class="form-group mb-4">
                <label class="form-label" for="edit_title" style="font-weight: 600;">Lorry Name / Jina la Lori</label>
                <input type="text" name="title" id="edit_title" class="form-control" required>
            </div>
            <div class="grid grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                <div class="form-group mb-4">
                    <label class="form-label" for="edit_type" style="font-weight: 600;">Lorry Type / Aina ya Lori</label>
                    <select name="lorry_type" id="edit_type" class="form-control" required>
                        <?php foreach (LORRY_TYPES as $key => $labels): ?>
                            <option value="<?= e($key) ?>"><?= e($labels['en']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label" style="font-weight: 600;">Plate Number / Namba ya Bamba</label>
                    <input type="text" id="edit_plate" class="form-control" disabled style="background: var(--gray-100); cursor: not-allowed;">
                </div>
            </div>
            <div class="grid grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                <div class="form-group mb-4">
                    <label class="form-label" for="edit_cap" style="font-weight: 600;">Capacity (Tons) / Uwezo (Tani)</label>
                    <input type="number" name="capacity_tons" id="edit_cap" class="form-control" step="0.5" required>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label" for="edit_price" style="font-weight: 600;">Price per KM (TZS) / Rate</label>
                    <input type="number" name="price_per_km" id="edit_price" class="form-control" required>
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="form-label" for="edit_loc" style="font-weight: 600;">Base Location / Mahali Lipo</label>
                <input type="text" name="location" id="edit_loc" class="form-control" required>
            </div>
            <div class="form-group mb-6">
                <label class="form-label" for="edit_desc" style="font-weight: 600;">Description / Maelezo</label>
                <textarea name="description" id="edit_desc" class="form-control"></textarea>
            </div>
            <div class="flex gap-2 justify-end" style="display: flex; gap: var(--space-2); justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeEditLorryModal()">Cancel / Futa</button>
                <button type="submit" class="btn btn-primary">Save Changes / Hifadhi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddLorryModal() {
    document.getElementById('addLorryModal').style.display = 'flex';
}
function closeAddLorryModal() {
    document.getElementById('addLorryModal').style.display = 'none';
}

function openEditLorryModal(id) {
    // Fetch lorry details first via API
    fetch('<?= APP_URL ?>/api/v1/lorries/' + id)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const data = res.data;
                document.getElementById('edit_lorry_id').value = data.id;
                document.getElementById('edit_title').value = data.name;
                document.getElementById('edit_type').value = data.lorry_type;
                document.getElementById('edit_plate').value = data.plate_number;
                document.getElementById('edit_cap').value = data.capacity_tonnes;
                document.getElementById('edit_price').value = data.price_per_km;
                document.getElementById('edit_loc').value = data.current_location;
                document.getElementById('edit_desc').value = data.description;

                document.getElementById('editLorryModal').style.display = 'flex';
            } else {
                Swal.fire('Error', 'Failed to retrieve lorry details.', 'error');
            }
        });
}

function closeEditLorryModal() {
    document.getElementById('editLorryModal').style.display = 'none';
}

// Handle AJAX Add Lorry form submission
document.getElementById('ajax-add-lorry-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?= APP_URL ?>/lorries/add', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(res => {
        if (res.success) {
            closeAddLorryModal();
            Swal.fire({
                title: 'Success! / Imefanikiwa!',
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
});

// Handle AJAX Edit Lorry form submission
document.getElementById('ajax-edit-lorry-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('edit_lorry_id').value;
    const formData = new FormData(this);
    
    fetch('<?= APP_URL ?>/lorries/' + id + '/edit', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(res => {
        if (res.success) {
            closeEditLorryModal();
            Swal.fire({
                title: 'Edited successfully! / Kimehaririwa!',
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
});

// Confirm deletion using SweetAlert2
function confirmDeleteLorry(id) {
    Swal.fire({
        title: '<?= currentLang() === "sw" ? "Je, una uhakika wa kufuta?" : "Are you sure you want to delete?" ?>',
        text: '<?= currentLang() === "sw" ? "Hutaweza kurudisha taarifa za lori hili!" : "You will not be able to recover this lorry profile!" ?>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<?= currentLang() === "sw" ? "Ndiyo, Futa!" : "Yes, Delete!" ?>',
        cancelButtonText: '<?= currentLang() === "sw" ? "Hapana" : "Cancel" ?>'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
            
            fetch('<?= APP_URL ?>/lorries/' + id + '/delete', {
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
                        title: 'Deleted! / Imefutwa!',
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

<style>
@keyframes modalFadeIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>
