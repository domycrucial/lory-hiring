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
            flashMessage('error', 'Invalid email or password. / Barua pepe au nywila si sahihi.');
            redirect(APP_URL . '/auth/login');
        }

        if ($user['status'] !== 'active') {
            flashMessage('error', 'Your account is not active. Contact support.');
            redirect(APP_URL . '/auth/login');
        }

        // Login success
        loginUser($user);
        flashMessage('success', 'Welcome back, ' . e($user['full_name']) . '! / Karibu tena!');

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
            flashMessage('error', implode(' ', $errors));
            redirect(APP_URL . '/auth/register');
        }

        // Check if email already exists
        $userModel = new User();
        if ($userModel->findByEmail($email)) {
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
            flashMessage('success', 'Account created! Please log in. / Akaunti imeundwa! Tafadhali ingia.');
            redirect(APP_URL . '/auth/login');
        } else {
            flashMessage('error', 'Registration failed. Please try again.');
            redirect(APP_URL . '/auth/register');
        }
    }

    /**
     * GET /auth/logout — Log the user out.
     */
    public function logout(): void
    {
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
        flashMessage('success', 'If that email exists, a reset link has been sent. (Simulated — check logs)');
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
