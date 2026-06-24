<div class="<?= !empty($inDashboard) ? '' : 'container' ?>" style="<?= !empty($inDashboard) ? '' : 'padding: var(--space-8) 0;' ?>">
    <div class="flex justify-between items-center mb-6">
        <h1><i class="fa-solid fa-truck"></i> My Lorries</h1>
        <a href="<?= APP_URL ?>/lorries/add" class="btn btn-primary">+ Add Lorry</a>
    </div>

    <?php if (empty($lorries)): ?>
        <div class="card p-6 text-center">
            <p style="font-size: 3rem;">🚛</p>
            <h3>No lorries listed yet</h3>
            <p class="text-muted">Add your first lorry to start receiving bookings!</p>
            <a href="<?= APP_URL ?>/lorries/add" class="btn btn-accent mt-4">+ Add Your First Lorry</a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th><th>Type</th><th>Plate</th><th>Price/km</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lorries as $l): ?>
                    <tr>
                        <td><strong><?= e($l['name'] ?? 'Lorry') ?></strong></td>
                        <td><?= e(LORRY_TYPES[$l['lorry_type']]['en'] ?? $l['lorry_type'] ?? '—') ?></td>
                        <td><?= e($l['plate_number'] ?? '—') ?></td>
                        <td><?= number_format((float)($l['price_per_km'] ?? 0)) ?> TZS</td>
                        <td><span class="badge status-<?= e($l['approval_status'] ?? 'pending') ?>"><?= e(ucfirst($l['approval_status'] ?? 'pending')) ?></span></td>
                        <td class="flex gap-2">
                            <a href="<?= APP_URL ?>/lorries/<?= (int)$l['id'] ?>/edit" class="btn btn-outline btn-sm">Edit</a>
                            <form method="POST" action="<?= APP_URL ?>/lorries/<?= (int)$l['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this lorry?')">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
