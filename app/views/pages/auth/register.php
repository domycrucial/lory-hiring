<div class="auth-wrapper">
    <div class="auth-card" style="position: relative; padding-top: var(--space-10);">


        <h1><i class="fa-solid fa-user-plus text-primary"></i> <?= currentLang() === 'sw' ? 'Jisajili' : 'Register' ?></h1>
        <p class="text-center text-muted mb-6"><?= currentLang() === 'sw' ? 'Unda akaunti yako ili kuanza kutumia OLHS.' : 'Create your account to start using OLHS.' ?></p>

        <form method="POST" action="<?= APP_URL ?>/auth/register" id="register-form">
            <?= csrfField() ?>

            <div class="form-group mb-4">
                <label class="form-label" for="full_name"><?= currentLang() === 'sw' ? 'Jina Kamili' : 'Full Name' ?></label>
                <input type="text" name="full_name" id="full_name" class="form-control" placeholder="John Doe" required>
            </div>

            <div class="form-group mb-4">
                <label class="form-label" for="reg_email"><?= currentLang() === 'sw' ? 'Barua Pepe / Email' : 'Email Address' ?></label>
                <input type="email" name="email" id="reg_email" class="form-control" placeholder="you@example.com" required>
            </div>

            <div class="form-group mb-4">
                <label class="form-label" for="phone"><?= currentLang() === 'sw' ? 'Namba ya Simu' : 'Phone Number' ?></label>
                <input type="tel" name="phone" id="phone" class="form-control" placeholder="+255 7XX XXX XXX" required>
            </div>

            <div class="form-group mb-4">
                <label class="form-label" for="role"><?= currentLang() === 'sw' ? 'Mimi ni' : 'I am a' ?></label>
                <select name="role" id="role" class="form-control" required>
                    <option value="">— <?= currentLang() === 'sw' ? 'Chagua Aina' : 'Select Role' ?> —</option>
                    <option value="customer"><?= currentLang() === 'sw' ? 'Mteja' : 'Customer' ?></option>
                    <option value="lorry_owner"><?= currentLang() === 'sw' ? 'Mmiliki wa Lori' : 'Lorry Owner' ?></option>
                </select>
            </div>

            <div class="form-group mb-4">
                <label class="form-label" for="reg_password"><?= currentLang() === 'sw' ? 'Nenosiri / Password' : 'Password' ?></label>
                <input type="password" name="password" id="reg_password" class="form-control" placeholder="Min 8 chars, uppercase, lowercase, digit" required>
                <span class="form-hint"><?= currentLang() === 'sw' ? 'Herufi zisizopungua 8 zikiwemo kubwa, ndogo na namba.' : 'At least 8 characters with uppercase letters and numbers.' ?></span>
            </div>

            <div class="form-group mb-6">
                <label class="form-label" for="password_confirm"><?= currentLang() === 'sw' ? 'Thibitisha Nenosiri' : 'Confirm Password' ?></label>
                <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="Re-enter password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="register-btn"><?= currentLang() === 'sw' ? 'Tengeneza Akaunti' : 'Create Account' ?></button>
        </form>

        <p class="text-center text-sm mt-6">
            <?= currentLang() === 'sw' ? 'Tayari una akaunti?' : 'Already have an account?' ?> <a href="<?= APP_URL ?>/auth/login"><strong><?= currentLang() === 'sw' ? 'Ingia hapa' : 'Login here' ?></strong></a>
        </p>
    </div>
</div>

<script>
document.getElementById('register-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const password = document.getElementById('reg_password').value;
    const confirm = document.getElementById('password_confirm').value;
    
    if (password.length < 8) {
        Swal.fire('Error / Hitilafu', 'Password must be at least 8 characters.', 'error');
        return;
    }
    if (password !== confirm) {
        Swal.fire('Error / Hitilafu', 'Passwords do not match.', 'error');
        return;
    }
    
    const formData = new FormData(this);
    fetch('<?= APP_URL ?>/auth/register', {
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
                title: 'Account created! / Akaunti imeundwa!',
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
        Swal.fire('Error / Hitilafu', 'Registration failed. Please try again.', 'error');
    });
});
</script>
