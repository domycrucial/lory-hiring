<!-- Edit Lorry Listing Container -->
<div class="<?= !empty($inDashboard) ? '' : 'container' ?>" style="<?= !empty($inDashboard) ? '' : 'padding: var(--space-8) 0;' ?>">
    <!-- Main page heading -->
    <h1><i class="fa-solid fa-pen-to-square"></i> Edit Lorry</h1>

    <!-- Form container card -->
    <div class="card p-6" style="max-width: 700px; margin-top: var(--space-6);">
        <form method="POST" action="<?= APP_URL ?>/lorries/<?= (int)$lorry['id'] ?>/edit" id="edit-lorry-form">
            <?= csrfField() ?>

            <!-- Input for Lorry Title/Nickname -->
            <div class="form-group">
                <label class="form-label" for="ed_title"><i class="fa-solid fa-signature" style="color: var(--primary);"></i> Lorry Name</label>
                <input type="text" name="title" id="ed_title" class="form-control" value="<?= e($lorry['name'] ?? '') ?>" required>
            </div>

            <!-- Two-column grid layout for Lorry Type and Plate Number -->
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="ed_type"><i class="fa-solid fa-truck-moving" style="color: var(--primary);"></i> Lorry Type</label>
                    <select name="lorry_type" id="ed_type" class="form-control" required>
                        <?php foreach (LORRY_TYPES as $key => $labels): ?>
                            <!-- Loop over options and auto-select current type (English labels only) -->
                            <option value="<?= e($key) ?>" <?= (($lorry['lorry_type'] ?? '') === $key) ? 'selected' : '' ?>><?= e($labels['en']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Disabled field for plate number to prevent modification -->
                <div class="form-group">
                    <label class="form-label"><i class="fa-solid fa-id-card" style="color: var(--primary);"></i> Registration Plate Number</label>
                    <input type="text" class="form-control" value="<?= e($lorry['plate_number'] ?? '') ?>" disabled style="background-color: var(--gray-100); cursor: not-allowed;">
                </div>
            </div>

            <!-- Two-column grid layout for Load Capacity and Distance Rate -->
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="ed_cap"><i class="fa-solid fa-weight-hanging" style="color: var(--primary);"></i> Capacity (Tons)</label>
                    <input type="number" name="capacity_tons" id="ed_cap" class="form-control" step="0.5" value="<?= e($lorry['capacity_tonnes'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="ed_price"><i class="fa-solid fa-tags" style="color: var(--primary);"></i> Price per Km (TZS)</label>
                    <input type="number" name="price_per_km" id="ed_price" class="form-control" value="<?= e($lorry['price_per_km'] ?? '') ?>" required>
                </div>
            </div>

            <!-- Input for base location -->
            <div class="form-group">
                <label class="form-label" for="ed_loc"><i class="fa-solid fa-location-dot" style="color: var(--primary);"></i> Base Location</label>
                <input type="text" name="location" id="ed_loc" class="form-control" value="<?= e($lorry['current_location'] ?? '') ?>" required>
            </div>

            <!-- Text area description input -->
            <div class="form-group">
                <label class="form-label" for="ed_desc"><i class="fa-solid fa-file-waveform" style="color: var(--primary);"></i> Lorry Description</label>
                <textarea name="description" id="ed_desc" class="form-control"><?= e($lorry['description'] ?? '') ?></textarea>
            </div>

            <!-- Form action buttons -->
            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-circle-check"></i> Save Changes</button>
                <a href="<?= APP_URL ?>/lorries/mine" class="btn btn-outline btn-lg"><i class="fa-solid fa-xmark"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>
