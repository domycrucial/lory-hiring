<?php
/**
 * app/controllers/NotificationController.php
 * Handles notification API endpoints (JSON).
 */

declare(strict_types=1);

class NotificationController
{
    /**
     * GET /api/v1/notifications — Get unread notifications as JSON.
     */
    public function getUnread(): void
    {
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'error' => 'Not logged in'], 401);
        }

        $model = new Notification();
        $unread = $model->getUnread(currentUserId());
        $count  = $model->countUnread(currentUserId());

        jsonResponse([
            'success' => true,
            'count'   => $count,
            'data'    => $unread,
        ]);
    }

    /**
     * POST /api/v1/notifications/read — Mark all as read.
     */
    public function markAllRead(): void
    {
        if (!isLoggedIn()) {
            jsonResponse(['success' => false, 'error' => 'Not logged in'], 401);
        }

        $model = new Notification();
        $model->markAllRead(currentUserId());

        jsonResponse(['success' => true]);
    }
}
