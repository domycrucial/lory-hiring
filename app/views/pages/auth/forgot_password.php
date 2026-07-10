<!-- Forgot Password / Password Reset Form Wrapper -->
<div class="auth-wrapper">
    <div class="auth-card" style="position: relative; padding-top: var(--space-10);">


        <h1><i class="fa-solid fa-key text-primary"></i> Reset Password</h1>
        <p class="text-center text-muted mb-6">Enter your registered email and set a new password for your account.</p>

        <!-- Submit form -->
        <form id="ajax-forgot-form">
            <?= csrfField() ?>
            <!-- Input for email address -->
            <div class="form-group mb-4">
                <label class="form-label" for="fp_email"><i class="fa-solid fa-envelope" style="color: var(--primary);"></i> Email Address</label>
                <input type="email" name="email" id="fp_email" class="form-control" placeholder="you@example.com" required>
            </div>
            <!-- Input for new password -->
            <div class="form-group mb-4">
                <label class="form-label" for="fp_password"><i class="fa-solid fa-lock" style="color: var(--primary);"></i> New Password / Nenosiri Jipya</label>
                <input type="password" name="password" id="fp_password" class="form-control" placeholder="Min 6 characters" required>
            </div>
            <!-- Confirm new password -->
            <div class="form-group mb-6">
                <label class="form-label" for="fp_confirm"><i class="fa-solid fa-circle-check" style="color: var(--primary);"></i> Confirm Password / Thibitisha</label>
                <input type="password" name="password_confirm" id="fp_confirm" class="form-control" placeholder="Re-enter new password" required>
            </div>
            <!-- Submit action button -->
            <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fa-solid fa-circle-check"></i> Reset Password / Badili Nenosiri</button>
        </form>

        <!-- Back to login link -->
        <p class="text-center text-sm mt-6">
            <a href="<?= APP_URL ?>/auth/login"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
        </p>
    </div>
</div>

<script>
document.getElementById('ajax-forgot-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = document.getElementById('fp_password').value;
    const confirm = document.getElementById('fp_confirm').value;
    
    if (password.length < 6) {
        Swal.fire('Error / Hitilafu', 'Password must be at least 6 characters.', 'error');
        return;
    }
    
    if (password !== confirm) {
        Swal.fire('Error / Hitilafu', 'Passwords do not match.', 'error');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('<?= APP_URL ?>/auth/forgot-password', {
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
                title: 'Password changed successfully! / Nenosiri limebadilishwa!',
                text: res.message,
                icon: 'success',
                confirmButtonColor: '#2563eb'
            }).then(() => {
                window.location.href = '<?= APP_URL ?>/auth/login';
            });
        } else {
            Swal.fire('Error / Hitilafu', res.message, 'error');
        }
    })
    .catch(() => {
        Swal.fire('Error / Hitilafu', 'An unexpected error occurred.', 'error');
    });
});
</script>
