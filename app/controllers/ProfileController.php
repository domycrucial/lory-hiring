<?php
/**
 * app/controllers/ProfileController.php
 * Handles user profile viewing and editing.
 */

declare(strict_types=1);

class ProfileController
{
    public function __construct()
    {
        requireLogin();
    }

    /**
     * GET /profile — View profile.
     */
    public function show(): void
    {
        $userId = currentUserId();
        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user) {
            flashMessage('error', 'User not found.');
            redirect(APP_URL . '/');
        }

        $pageTitle = 'My Profile';
        $currentPage = 'profile';

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/profile/show.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * GET /profile/edit — Show edit profile form.
     */
    public function editForm(): void
    {
        $userId = currentUserId();
        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user) {
            flashMessage('error', 'User not found.');
            redirect(APP_URL . '/');
        }

        $pageTitle = 'Edit Profile';
        $currentPage = 'profile';

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/profile/edit.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /profile/edit — Save profile updates.
     */
    public function update(): void
    {
        verifyCsrf();
        $userId = currentUserId();
        
        $fullName = sanitizeString(post('full_name'));
        $phone = sanitizePhone(post('phone'));
        $lang = post('preferred_lang', 'en');

        if (empty($fullName) || empty($phone)) {
            flashMessage('error', 'Full name and phone number are required.');
            redirect(APP_URL . '/profile/edit');
        }

        if (!in_array($lang, ['en', 'sw'], true)) {
            $lang = 'en';
        }

        $userModel = new User();
        $updated = $userModel->update($userId, [
            'full_name' => $fullName,
            'phone' => $phone,
            'preferred_lang' => $lang,
        ]);

        // Update session info
        $_SESSION['user_name'] = $fullName;
        $_SESSION['user_lang'] = $lang;

        flashMessage('success', 'Profile updated successfully! / Wasifu wako umebadilishwa!');
        redirect(APP_URL . '/profile');
    }
}
