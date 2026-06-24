<?php
/**
 * app/models/Notification.php
 * Notification data model — in-app notification management.
 */

declare(strict_types=1);

class Notification extends BaseModel
{
    protected string $table = 'notifications';

    /**
     * Create a new notification for a user.
     */
    public function notify(
        int    $userId,
        string $type,
        string $title,
        string $message,
        string $link    = '',
        string $channel = 'in_app'
    ): int {
        return $this->create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => sanitizeString($title),
            'message' => sanitizeString($message),
            'link'    => $link,
            'is_read' => 0,
            'channel' => $channel,
        ]);
    }

    /**
     * Get unread notifications for a user.
     */
    public function getUnread(int $userId, int $limit = 20): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id AND is_read = 0 AND channel = 'in_app' ORDER BY created_at DESC LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Count unread notifications.
     */
    public function countUnread(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id AND is_read = 0 AND channel = 'in_app'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllRead(int $userId): int
    {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->rowCount();
    }
}
