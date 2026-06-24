<!-- Forgot Password Form Wrapper -->
<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Main card heading -->
        <h1><i class="fa-solid fa-key"></i> Forgot Password</h1>
        <!-- Supporting instructions text -->
        <p class="text-center text-muted mb-6">Enter your registered email address below and we will email you a secure link to reset your account password.</p>

        <!-- Submit form -->
        <form method="POST" action="<?= APP_URL ?>/auth/forgot-password" id="forgot-form">
            <?= csrfField() ?>
            <!-- Input for email address -->
            <div class="form-group">
                <label class="form-label" for="fp_email"><i class="fa-solid fa-envelope" style="color: var(--primary);"></i> Email Address</label>
                <input type="email" name="email" id="fp_email" class="form-control" placeholder="you@example.com" required>
            </div>
            <!-- Submit form action button -->
            <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fa-solid fa-paper-plane"></i> Send Reset Link</button>
        </form>

        <!-- Back to login link -->
        <p class="text-center text-sm mt-6">
            <a href="<?= APP_URL ?>/auth/login"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
        </p>
    </div>
</div>
