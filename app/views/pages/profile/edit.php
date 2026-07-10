<h1><i class="fa-solid fa-user-pen"></i> Edit Profile</h1>
<p class="text-muted mb-6">Modify your personal details and language settings.</p>

<div class="card p-6" style="max-width: 600px;">
    <form action="<?= APP_URL ?>/profile/edit" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <div class="form-group mb-4">
            <label for="full_name" class="form-label">Full Name / Jina Kamili</label>
            <input type="text" id="full_name" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
        </div>

        <div class="form-group mb-4">
            <label for="phone" class="form-label">Phone Number / Namba ya Simu</label>
            <input type="text" id="phone" name="phone" class="form-control" value="<?= e($user['phone']) ?>" required>
        </div>

        <div class="form-group mb-6">
            <label for="preferred_lang" class="form-label">Preferred Language / Lugha Unayopendelea</label>
            <select id="preferred_lang" name="preferred_lang" class="form-control">
                <option value="en" <?= $user['preferred_lang'] === 'en' ? 'selected' : '' ?>>English</option>
                <option value="sw" <?= $user['preferred_lang'] === 'sw' ? 'selected' : '' ?>>Kiswahili</option>
            </select>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
            <a href="<?= APP_URL ?>/profile" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
