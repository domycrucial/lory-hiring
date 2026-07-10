<div class="flex justify-between items-center mb-6" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6); flex-wrap: wrap; gap: var(--space-4);">
    <div>
        <h1 style="margin-bottom: var(--space-1);"><i class="fa-solid fa-users text-primary"></i> <?= currentLang() === 'sw' ? 'Dhibiti Watumiaji' : 'Manage Users' ?></h1>
        <p class="text-muted" style="margin-bottom: 0;">
            <?= currentLang() === 'sw' 
                ? 'Angalia wasifu wa watumiaji, sajili akaunti mpya, na uzuie ufikiaji kwa kusimamisha akaunti.' 
                : 'View user profiles, register new accounts, and restrict access by suspending accounts.' 
            ?>
        </p>
    </div>
    <!-- Add User Button -->
    <button class="btn btn-primary" onclick="openAddUserModal()" style="display: inline-flex; align-items: center; gap: var(--space-2);">
        <i class="fa-solid fa-user-plus"></i> <?= currentLang() === 'sw' ? 'Ongeza Mtumiaji' : 'Add User' ?>
    </button>
</div>

<!-- Add User Modal Dialog -->
<div id="addUserModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1050; transition: opacity 0.3s ease;">
    <div class="card p-6" style="width: 500px; max-width: 90%; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); animation: modalFadeIn 0.3s ease;">
        <div class="flex justify-between items-center mb-4" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: var(--space-4);">
            <h3 style="margin-bottom: 0;"><i class="fa-solid fa-user-plus text-primary"></i> <?= currentLang() === 'sw' ? 'Ongeza Mtumiaji mpya' : 'Add New User' ?></h3>
            <button onclick="closeAddUserModal()" style="background: none; border: none; font-size: 1.25rem; color: var(--gray-500); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form action="<?= APP_URL ?>/admin/users/create" method="POST">
            <?= csrfField() ?>
            <div class="form-group mb-4">
                <label for="new_full_name" class="form-label" style="font-weight: 600;"><?= currentLang() === 'sw' ? 'Jina Kamili' : 'Full Name' ?></label>
                <input type="text" id="new_full_name" name="full_name" class="form-control" placeholder="e.g. Salim Khalfan" required>
            </div>
            <div class="form-group mb-4">
                <label for="new_email" class="form-label" style="font-weight: 600;"><?= currentLang() === 'sw' ? 'Barua Pepe / Email' : 'Email Address' ?></label>
                <input type="email" id="new_email" name="email" class="form-control" placeholder="e.g. salim@gmail.com" required>
            </div>
            <div class="form-group mb-4">
                <label for="new_phone" class="form-label" style="font-weight: 600;"><?= currentLang() === 'sw' ? 'Namba ya Simu' : 'Phone Number' ?></label>
                <input type="tel" id="new_phone" name="phone" class="form-control" placeholder="e.g. +255712345678" required>
            </div>
            <div class="form-group mb-4">
                <label for="new_role" class="form-label" style="font-weight: 600;"><?= currentLang() === 'sw' ? 'Nafasi / Kazi' : 'Role' ?></label>
                <select id="new_role" name="role" class="form-control" required>
                    <option value="customer"><?= currentLang() === 'sw' ? 'Mteja' : 'Customer' ?></option>
                    <option value="lorry_owner"><?= currentLang() === 'sw' ? 'Mmiliki wa Lori' : 'Lorry Owner' ?></option>
                    <option value="admin"><?= currentLang() === 'sw' ? 'Msimamizi / Admin' : 'Administrator' ?></option>
                </select>
            </div>
            <div class="form-group mb-6">
                <label for="new_password" class="form-label" style="font-weight: 600;"><?= currentLang() === 'sw' ? 'Nenosiri / Password' : 'Password' ?></label>
                <input type="password" id="new_password" name="password" class="form-control" placeholder="Min 6 characters" required>
            </div>
            <div class="flex gap-2 justify-end" style="display: flex; gap: var(--space-2); justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeAddUserModal()"><?= currentLang() === 'sw' ? 'Futa' : 'Cancel' ?></button>
                <button type="submit" class="btn btn-primary"><?= currentLang() === 'sw' ? 'Hifadhi' : 'Save User' ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddUserModal() {
    document.getElementById('addUserModal').style.display = 'flex';
}
function closeAddUserModal() {
    document.getElementById('addUserModal').style.display = 'none';
}
</script>

<style>
@keyframes modalFadeIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>

<?php if (empty($usersList)): ?>
    <div class="card p-6 text-center mt-4">
        <p class="text-muted"><i class="fa-solid fa-user-slash"></i> <?= currentLang() === 'sw' ? 'Hakuna watumiaji waliopatikana.' : 'No users found.' ?></p>
    </div>
<?php else: ?>
    <div class="table-wrapper mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th><?= currentLang() === 'sw' ? 'Jina Kamili' : 'Full Name' ?></th>
                    <th><?= currentLang() === 'sw' ? 'Barua Pepe' : 'Email' ?></th>
                    <th><?= currentLang() === 'sw' ? 'Simu' : 'Phone' ?></th>
                    <th><?= currentLang() === 'sw' ? 'Kazi' : 'Role' ?></th>
                    <th><?= currentLang() === 'sw' ? 'Salio la Pochi' : 'Wallet Balance' ?></th>
                    <th><?= currentLang() === 'sw' ? 'Hali' : 'Status' ?></th>
                    <th><?= currentLang() === 'sw' ? 'Amejiunga' : 'Joined At' ?></th>
                    <th class="text-right"><?= currentLang() === 'sw' ? 'Hatua' : 'Actions' ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usersList as $u): ?>
                <tr>
                    <td><strong><?= e($u['full_name']) ?></strong></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['phone']) ?></td>
                    <td>
                        <span class="badge badge-secondary">
                            <?php
                            if ($u['role'] === 'customer') {
                                echo currentLang() === 'sw' ? 'Mteja' : 'Customer';
                            } elseif ($u['role'] === 'lorry_owner') {
                                echo currentLang() === 'sw' ? 'Mmiliki wa Lori' : 'Lorry Owner';
                            } else {
                                echo currentLang() === 'sw' ? 'Msimamizi' : 'Admin';
                            }
                            ?>
                        </span>
                    </td>
                    <td><?= formatTZS($u['wallet_balance'] ?? 0) ?></td>
                    <td>
                        <span class="badge badge-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>">
                            <?= e(currentLang() === 'sw' ? ($u['status'] === 'active' ? 'Imewezeshwa' : 'Imesimamishwa') : ucfirst($u['status'])) ?>
                        </span>
                    </td>
                    <td><?= formatDate($u['created_at']) ?></td>
                    <td class="text-right">
                        <?php if ($u['id'] !== currentUserId()): ?>
                            <?php if ($u['status'] === 'active'): ?>
                            <form action="<?= APP_URL ?>/admin/users/<?= (int)$u['id'] ?>/suspend" method="POST" style="display: inline-block;" class="confirm-action-form" data-message="<?= currentLang() === 'sw' ? 'Je, una uhakika unataka kumsimamisha ' . e($u['full_name']) . '?' : 'Are you sure you want to suspend ' . e($u['full_name']) . '?' ?>">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <button type="submit" class="btn btn-outline btn-danger btn-xs"><?= currentLang() === 'sw' ? 'Simamisha' : 'Suspend' ?></button>
                            </form>
                            <?php else: ?>
                            <form action="<?= APP_URL ?>/admin/users/<?= (int)$u['id'] ?>/activate" method="POST" style="display: inline-block;" class="confirm-action-form" data-message="<?= currentLang() === 'sw' ? 'Je, una uhakika unataka kumwezesha ' . e($u['full_name']) . '?' : 'Are you sure you want to activate ' . e($u['full_name']) . '?' ?>">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <button type="submit" class="btn btn-primary btn-xs"><?= currentLang() === 'sw' ? 'Wezesha' : 'Activate' ?></button>
                            </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted text-xs"><?= currentLang() === 'sw' ? 'Binafsi' : 'Self (Locked)' ?></span>
                        <?php endif; ?>
                    </td>
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
                    Inaonyesha Ukurasa <strong><?= $page ?></strong> kati ya <strong><?= $totalPages ?></strong> (Jumla: watumiaji <strong><?= $totalUsers ?></strong>)
                <?php else: ?>
                    Showing Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong> (Total: <strong><?= $totalUsers ?></strong> users)
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.confirm-action-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-message');
            Swal.fire({
                title: '<?= currentLang() === "sw" ? "Thibitisha Kitendo" : "Confirm Action" ?>',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#ef4444',
                confirmButtonText: '<?= currentLang() === "sw" ? "Ndiyo, Endelea" : "Yes, Proceed" ?>',
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
