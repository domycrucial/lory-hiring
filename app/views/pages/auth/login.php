<div class="auth-wrapper">
    <div class="auth-card" style="position: relative; padding-top: var(--space-10);">
        <!-- Back to Home arrow -->
        <a href="<?= APP_URL ?>/" class="auth-back-arrow" style="position: absolute; top: var(--space-4); left: var(--space-4); color: var(--gray-500); text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 6px; font-size: 0.85rem;"><i class="fa-solid fa-arrow-left"></i> Home</a>
        <h1><i class="fa-solid fa-lock text-primary"></i> <?= currentLang() === 'sw' ? 'Ingia' : 'Login' ?></h1>
        <p class="text-center text-muted mb-6"><?= currentLang() === 'sw' ? 'Weka taarifa zako ili kuingia kwenye akaunti yako.' : 'Enter your credentials to access your account.' ?></p>

        <form method="POST" action="<?= APP_URL ?>/auth/login" id="login-form">
            <?= csrfField() ?>

            <div class="form-group">
                <label class="form-label" for="email"><?= currentLang() === 'sw' ? 'Barua Pepe / Email' : 'Email Address' ?></label>
                <input type="email" name="email" id="email" class="form-control" placeholder="example@email.com" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password"><?= currentLang() === 'sw' ? 'Nenosiri / Password' : 'Password' ?></label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="login-btn"><?= currentLang() === 'sw' ? 'Ingia' : 'Login' ?></button>
        </form>

        <p class="divider">or</p>
        <p class="text-center text-sm">
            <a href="<?= APP_URL ?>/auth/forgot-password"><?= currentLang() === 'sw' ? 'Umesahau nenosiri?' : 'Forgot password?' ?></a>
        </p>
        <p class="text-center text-sm mt-4">
            <?= currentLang() === 'sw' ? 'Huna akaunti?' : "Don't have an account?" ?> <a href="<?= APP_URL ?>/auth/register"><strong><?= currentLang() === 'sw' ? 'Jisajili hapa' : 'Register here' ?></strong></a>
        </p>
    </div>
</div>
