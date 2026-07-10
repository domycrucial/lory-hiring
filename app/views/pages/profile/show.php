<h1><i class="fa-solid fa-id-card"></i> My Profile</h1>
<p class="text-muted mb-6">Manage your account information and preferences.</p>

<div class="card p-6" style="max-width: 600px;">
    <div class="flex gap-6 items-center mb-6">
        <div style="width: 80px; height: 80px; background: var(--primary-light); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; text-transform: uppercase;">
            <?= mb_substr(currentUserName(), 0, 1) ?>
        </div>
        <div>
            <h2><?= e($user['full_name']) ?></h2>
            <p class="text-muted"><span class="badge badge-secondary"><?= e(ucwords(str_replace('_', ' ', $user['role']))) ?></span></p>
        </div>
    </div>

    <table class="table mb-6">
        <tr>
            <td class="font-bold" style="width: 150px;">Full Name</td>
            <td><?= e($user['full_name']) ?></td>
        </tr>
        <tr>
            <td class="font-bold">Email Address</td>
            <td><?= e($user['email']) ?></td>
        </tr>
        <tr>
            <td class="font-bold">Phone Number</td>
            <td><?= e($user['phone']) ?></td>
        </tr>
        <tr>
            <td class="font-bold">Preferred Language</td>
            <td><?= $user['preferred_lang'] === 'sw' ? 'Kiswahili' : 'English' ?></td>
        </tr>
        <tr>
            <td class="font-bold">Joined On</td>
            <td><?= formatDate($user['created_at']) ?></td>
        </tr>
    </table>

    <div class="flex gap-3">
        <a href="<?= APP_URL ?>/profile/edit" class="btn btn-primary"><i class="fa-solid fa-user-pen"></i> Edit Profile</a>
    </div>
</div>
