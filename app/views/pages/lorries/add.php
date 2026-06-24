<!-- Add Lorry Listing Page Container -->
<div class="<?= !empty($inDashboard) ? '' : 'container' ?>" style="<?= !empty($inDashboard) ? '' : 'padding: var(--space-8) 0;' ?>">
    <!-- Main page heading -->
    <h1><i class="fa-solid fa-plus"></i> Add New Lorry</h1>

    <!-- Form container card -->
    <div class="card p-6" style="max-width: 700px; margin-top: var(--space-6);">
        <form method="POST" action="<?= APP_URL ?>/lorries/add" enctype="multipart/form-data" id="add-lorry-form">
            <?= csrfField() ?>

            <!-- Input for Lorry Nickname/Title -->
            <div class="form-group">
                <label class="form-label" for="title"><i class="fa-solid fa-signature" style="color: var(--primary);"></i> Lorry Title</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Isuzu FRR 2020 Flatbed" required>
            </div>

            <!-- Two-column grid layout for Type selection and Plate Number -->
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="lorry_type"><i class="fa-solid fa-truck-moving" style="color: var(--primary);"></i> Lorry Type</label>
                    <select name="lorry_type" id="lorry_type" class="form-control" required>
                        <option value="">— Select —</option>
                        <?php foreach (LORRY_TYPES as $key => $labels): ?>
                            <!-- Loop over supported truck presets (English labels only) -->
                            <option value="<?= e($key) ?>"><?= e($labels['en']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="plate_number"><i class="fa-solid fa-id-card" style="color: var(--primary);"></i> Registration Plate Number</label>
                    <input type="text" name="plate_number" id="plate_number" class="form-control" placeholder="T 123 ABC" required>
                </div>
            </div>

            <!-- Two-column grid layout for Load Capacity and Distance Rate -->
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="capacity_tons"><i class="fa-solid fa-weight-hanging" style="color: var(--primary);"></i> Load Capacity (Tons)</label>
                    <input type="number" name="capacity_tons" id="capacity_tons" class="form-control" step="0.5" min="0.5" placeholder="e.g. 7" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="price_per_km"><i class="fa-solid fa-tags" style="color: var(--primary);"></i> Rental Rate per Km (TZS)</label>
                    <input type="number" name="price_per_km" id="price_per_km" class="form-control" min="100" placeholder="e.g. 2500" required>
                </div>
            </div>

            <!-- Input for Lorry current geographic base location -->
            <div class="form-group">
                <label class="form-label" for="location"><i class="fa-solid fa-location-dot" style="color: var(--primary);"></i> Base Location</label>
                <input type="text" name="location" id="location" class="form-control" placeholder="e.g. Dar es Salaam, Kinondoni" required>
            </div>

            <!-- Text area description input -->
            <div class="form-group">
                <label class="form-label" for="description"><i class="fa-solid fa-file-waveform" style="color: var(--primary);"></i> Lorry Description</label>
                <textarea name="description" id="description" class="form-control" placeholder="Describe your lorry: condition, vehicle features, driver details, availability..."></textarea>
            </div>

            <!-- Input for vehicle photo upload -->
            <div class="form-group">
                <label class="form-label" for="photo"><i class="fa-solid fa-image" style="color: var(--primary);"></i> Lorry Photo</label>
                <input type="file" name="photo" id="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                <span class="form-hint">Maximum file size is 2MB. Only JPEG, PNG, or WebP formats are allowed.</span>
            </div>

            <!-- Submit form action button -->
            <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fa-solid fa-cloud-arrow-up"></i> Submit for Approval</button>
        </form>
    </div>
</div>
