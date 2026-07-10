<h1><i class="fa-solid fa-clock-rotate-left"></i> System Audit Logs</h1>
<p class="text-muted mb-6">Real-time system events, actions, and security updates recorded across the platform.</p>

<?php if (empty($logs)): ?>
    <div class="card p-6 text-center mt-4">
        <p class="text-muted"><i class="fa-solid fa-inbox"></i> No system logs recorded yet.</p>
    </div>
<?php else: ?>
    <div class="table-wrapper mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>Triggered By</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><small class="text-muted"><?= formatDateTime($log['created_at']) ?></small></td>
                    <td>
                        <span class="badge badge-secondary" style="font-family: monospace;">
                            <?= e($log['action']) ?>
                        </span>
                    </td>
                    <td><?= e($log['details']) ?></td>
                    <td>
                        <?php if ($log['user_id']): ?>
                            <strong><?= e($log['user_name']) ?></strong><br>
                            <small class="text-muted"><?= e($log['user_email']) ?></small>
                        <?php else: ?>
                            <span class="text-muted text-xs">System / Guest</span>
                        <?php endif; ?>
                    </td>
                    <td><code><?= e($log['ip_address'] ?? '—') ?></code></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination controls -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                <?php if (currentLang() === 'sw'): ?>
                    Inaonyesha Ukurasa <strong><?= $page ?></strong> kati ya <strong><?= $totalPages ?></strong> (Jumla: matukio <strong><?= $totalLogs ?></strong>)
                <?php else: ?>
                    Showing Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong> (Total: <strong><?= $totalLogs ?></strong> log entries)
                <?php endif; ?>
            </div>
            <nav class="pagination-nav">
                <!-- Previous Button -->
                <a href="?page=<?= $page - 1 ?>" class="pagination-link nav-btn <?= $page <= 1 ? 'disabled' : '' ?>" title="<?= currentLang() === 'sw' ? 'Ukurasa Uliopita' : 'Previous Page' ?>">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
                
                <!-- Next Button -->
                <a href="?page=<?= $page + 1 ?>" class="pagination-link nav-btn <?= $page >= $totalPages ? 'disabled' : '' ?>" title="<?= currentLang() === 'sw' ? 'Ukurasa Ujao' : 'Next Page' ?>">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </nav>
        </div>
    <?php endif; ?>
<?php endif; ?>
