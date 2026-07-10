<?php
/**
 * app/models/BaseModel.php
 * Base class for all database models.
 * Provides shared PDO query methods to avoid code duplication.
 *
 * All child models extend this class and inherit:
 *   - findById()     — fetch one record by primary key
 *   - findAll()      — fetch all records (with optional WHERE)
 *   - create()       — insert a new record
 *   - update()       — update an existing record
 *   - softDelete()   — mark a record as deleted (deleted_at = NOW())
 *   - count()        — count records matching a condition
 */

declare(strict_types=1);

abstract class BaseModel
{
    /** @var PDO Shared database connection */
    protected PDO $db;

    /** @var string Database table name — MUST be set in each child class */
    protected string $table = '';

    /** @var string Primary key column name */
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = getDB(); // Singleton PDO connection from config/db.php
    }

    // ─────────────────────────────────────────────────────────
    // READ METHODS
    // ─────────────────────────────────────────────────────────

    /**
     * Find a single record by its primary key.
     * Automatically excludes soft-deleted records if the table has deleted_at.
     *
     * @param int $id Record ID
     * @return array|null Record as associative array, or null if not found
     */
    public function findById(int $id): ?array
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $sql .= $this->hasSoftDelete() ? " AND `deleted_at` IS NULL" : "";
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    /**
     * Fetch all records from the table.
     * Automatically excludes soft-deleted records.
     *
     * @param string $orderBy Column and direction (e.g. 'created_at DESC')
     * @param int    $limit   Maximum number of records to return (0 = no limit)
     * @param int    $offset  Starting offset for pagination
     * @return array List of records
     */
    public function findAll(string $orderBy = 'id DESC', int $limit = 0, int $offset = 0): array
    {
        $sql  = "SELECT * FROM `{$this->table}`";
        $sql .= $this->hasSoftDelete() ? " WHERE `deleted_at` IS NULL" : "";
        $sql .= " ORDER BY {$orderBy}";
        if ($limit > 0) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find records matching a specific column value.
     *
     * @param string $column Column name to filter by
     * @param mixed  $value  Value to match
     * @param string $orderBy Sort order
     * @return array Matching records
     */
    public function findBy(string $column, mixed $value, string $orderBy = 'id DESC'): array
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE `{$column}` = :value";
        $sql .= $this->hasSoftDelete() ? " AND `deleted_at` IS NULL" : "";
        $sql .= " ORDER BY {$orderBy}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':value' => $value]);
        return $stmt->fetchAll();
    }

    /**
     * Find a single record matching a column value.
     *
     * @param string $column Column name
     * @param mixed  $value  Value to match
     * @return array|null
     */
    public function findOneBy(string $column, mixed $value): ?array
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE `{$column}` = :value";
        $sql .= $this->hasSoftDelete() ? " AND `deleted_at` IS NULL" : "";
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':value' => $value]);
        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    // ─────────────────────────────────────────────────────────
    // WRITE METHODS
    // ─────────────────────────────────────────────────────────

    /**
     * Insert a new record into the table.
     *
     * @param array $data Associative array of column => value pairs
     * @return int The new record's primary key ID
     * @throws PDOException On query failure
     */
    public function create(array $data): int
    {
        $columns      = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($data)));
        $placeholders = implode(', ', array_map(fn($c) => ":{$c}", array_keys($data)));

        $sql  = "INSERT INTO `{$this->table}` ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        // Bind with named placeholders
        foreach ($data as $column => $value) {
            $stmt->bindValue(":{$column}", $value);
        }

        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing record by primary key.
     *
     * @param int   $id   Record ID to update
     * @param array $data Associative array of column => value pairs to update
     * @return int Number of affected rows
     */
    public function update(int $id, array $data): int
    {
        $sets = implode(', ', array_map(fn($c) => "`{$c}` = :{$c}", array_keys($data)));
        $sql  = "UPDATE `{$this->table}` SET {$sets} WHERE `{$this->primaryKey}` = :__id";

        $stmt = $this->db->prepare($sql);
        foreach ($data as $column => $value) {
            $stmt->bindValue(":{$column}", $value);
        }
        $stmt->bindValue(':__id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Soft-delete a record by setting deleted_at to NOW().
     * Only call this on tables that have a deleted_at column.
     *
     * @param int $id Record ID
     * @return int Affected rows
     */
    public function softDelete(int $id): int
    {
        $sql  = "UPDATE `{$this->table}` SET `deleted_at` = NOW()
                 WHERE `{$this->primaryKey}` = :id AND `deleted_at` IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    /**
     * Hard-delete a record permanently.
     * Use sparingly — prefer softDelete() for important data.
     *
     * @param int $id Record ID
     * @return int Affected rows
     */
    public function hardDelete(int $id): int
    {
        $sql  = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    // ─────────────────────────────────────────────────────────
    // UTILITY METHODS
    // ─────────────────────────────────────────────────────────

    /**
     * Count records matching an optional WHERE clause.
     *
     * @param string $where      SQL WHERE clause (without the WHERE keyword)
     * @param array  $params     Bound parameter values
     * @return int Record count
     */
    public function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";

        $conditions = [];
        if ($this->hasSoftDelete()) {
            $conditions[] = '`deleted_at` IS NULL';
        }
        if (!empty($where)) {
            $conditions[] = "({$where})";
        }
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Execute a raw SQL query with bound parameters.
     * Use this for complex queries not covered by the base methods.
     * ALWAYS use prepared statements — never concatenate user input.
     *
     * @param string $sql    Prepared SQL statement
     * @param array  $params Bound parameter values
     * @return array Result rows
     */
    public function rawQuery(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a raw SQL statement and return the affected row count.
     *
     * @param string $sql    Prepared SQL statement
     * @param array  $params Bound parameter values
     * @return int Affected rows
     */
    public function rawExecute(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Run a single-value aggregate query (e.g. SUM, MAX, COUNT).
     *
     * @param string $sql    Prepared SQL with a single SELECT expression
     * @param array  $params Bound parameter values
     * @return mixed Scalar result
     */
    public function rawScalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Check if this table has a soft-delete (deleted_at) column.
     * Override in child models that don't support soft delete.
     *
     * @return bool
     */
    protected function hasSoftDelete(): bool
    {
        return false; // Default false; override in User, Lorry models
    }
}
