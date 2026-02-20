<?php

namespace App;

use PDO;
use PDOStatement;
use Exception;

/**
 * Database — PDO wrapper for secure, type-safe database operations
 * 
 * Provides prepared statement helpers, transaction management, and query logging
 */
class Database
{
    private PDO $pdo;
    private bool $inTransaction = false;
    private array $queryLog = [];
    private bool $logEnabled = false;

    /**
     * Initialize database connection
     * 
     * @param array $config Configuration array with host, name, user, pass, port, charset
     * @throws Exception If connection fails
     */
    public function __construct(array $config)
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'] ?? 'localhost',
                $config['port'] ?? 3306,
                $config['name'],
                $config['charset'] ?? 'utf8mb4'
            );

            $this->pdo = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,  // Use native prepared statements
            ]);

            // Set strict mode and charset
            $this->pdo->exec("SET character_set_client=utf8mb4");
            $this->pdo->exec("SET character_set_results=utf8mb4");
            $this->pdo->exec("SET character_set_connection=utf8mb4");
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS=1");

        } catch (Exception $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute a prepared statement with parameters
     * 
     * @param string $sql SQL query with ? placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement
     */
    public function prepare(string $sql, array $params = []): PDOStatement
    {
        $this->logQuery($sql, $params);

        $stmt = $this->pdo->prepare($sql);
        if (!empty($params)) {
            $stmt->execute($params);
        }

        return $stmt;
    }

    /**
     * Execute a query and return all results
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->prepare($sql, $params)->fetchAll();
    }

    /**
     * Execute a query and return single row
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array|null
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $result = $this->prepare($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Execute a query and return single column value
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return mixed
     */
    public function fetchColumn(string $sql, array $params = [])
    {
        $result = $this->prepare($sql, $params)->fetchColumn();
        return $result !== null ? $result : null;
    }

    /**
     * Execute INSERT and return inserted ID
     * 
     * @param string $table Table name
     * @param array $data Column => value pairs
     * @return string Last inserted row ID
     */
    public function insert(string $table, array $data): string
    {
        $columns = implode(', ', array_map(fn($col) => "`$col`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";

        $this->prepare($sql, array_values($data));
        return $this->pdo->lastInsertId();
    }

    /**
     * Execute UPDATE query
     * 
     * @param string $table Table name
     * @param array $data Column => value pairs to update
     * @param string $where WHERE clause (e.g., "id = ?")
     * @param array $whereParams Parameters for WHERE clause
     * @return int Number of rows affected
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = implode(', ', array_map(fn($col) => "`$col` = ?", array_keys($data)));
        $sql = "UPDATE `$table` SET $sets WHERE $where";

        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->prepare($sql, $params);

        return $stmt->rowCount();
    }

    /**
     * Execute DELETE query
     * 
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $whereParams Parameters for WHERE clause
     * @return int Number of rows deleted
     */
    public function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = "DELETE FROM `$table` WHERE $where";
        $stmt = $this->prepare($sql, $whereParams);
        return $stmt->rowCount();
    }

    /**
     * Begin database transaction
     */
    public function beginTransaction(): void
    {
        if (!$this->inTransaction) {
            $this->pdo->beginTransaction();
            $this->inTransaction = true;
        }
    }

    /**
     * Commit transaction
     */
    public function commit(): void
    {
        if ($this->inTransaction) {
            $this->pdo->commit();
            $this->inTransaction = false;
        }
    }

    /**
     * Rollback transaction
     */
    public function rollback(): void
    {
        if ($this->inTransaction) {
            $this->pdo->rollBack();
            $this->inTransaction = false;
        }
    }

    /**
     * Check if in transaction
     */
    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    /**
     * Get raw PDO instance (use sparingly)
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Enable/disable query logging
     */
    public function setLogging(bool $enabled): void
    {
        $this->logEnabled = $enabled;
    }

    /**
     * Log a query for debugging
     */
    private function logQuery(string $sql, array $params = []): void
    {
        if (!$this->logEnabled) {
            return;
        }

        $this->queryLog[] = [
            'sql' => $sql,
            'params' => $params,
            'timestamp' => microtime(true),
        ];
    }

    /**
     * Get query log
     */
    public function getQueryLog(): array
    {
        return $this->queryLog;
    }
}
