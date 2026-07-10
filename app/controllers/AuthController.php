<?php
/**
 * app/controllers/AuthController.php
 * Handles registration, login, logout, and password reset.
 */

declare(strict_types=1);

class AuthController
{
    /**
     * GET /auth/login — Show login form.
     */
    public function loginForm(): void
    {
        requireGuest();
        $pageTitle = 'Login';
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/auth/login.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /auth/login — Process login.
     */
    public function login(): void
    {
        verifyCsrf();

        $email    = sanitizeEmail(post('email'));
        $password = post('password');

        if (!$email || empty($password)) {
            flashMessage('error', 'Please enter a valid email and password.');
            redirect(APP_URL . '/auth/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            logSystemAction('login_failed', "Failed login attempt for email: {$email}");
            flashMessage('error', 'Invalid email or password. / Barua pepe au nywila si sahihi.');
            redirect(APP_URL . '/auth/login');
        }

        if ($user['status'] !== 'active') {
            flashMessage('error', 'Your account is not active. Contact support.');
            redirect(APP_URL . '/auth/login');
        }

        // Login success
        loginUser($user);
        logSystemAction('login_success', "User logged in: {$user['email']} (Role: {$user['role']})");

        // Redirect to return URL or dashboard
        $returnUrl = get('return');
        if (!empty($returnUrl)) {
            redirect(urldecode($returnUrl));
        }
        redirect(getDashboardUrl($user['role']));
    }

    /**
     * GET /auth/register — Show registration form.
     */
    public function registerForm(): void
    {
        requireGuest();
        $pageTitle = 'Register';
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/auth/register.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /auth/register — Process registration.
     */
    public function register(): void
    {
        verifyCsrf();

        $fullName = sanitizeString(post('full_name'));
        $email    = sanitizeEmail(post('email'));
        $phone    = sanitizePhone(post('phone'));
        $password = post('password');
        $confirm  = post('password_confirm');
        $role     = post('role');

        $errors = [];

        if (!validateLength($fullName, 2, 100)) {
            $errors[] = 'Full name must be 2-100 characters.';
        }
        if (!$email) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (empty($phone)) {
            $errors[] = 'Please enter a phone number.';
        }
        if (!validatePassword($password)) {
            $errors[] = 'Password must be at least 8 characters with uppercase, lowercase, and a digit.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }
        if (!in_array($role, ['customer', 'lorry_owner'])) {
            $errors[] = 'Please select a valid role.';
        }

        if (!empty($errors)) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => implode(' ', $errors)]);
            }
            flashMessage('error', implode(' ', $errors));
            redirect(APP_URL . '/auth/register');
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'An account with this email already exists.']);
            }
            flashMessage('error', 'An account with this email already exists.');
            redirect(APP_URL . '/auth/register');
        }

        // Create user
        $userId = $userModel->create([
            'full_name'     => $fullName,
            'email'         => $email,
            'phone'         => $phone,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role'          => $role,
            'status'        => 'active',
        ]);

        if ($userId) {
            logSystemAction('register_success', "New user registered: {$email} (Role: {$role})");
            if (isAjax()) {
                jsonResponse(['success' => true, 'message' => 'Account created! Please log in. / Akaunti imeundwa! Tafadhali ingia.']);
            }
            flashMessage('success', 'Account created! Please log in. / Akaunti imeundwa! Tafadhali ingia.');
            redirect(APP_URL . '/auth/login');
        } else {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Registration failed. Please try again.']);
            }
            flashMessage('error', 'Registration failed. Please try again.');
            redirect(APP_URL . '/auth/register');
        }
    }

    /**
     * GET /auth/logout — Log the user out.
     */
    public function logout(): void
    {
        $userEmail = isLoggedIn() ? $_SESSION['user_email'] ?? 'Unknown' : 'Unknown';
        logSystemAction('logout', "User logged out: {$userEmail}");
        logoutUser();
        flashMessage('success', 'You have been logged out. / Umetoka.');
        redirect(APP_URL . '/');
    }

    /**
     * GET /auth/forgot-password — Show forgot password form.
     */
    public function forgotPasswordForm(): void
    {
        $pageTitle = 'Forgot Password';
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/auth/forgot_password.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /auth/forgot-password — Process forgot password (simulated).
     */
    public function forgotPassword(): void
    {
        verifyCsrf();
        $email    = sanitizeEmail(post('email'));
        $password = post('password');
        $confirm  = post('password_confirm');

        $errors = [];
        if (!$email) {
            $errors[] = 'Valid email is required.';
        }
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => implode(' ', $errors)]);
            }
            flashMessage('error', implode(' ', $errors));
            redirect(APP_URL . '/auth/forgot-password');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'No user found with that email address.']);
            }
            flashMessage('error', 'No user found with that email address.');
            redirect(APP_URL . '/auth/forgot-password');
        }

        $userModel->update((int)$user['id'], [
            'password_hash' => password_hash($password, PASSWORD_BCRYPT)
        ]);

        logSystemAction('password_reset', "User reset password for email: {$email}");

        if (isAjax()) {
            jsonResponse(['success' => true, 'message' => 'Password reset successfully! / Nenosiri limebadilishwa kwa mafanikio!']);
        }

        flashMessage('success', 'Password reset successfully!');
        redirect(APP_URL . '/auth/login');
    }

    /**
     * GET /auth/reset-password — Show reset password form.
     */
    public function resetPasswordForm(): void
    {
        $pageTitle = 'Reset Password';
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/auth/forgot_password.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /auth/reset-password — Process password reset (simulated).
     */
    public function resetPassword(): void
    {
        verifyCsrf();
        flashMessage('info', 'Password reset is simulated in MVP mode.');
        redirect(APP_URL . '/auth/login');
    }
}
