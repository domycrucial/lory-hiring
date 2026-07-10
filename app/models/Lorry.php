<?php
/**
 * app/models/Lorry.php
 * Lorry data model — handles lorry listings, search, photos, and approval.
 */

declare(strict_types=1);

class Lorry extends BaseModel
{
    protected string $table = 'lorries';

    protected function hasSoftDelete(): bool
    {
        return true;
    }

    // ─────────────────────────────────────────────────────────
    // SEARCH & DISCOVERY
    // ─────────────────────────────────────────────────────────

    /**
     * Search approved lorries with optional filters and pagination.
     * Only returns lorries with approval_status = 'approved'.
     *
     * @param array $filters [type, location, min_capacity, max_capacity, min_price, max_price, availability]
     * @param string $sortBy  Column to sort by (price_per_km, avg_rating, total_trips)
     * @param int    $limit   Records per page
     * @param int    $offset  Pagination offset
     * @return array List of lorry records with owner info and primary photo
     */
    public function search(
        array  $filters = [],
        string $sortBy  = 'avg_rating',
        int    $limit   = 12,
        int    $offset  = 0
    ): array {
        $where  = [
            "l.approval_status = 'approved'",
        ];
        $params = [];

        // Filter by lorry type
        if (!empty($filters['type'])) {
            $where[]         = 'l.lorry_type = :type';
            $params[':type'] = $filters['type'];
        }

        // Filter by location (city/area text search)
        if (!empty($filters['location'])) {
            $where[]             = 'l.current_location LIKE :location';
            $params[':location'] = '%' . $filters['location'] . '%';
        }

        // Filter by availability
        if (!empty($filters['availability'])) {
            $where[]               = 'l.availability_status = :avail';
            $params[':avail']      = $filters['availability'];
        }

        // Filter by minimum capacity
        if (!empty($filters['min_capacity'])) {
            $where[]                  = 'l.capacity_tonnes >= :min_cap';
            $params[':min_cap']       = (float)$filters['min_capacity'];
        }

        // Filter by maximum capacity
        if (!empty($filters['max_capacity'])) {
            $where[]                  = 'l.capacity_tonnes <= :max_cap';
            $params[':max_cap']       = (float)$filters['max_capacity'];
        }

        // Filter by maximum price per km
        if (!empty($filters['max_price'])) {
            $where[]                  = 'l.price_per_km <= :max_price';
            $params[':max_price']     = (float)$filters['max_price'];
        }

        // Allowed sort columns (whitelist to prevent SQL injection)
        $allowedSorts = ['price_per_km', 'avg_rating', 'total_trips', 'created_at'];
        $sortColumn   = in_array($sortBy, $allowedSorts) ? $sortBy : 'avg_rating';

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT
                    l.*,
                    u.full_name  AS owner_name,
                    u.phone      AS owner_phone,
                    p.photo_path AS primary_photo
                FROM lorries l
                INNER JOIN users u ON u.id = l.owner_id AND u.deleted_at IS NULL
                LEFT JOIN lorry_photos p ON p.lorry_id = l.id AND p.is_primary = 1
                WHERE {$whereClause}
                ORDER BY l.{$sortColumn} DESC
                LIMIT {$limit} OFFSET {$offset}";

        return $this->rawQuery($sql, $params);
    }

    /**
     * Count total results for a search (used for pagination).
     *
     * @param array $filters Same filters as search()
     * @return int Total matching lorry count
     */
    public function countSearch(array $filters = []): int
    {
        $where  = ["l.approval_status = 'approved'"];
        $params = [];

        if (!empty($filters['type'])) {
            $where[]         = 'l.lorry_type = :type';
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['location'])) {
            $where[]             = 'l.current_location LIKE :location';
            $params[':location'] = '%' . $filters['location'] . '%';
        }

        $sql = "SELECT COUNT(*) FROM lorries l WHERE " . implode(' AND ', $where);
        return (int)$this->rawScalar($sql, $params);
    }

    /**
     * Get full lorry detail with owner info and all photos.
     * Used on the lorry detail page.
     *
     * @param int $lorryId
     * @return array|null
     */
    public function getDetailById(int $lorryId): ?array
    {
        // Fetch main lorry + owner info
        $sql  = "SELECT l.*, u.full_name AS owner_name, u.phone AS owner_phone,
                        u.email AS owner_email, u.profile_photo AS owner_photo
                 FROM lorries l
                 INNER JOIN users u ON u.id = l.owner_id
                 WHERE l.id = :id AND l.approval_status = 'approved'
                 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $lorryId]);
        $lorry = $stmt->fetch();

        if (!$lorry) {
            return null;
        }

        // Fetch all photos for this lorry
        $lorry['photos'] = $this->getPhotos($lorryId);

        // Compute live average rating from reviews table
        $lorry['avg_rating'] = $this->computeAvgRating($lorryId);

        return $lorry;
    }

    // ─────────────────────────────────────────────────────────
    // OWNER LORRY MANAGEMENT
    // ─────────────────────────────────────────────────────────

    /**
     * Get all lorries belonging to a specific owner.
     *
     * @param int $ownerId
     * @return array
     */
    public function getByOwner(int $ownerId): array
    {
        $sql  = "SELECT l.*, p.photo_path AS primary_photo
                 FROM lorries l
                 LEFT JOIN lorry_photos p ON p.lorry_id = l.id AND p.is_primary = 1
                 WHERE l.owner_id = :owner_id
                 ORDER BY l.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':owner_id' => $ownerId]);
        return $stmt->fetchAll();
    }

    /**
     * Add a new lorry listing (starts with approval_status = 'pending').
     *
     * @param array $data Lorry data from the add form
     * @return int New lorry ID
     */
    public function addLorry(array $data): int
    {
        return $this->create([
            'owner_id'            => $data['owner_id'],
            'name'                => sanitizeString($data['name']),
            'lorry_type'          => $data['lorry_type'],
            'capacity_tonnes'     => (float)$data['capacity_tonnes'],
            'plate_number'        => strtoupper(sanitizeString($data['plate_number'])),
            'price_per_km'        => !empty($data['price_per_km']) ? (float)$data['price_per_km'] : null,
            'base_price'          => !empty($data['base_price']) ? (float)$data['base_price'] : null,
            'current_location'    => sanitizeString($data['current_location']),
            'lat'                 => !empty($data['lat']) ? (float)$data['lat'] : null,
            'lng'                 => !empty($data['lng']) ? (float)$data['lng'] : null,
            'description'         => sanitizeString($data['description'] ?? ''),
            'availability_status' => 'available',
            'approval_status'     => 'pending',
        ]);
    }

    /**
     * Update availability status of a lorry.
     *
     * @param int    $lorryId  Lorry ID
     * @param int    $ownerId  Must match lorry owner (authorisation check)
     * @param string $status   New status: available|on_trip|maintenance
     * @return bool
     */
    public function updateAvailability(int $lorryId, int $ownerId, string $status): bool
    {
        $allowed = ['available', 'on_trip', 'maintenance'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $sql  = "UPDATE lorries
                 SET availability_status = :status
                 WHERE id = :id AND owner_id = :owner_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':status'   => $status,
            ':id'       => $lorryId,
            ':owner_id' => $ownerId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Recalculate and update the avg_rating cached column.
     * Called after a new review is submitted.
     *
     * @param int $lorryId
     * @return void
     */
    public function refreshAvgRating(int $lorryId): void
    {
        $avg = $this->computeAvgRating($lorryId);
        $this->update($lorryId, ['avg_rating' => $avg]);
    }

    /**
     * Compute average rating directly from reviews table.
     *
     * @param int $lorryId
     * @return float
     */
    public function computeAvgRating(int $lorryId): float
    {
        $sql  = "SELECT COALESCE(AVG(rating), 0) FROM reviews
                 WHERE lorry_id = :id AND is_flagged = 0";
        return (float)$this->rawScalar($sql, [':id' => $lorryId]);
    }

    /**
     * Get all reviews for a specific lorry.
     *
     * @param int $lorryId
     * @return array
     */
    public function getReviews(int $lorryId): array
    {
        $sql = "SELECT r.*, u.full_name AS reviewer_name
                FROM reviews r
                INNER JOIN users u ON u.id = r.reviewer_id
                WHERE r.lorry_id = :lorry_id AND r.is_flagged = 0
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':lorry_id' => $lorryId]);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────────────────
    // PHOTOS
    // ─────────────────────────────────────────────────────────

    /**
     * Get all photos for a lorry.
     *
     * @param int $lorryId
     * @return array
     */
    public function getPhotos(int $lorryId): array
    {
        $sql  = "SELECT * FROM lorry_photos WHERE lorry_id = :id ORDER BY is_primary DESC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $lorryId]);
        return $stmt->fetchAll();
    }

    /**
     * Add a photo record for a lorry.
     *
     * @param int    $lorryId   Lorry ID
     * @param string $photoPath Relative path to stored image
     * @param bool   $isPrimary Whether this is the main display photo
     * @return int New photo record ID
     */
    public function addPhoto(int $lorryId, string $photoPath, bool $isPrimary = false): int
    {
        $sql  = "INSERT INTO lorry_photos (lorry_id, photo_path, is_primary)
                 VALUES (:lorry_id, :path, :primary)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':lorry_id' => $lorryId,
            ':path'     => $photoPath,
            ':primary'  => $isPrimary ? 1 : 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Count the number of photos for a lorry.
     *
     * @param int $lorryId
     * @return int
     */
    public function photoCount(int $lorryId): int
    {
        $sql  = "SELECT COUNT(*) FROM lorry_photos WHERE lorry_id = :id";
        return (int)$this->rawScalar($sql, [':id' => $lorryId]);
    }

    // ─────────────────────────────────────────────────────────
    // ADMIN OPERATIONS
    // ─────────────────────────────────────────────────────────

    /**
     * Get all lorries pending admin approval.
     *
     * @return array
     */
    public function getPendingApprovals(): array
    {
        $sql = "SELECT l.*, u.full_name AS owner_name, u.email AS owner_email, u.phone AS owner_phone,
                       p.photo_path AS primary_photo
                FROM lorries l
                INNER JOIN users u ON u.id = l.owner_id
                LEFT JOIN lorry_photos p ON p.lorry_id = l.id AND p.is_primary = 1
                WHERE l.approval_status = 'pending'
                ORDER BY l.created_at ASC";
        return $this->rawQuery($sql);
    }

    /**
     * Approve a lorry listing (admin action).
     *
     * @param int $lorryId
     * @return bool
     */
    public function approve(int $lorryId): bool
    {
        return $this->update($lorryId, ['approval_status' => 'approved']) > 0;
    }

    /**
     * Reject a lorry listing with a reason (admin action).
     *
     * @param int    $lorryId
     * @param string $reason
     * @return bool
     */
    public function reject(int $lorryId, string $reason): bool
    {
        return $this->update($lorryId, [
            'approval_status'  => 'rejected',
            'rejection_reason' => sanitizeString($reason),
        ]) > 0;
    }

    /**
     * Get count of active (approved) lorries.
     *
     * @return int
     */
    public function countActive(): int
    {
        return (int)$this->rawScalar(
            "SELECT COUNT(*) FROM lorries WHERE approval_status = 'approved'"
        );
    }
}
