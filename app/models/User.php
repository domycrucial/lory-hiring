<?php
/**
 * app/models/User.php
 * User data model — handles all user-related DB operations.
 * Covers: registration, login, password management, profile.
 */

declare(strict_types=1);

class User extends BaseModel
{
    protected string $table = 'users';

    protected function hasSoftDelete(): bool
    {
        return true;
    }

    // ─────────────────────────────────────────────────────────
    // AUTHENTICATION
    // ─────────────────────────────────────────────────────────

    /**
     * Find a user by email address (case-insensitive).
     * Used during login and password reset.
     *
     * @param string $email Email address
     * @return array|null User record or null if not found
     */
    public function findByEmail(string $email): ?array
    {
        $sql  = "SELECT * FROM users
                 WHERE email = :email AND deleted_at IS NULL
                 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => strtolower(trim($email))]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Verify a plain-text password against the stored bcrypt hash.
     *
     * @param string $plainText   Raw password from login form
     * @param string $storedHash  bcrypt hash from the database
     * @return bool True if password matches
     */
    public function verifyPassword(string $plainText, string $storedHash): bool
    {
        return password_verify($plainText, $storedHash);
    }

    /**
     * Hash a plain-text password using bcrypt (cost 12).
     *
     * @param string $plainText Raw password
     * @return string bcrypt hash
     */
    public function hashPassword(string $plainText): string
    {
        return password_hash($plainText, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Register a new user account.
     *
     * @param array $data [full_name, email, phone, password, role]
     * @return int Newly created user ID
     */
    public function register(array $data): int
    {
        return $this->create([
            'full_name'     => sanitizeString($data['full_name']),
            'email'         => strtolower(trim($data['email'])),
            'phone'         => sanitizePhone($data['phone']),
            'password_hash' => $this->hashPassword($data['password']),
            'role'          => $data['role'] ?? 'customer',
            'status'        => 'active',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Check if an email is already registered.
     *
     * @param string $email
     * @return bool True if email exists
     */
    public function emailExists(string $email): bool
    {
        $sql  = "SELECT COUNT(*) FROM users WHERE email = :email AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => strtolower(trim($email))]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ─────────────────────────────────────────────────────────
    // ACCOUNT LOCKOUT (after 5 failed login attempts)
    // ─────────────────────────────────────────────────────────

    /**
     * Increment the failed login attempt counter.
     * Locks the account for LOCKOUT_MINUTES minutes after LOGIN_MAX_ATTEMPTS failures.
     *
     * @param int $userId
     * @return void
     */
    public function incrementLoginAttempts(int $userId): void
    {
        $sql  = "UPDATE users
                 SET login_attempts = login_attempts + 1,
                     lockout_until  = CASE
                         WHEN login_attempts + 1 >= :max_attempts
                         THEN DATE_ADD(NOW(), INTERVAL :lockout_minutes MINUTE)
                         ELSE lockout_until
                     END
                 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':max_attempts'    => LOGIN_MAX_ATTEMPTS,
            ':lockout_minutes' => LOCKOUT_MINUTES,
            ':id'              => $userId,
        ]);
    }

    /**
     * Reset the login attempt counter on successful login.
     *
     * @param int $userId
     * @return void
     */
    public function resetLoginAttempts(int $userId): void
    {
        $this->update($userId, [
            'login_attempts' => 0,
            'lockout_until'  => null,
        ]);
    }

    /**
     * Check if an account is currently locked out.
     *
     * @param array $user User record from DB
     * @return bool True if locked
     */
    public function isLockedOut(array $user): bool
    {
        if (empty($user['lockout_until'])) {
            return false;
        }
        return strtotime($user['lockout_until']) > time();
    }

    // ─────────────────────────────────────────────────────────
    // PASSWORD RESET
    // ─────────────────────────────────────────────────────────

    /**
     * Create a password reset token (SHA-256 hashed before storing in DB).
     * Returns the raw token to be emailed — never stored in plaintext.
     *
     * @param string $email User's email
     * @return string Raw token to include in the reset link
     */
    public function createPasswordResetToken(string $email): string
    {
        // Invalidate any existing unused tokens for this email
        $sql  = "UPDATE password_resets SET used = 1 WHERE email = :email AND used = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);

        // Generate a cryptographically secure random token
        $rawToken  = bin2hex(random_bytes(32));  // 64 hex chars = 256 bits
        $tokenHash = hash('sha256', $rawToken);   // Store only the hash

        // Insert new token with 1-hour expiry
        $sql  = "INSERT INTO password_resets (email, token_hash, expires_at)
                 VALUES (:email, :token_hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email'      => $email,
            ':token_hash' => $tokenHash,
        ]);

        return $rawToken; // Only the raw token is ever sent via email
    }

    /**
     * Verify a password reset token and return the associated email.
     *
     * @param string $rawToken   Token from the URL query parameter
     * @param string $email      Email from the URL query parameter
     * @return bool True if token is valid, unexpired, and unused
     */
    public function verifyPasswordResetToken(string $rawToken, string $email): bool
    {
        $tokenHash = hash('sha256', $rawToken);

        $sql  = "SELECT id FROM password_resets
                 WHERE email = :email
                   AND token_hash = :token_hash
                   AND used = 0
                   AND expires_at > NOW()
                 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email'      => $email,
            ':token_hash' => $tokenHash,
        ]);

        return $stmt->fetch() !== false;
    }

    /**
     * Reset the user's password and mark the token as used.
     *
     * @param string $rawToken   Raw token (will be hashed to find the record)
     * @param string $email      User's email address
     * @param string $newPassword Plain-text new password
     * @return bool True on success
     */
    public function resetPassword(string $rawToken, string $email, string $newPassword): bool
    {
        if (!$this->verifyPasswordResetToken($rawToken, $email)) {
            return false;
        }

        // Update password
        $user = $this->findByEmail($email);
        if (!$user) {
            return false;
        }

        $this->update((int)$user['id'], [
            'password_hash' => $this->hashPassword($newPassword),
            'login_attempts' => 0,
            'lockout_until'  => null,
        ]);

        // Mark token as used
        $tokenHash = hash('sha256', $rawToken);
        $sql  = "UPDATE password_resets SET used = 1 WHERE token_hash = :token_hash";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token_hash' => $tokenHash]);

        return true;
    }

    // ─────────────────────────────────────────────────────────
    // WALLET (Lorry Owners)
    // ─────────────────────────────────────────────────────────

    /**
     * Credit an amount to a lorry owner's wallet.
     * Uses SELECT FOR UPDATE inside a transaction to prevent race conditions.
     *
     * @param int   $ownerId Lorry owner user ID
     * @param float $amount  Amount to credit (TZS)
     * @return bool
     */
    public function creditWallet(int $ownerId, float $amount): bool
    {
        try {
            $this->db->beginTransaction();

            // Lock the row to prevent concurrent updates
            $stmt = $this->db->prepare(
                "SELECT wallet_balance FROM users WHERE id = :id FOR UPDATE"
            );
            $stmt->execute([':id' => $ownerId]);
            $row = $stmt->fetch();

            if (!$row) {
                $this->db->rollBack();
                return false;
            }

            $newBalance = (float)$row['wallet_balance'] + $amount;

            $this->update($ownerId, ['wallet_balance' => $newBalance]);
            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('[OLHS Wallet] Credit failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Debit an amount from a lorry owner's wallet (for withdrawals).
     * Checks sufficient balance before deducting.
     *
     * @param int   $ownerId Lorry owner user ID
     * @param float $amount  Amount to debit (TZS)
     * @return bool False if insufficient balance
     */
    public function debitWallet(int $ownerId, float $amount): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "SELECT wallet_balance FROM users WHERE id = :id FOR UPDATE"
            );
            $stmt->execute([':id' => $ownerId]);
            $row = $stmt->fetch();

            if (!$row || (float)$row['wallet_balance'] < $amount) {
                $this->db->rollBack();
                return false; // Insufficient balance
            }

            $newBalance = (float)$row['wallet_balance'] - $amount;
            $this->update($ownerId, ['wallet_balance' => $newBalance]);
            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('[OLHS Wallet] Debit failed: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────
    // ADMIN OPERATIONS
    // ─────────────────────────────────────────────────────────

    /**
     * Get all users with optional role filter and search.
     * Used by admin panel.
     *
     * @param string $role   Filter by role ('' = all)
     * @param string $search Search term for name or email
     * @param int    $limit  Records per page
     * @param int    $offset Pagination offset
     * @return array
     */
    public function adminGetUsers(
        string $role = '',
        string $search = '',
        int $limit = 20,
        int $offset = 0
    ): array {
        $where  = ['deleted_at IS NULL'];
        $params = [];

        if (!empty($role)) {
            $where[]        = 'role = :role';
            $params[':role'] = $role;
        }
        if (!empty($search)) {
            $where[]          = '(full_name LIKE :search OR email LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT id, full_name, email, phone, role, status, wallet_balance, created_at
                FROM users
                WHERE {$whereClause}
                ORDER BY created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        return $this->rawQuery($sql, $params);
    }

    /**
     * Get count of users registered today, this month, and total.
     *
     * @return array ['today', 'month', 'total']
     */
    public function getUserStats(): array
    {
        return [
            'today' => (int)$this->rawScalar(
                "SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL"
            ),
            'month' => (int)$this->rawScalar(
                "SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(NOW())
                 AND YEAR(created_at) = YEAR(NOW()) AND deleted_at IS NULL"
            ),
            'total' => $this->count(),
        ];
    }

    /**
     * Get count of users with optional role filter and search (for pagination).
     *
     * @param string $role
     * @param string $search
     * @return int
     */
    public function countUsers(string $role = '', string $search = ''): int
    {
        $where  = ['deleted_at IS NULL'];
        $params = [];

        if (!empty($role)) {
            $where[]        = 'role = :role';
            $params[':role'] = $role;
        }
        if (!empty($search)) {
            $where[]          = '(full_name LIKE :search OR email LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) FROM users WHERE {$whereClause}";
        return (int)$this->rawScalar($sql, $params);
    }
}
