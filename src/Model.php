<?php

namespace App;

use App\Database;

/**
 * Model — Abstract base class for all database models
 * 
 * Provides common ORM functionality: find, create, update, delete
 * Subclasses define their own table name and schema
 */
abstract class Model
{
    protected Database $db;
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;

    /**
     * Initialize model with database connection
     * 
     * @param Database $db Database instance
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Get table name for this model
     */
    public static function getTable(): string
    {
        if (!isset(static::$table)) {
            throw new \Exception(static::class . ' must define protected static $table');
        }
        return static::$table;
    }

    /**
     * Create a new instance from database row
     * 
     * @param array $attributes Row data from database
     * @return static
     */
    public static function fromArray(Database $db, array $attributes): static
    {
        $instance = new static($db);
        $instance->attributes = $attributes;
        $instance->original = $attributes;
        $instance->exists = true;
        return $instance;
    }

    /**
     * Find a record by primary key
     * 
     * @param mixed $id Primary key value
     * @return static|null
     */
    public static function find(Database $db, $id): ?static
    {
        $table = static::getTable();
        $pk = static::$primaryKey;
        $row = $db->fetch("SELECT * FROM `$table` WHERE `$pk` = ?", [$id]);
        return $row ? static::fromArray($db, $row) : null;
    }

    /**
     * Find all records
     * 
     * @param string $orderBy Optional order by clause
     * @return array Array of model instances
     */
    public static function all(Database $db, string $orderBy = ''): array
    {
        $table = static::getTable();
        $sql = "SELECT * FROM `$table`";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        $rows = $db->fetchAll($sql);
        return array_map(fn($row) => static::fromArray($db, $row), $rows);
    }

    /**
     * Find records with WHERE clause
     * 
     * @param string $where WHERE clause (e.g., "status = ?")
     * @param array $params Parameters for WHERE
     * @return array Array of model instances
     */
    public static function where(Database $db, string $where, array $params = []): array
    {
        $table = static::getTable();
        $sql = "SELECT * FROM `$table` WHERE $where";
        $rows = $db->fetchAll($sql, $params);
        return array_map(fn($row) => static::fromArray($db, $row), $rows);
    }

    /**
     * Find first record matching WHERE clause
     * 
     * @param string $where WHERE clause
     * @param array $params Parameters
     * @return static|null
     */
    public static function firstWhere(Database $db, string $where, array $params = []): ?static
    {
        $table = static::getTable();
        $sql = "SELECT * FROM `$table` WHERE $where LIMIT 1";
        $row = $db->fetch($sql, $params);
        return $row ? static::fromArray($db, $row) : null;
    }

    /**
     * Count records
     * 
     * @param string $where Optional WHERE clause
     * @param array $params Parameters
     * @return int
     */
    public static function count(Database $db, string $where = '', array $params = []): int
    {
        $table = static::getTable();
        $sql = "SELECT COUNT(*) FROM `$table`";
        if ($where) {
            $sql .= " WHERE $where";
        }
        return (int) $db->fetchColumn($sql, $params);
    }

    /**
     * Paginate results
     * 
     * @param int $page Page number (1-based)
     * @param int $perPage Results per page
     * @param string $where Optional WHERE clause
     * @param array $params Parameters
     * @return array ['data' => [...], 'total' => n, 'page' => n, 'per_page' => n, 'pages' => n]
     */
    public static function paginate(
        Database $db,
        int $page = 1,
        int $perPage = 25,
        string $where = '',
        array $params = []
    ): array {
        $table = static::getTable();
        
        $total = static::count($db, $where, $params);
        $pages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM `$table`";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $sql .= " LIMIT ? OFFSET ?";
        
        $paginatedParams = array_merge($params, [$perPage, $offset]);
        $rows = $db->fetchAll($sql, $paginatedParams);

        return [
            'data' => array_map(fn($row) => static::fromArray($db, $row), $rows),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => max(1, $pages),
        ];
    }

    /**
     * Save model (insert or update)
     */
    public function save(): bool
    {
        if ($this->exists) {
            return $this->update();
        } else {
            return $this->create();
        }
    }

    /**
     * Create new record
     */
    protected function create(): bool
    {
        $table = static::getTable();
        $id = $this->db->insert($table, $this->attributes);
        
        if ($id) {
            $this->attributes[static::$primaryKey] = $id;
            $this->original = $this->attributes;
            $this->exists = true;
            return true;
        }
        return false;
    }

    /**
     * Update existing record
     */
    protected function update(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $table = static::getTable();
        $pk = static::$primaryKey;
        $id = $this->attributes[$pk];

        // Only update changed attributes
        $changes = [];
        foreach ($this->attributes as $key => $value) {
            if ($key !== $pk && (!isset($this->original[$key]) || $this->original[$key] !== $value)) {
                $changes[$key] = $value;
            }
        }

        if (empty($changes)) {
            return true;  // Nothing to update
        }

        $this->db->update($table, $changes, "`$pk` = ?", [$id]);
        $this->original = $this->attributes;
        return true;
    }

    /**
     * Delete record
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $table = static::getTable();
        $pk = static::$primaryKey;
        $id = $this->attributes[$pk];

        $this->db->delete($table, "`$pk` = ?", [$id]);
        $this->exists = false;
        return true;
    }

    /**
     * Mass attribute assignment
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                continue;  // Skip model properties
            }
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    /**
     * Get attribute value
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Set attribute value
     */
    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Check if attribute exists
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Get all attributes
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Get JSON representation
     */
    public function toJson(): string
    {
        return json_encode($this->attributes);
    }
}
