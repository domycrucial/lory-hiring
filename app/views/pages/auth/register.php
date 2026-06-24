<div class="auth-wrapper">
    <div class="auth-card">
        <h1>📝 Register</h1>
        <p class="text-center text-muted mb-6">Create your account to start using OLHS.</p>

        <form method="POST" action="<?= APP_URL ?>/auth/register" id="register-form">
            <?= csrfField() ?>

            <div class="form-group">
                <label class="form-label" for="full_name">Full Name</label>
                <input type="text" name="full_name" id="full_name" class="form-control" placeholder="John Doe" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="reg_email">Email Address</label>
                <input type="email" name="email" id="reg_email" class="form-control" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input type="tel" name="phone" id="phone" class="form-control" placeholder="+255 7XX XXX XXX" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="role">I am a</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="">— Select Role —</option>
                    <option value="customer">Customer</option>
                    <option value="lorry_owner">Lorry Owner</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="reg_password">Password</label>
                <input type="password" name="password" id="reg_password" class="form-control" placeholder="Min 8 chars, uppercase, lowercase, digit" required>
                <span class="form-hint">At least 8 characters with uppercase letters and numbers.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirm">Confirm Password</label>
                <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="Re-enter password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="register-btn">Create Account</button>
        </form>

        <p class="text-center text-sm mt-6">
            Already have an account? <a href="<?= APP_URL ?>/auth/login"><strong>Login here</strong></a>
        </p>
    </div>
</div>
