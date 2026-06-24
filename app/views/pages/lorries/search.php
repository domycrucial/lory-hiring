<!-- Search Page Content Container -->
<div class="<?= !empty($inDashboard) ? '' : 'container' ?>" style="<?= !empty($inDashboard) ? '' : 'padding: var(--space-8) 0;' ?>">
    <!-- Main page heading -->
    <h1><i class="fa-solid fa-magnifying-glass"></i> Search Lorries</h1>

    <!-- ─── Search Filters Component ─────────────────────────────────── -->
    <!-- Filter form collecting parameters for query -->
    <form method="GET" action="<?= APP_URL ?>/lorries/search" class="card p-6 mb-8" id="search-form">
        <div class="grid grid-4">
            <!-- Selector for truck types -->
            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-truck-moving"></i> Lorry Type</label>
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <?php foreach (LORRY_TYPES as $key => $labels): ?>
                        <!-- Loop over preset truck configurations -->
                        <option value="<?= e($key) ?>" <?= (get('type') === $key) ? 'selected' : '' ?>><?= e($labels['en']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Input for geographic region/city location -->
            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-map-location-dot"></i> Location</label>
                <input type="text" name="location" class="form-control" placeholder="e.g. Dar es Salaam" value="<?= e(get('location')) ?>">
            </div>
            <!-- Input for maximum price limit -->
            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-hand-holding-dollar"></i> Max Price (TZS/km)</label>
                <input type="number" name="max_price" class="form-control" placeholder="e.g. 5000" value="<?= e(get('max_price')) ?>">
            </div>
            <!-- Submit action buttons -->
            <div class="form-group" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </div>
        </div>
    </form>

    <!-- ─── Search Results Section ───────────────────────────────────── -->
    <!-- Renders fallback state if search result matches no listings -->
    <?php if (empty($lorries)): ?>
        <div class="card p-8 text-center">
            <!-- Large info icon -->
            <p style="font-size: 4rem; color: var(--gray-400);"><i class="fa-solid fa-truck-flatbed"></i></p>
            <h3 class="mt-4">No lorries found</h3>
            <p class="text-muted">Try adjusting your search filters or browse all available vehicles.</p>
            <a href="<?= APP_URL ?>/lorries/search" class="btn btn-outline mt-4">Reset Filters</a>
        </div>
    <?php else: ?>
        <!-- List layout showing matching listings cards -->
        <div class="flex flex-col gap-6" style="max-width: 900px; margin: 0 auto;">
            <?php foreach ($lorries as $lorry): ?>
            <div class="card-horizontal">
                <!-- Smooth hover scaling zoom wrapper -->
                <div class="card-img-wrapper">
                    <?php 
                    $photoUrl = getLorryPhotoUrl($lorry['primary_photo'] ?? null);
                    if ($photoUrl): 
                    ?>
                        <img src="<?= $photoUrl ?>" alt="<?= e($lorry['name'] ?? 'Lorry') ?>" class="card-img">
                    <?php else: ?>
                        <!-- Standard SVG layout fallback icon -->
                        <div style="width:100%; height:100%; display: flex; align-items: center; justify-content: center; font-size: 4rem; background: var(--gray-100);">🚛</div>
                    <?php endif; ?>
                </div>
                <!-- Card contents -->
                <div class="card-body">
                    <div class="card-content-top">
                        <div>
                            <!-- Title head -->
                            <h3 class="card-title" style="margin-bottom: var(--space-2);"><?= e($lorry['name'] ?? 'Lorry') ?></h3>
                            <!-- Spec key value details lines -->
                            <p class="card-text" style="margin-bottom: 0;">
                                <span style="margin-right: var(--space-4); display: inline-flex; align-items: center; gap: 6px;"><i class="fa-solid fa-location-dot" style="color: var(--primary);"></i> <?= e($lorry['current_location'] ?? '—') ?></span>
                                <span style="margin-right: var(--space-4); display: inline-flex; align-items: center; gap: 6px;"><i class="fa-solid fa-truck-moving" style="color: var(--primary);"></i> <?= e(LORRY_TYPES[$lorry['lorry_type']]['en'] ?? $lorry['lorry_type'] ?? '—') ?></span>
                                <span style="display: inline-flex; align-items: center; gap: 6px;"><i class="fa-solid fa-weight-hanging" style="color: var(--primary);"></i> <?= e($lorry['capacity_tonnes'] ?? '—') ?> tons</span>
                            </p>
                        </div>
                        <!-- Star ratings display row -->
                        <div class="text-right">
                            <?= renderStars((float)($lorry['avg_rating'] ?? 0)) ?>
                            <div class="text-sm text-muted" style="margin-top: 2px;">(<?= (int)($lorry['total_trips'] ?? 0) ?> trips)</div>
                        </div>
                    </div>
                    
                    <div class="card-content-bottom">
                        <div style="font-size: 1.2rem; color: var(--gray-900);">
                            Rate: <strong class="text-primary"><?= number_format((float)($lorry['price_per_km'] ?? 0)) ?> TZS/km</strong>
                        </div>
                        <!-- CTA view and booking button -->
                        <a href="<?= APP_URL ?>/lorries/<?= (int)$lorry['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-calendar-days"></i> View & Book</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
