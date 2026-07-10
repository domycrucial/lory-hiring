<?php
/**
 * app/models/Withdrawal.php
 * Withdrawal data model.
 */

declare(strict_types=1);

class Withdrawal extends BaseModel
{
    protected string $table = 'withdrawals';

    /**
     * Get all pending withdrawal requests with owner details.
     */
    public function getPending(): array
    {
        $sql = "SELECT w.*, u.full_name AS owner_name, u.email AS owner_email, u.phone AS owner_phone
                FROM withdrawals w
                INNER JOIN users u ON u.id = w.owner_id
                WHERE w.status = 'pending'
                ORDER BY w.created_at ASC";
        return $this->rawQuery($sql);
    }

    /**
     * Get history for a specific owner.
     */
    public function getHistory(int $ownerId): array
    {
        return $this->findBy('owner_id', $ownerId, 'created_at DESC');
    }
}
