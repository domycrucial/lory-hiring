<div class="auth-wrapper">
    <div class="auth-card">
        <h1>🔐 Login</h1>
        <p class="text-center text-muted mb-6">Enter your credentials to access your account.</p>

        <form method="POST" action="<?= APP_URL ?>/auth/login" id="login-form">
            <?= csrfField() ?>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="example@email.com" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="login-btn">Login</button>
        </form>

        <p class="divider">or</p>
        <p class="text-center text-sm">
            <a href="<?= APP_URL ?>/auth/forgot-password">Forgot password?</a>
        </p>
        <p class="text-center text-sm mt-4">
            Don't have an account? <a href="<?= APP_URL ?>/auth/register"><strong>Register here</strong></a>
        </p>
    </div>
</div>
