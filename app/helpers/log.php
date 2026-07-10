<?php
/**
 * app/helpers/log.php
 * Real-time system auditing helper.
 */

declare(strict_types=1);

/**
 * Logs a system-wide user or admin action.
 * Automatically checks and initializes the database table if not present.
 *
 * @param string $action  Short tag representing the action (e.g., 'user_login')
 * @param string $details Detailed description of the action
 */
function logSystemAction(string $action, string $details): void
{
    try {
        $db = getDB();

        // Self-healing database table migration
        static $migrationChecked = false;
        if (!$migrationChecked) {
            $db->exec("CREATE TABLE IF NOT EXISTS `system_logs` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `user_id` INT UNSIGNED NULL DEFAULT NULL,
              `action` VARCHAR(100) NOT NULL,
              `details` TEXT NOT NULL,
              `ip_address` VARCHAR(45) NULL DEFAULT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              INDEX `idx_action` (`action`),
              INDEX `idx_created_at` (`created_at`),
              CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
            $migrationChecked = true;
        }

        $userId = isLoggedIn() ? (int)currentUserId() : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        $stmt = $db->prepare("
            INSERT INTO system_logs (user_id, action, details, ip_address) 
            VALUES (:user_id, :action, :details, :ip_address)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':details' => $details,
            ':ip_address' => $ip
        ]);
    } catch (Exception $e) {
        error_log('[OLHS Audit Logs Error] Failed writing audit log: ' . $e->getMessage());
    }
}

/**
 * Checks if the current request is an AJAX or JSON request.
 *
 * @return bool
 */
function isAjax(): bool
{
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
           (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) ||
           (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json'));
}
