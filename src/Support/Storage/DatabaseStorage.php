<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Support\Storage;

use Rumenx\PhpChatbot\Contracts\MemoryStorageInterface;

/**
 * Class DatabaseStorage
 *
 * Database-based storage implementation for conversation memory.
 * Uses PDO for database abstraction. Supports MySQL, PostgreSQL, and SQLite.
 *
 * @category AI
 * @package  Rumenx\PhpChatbot\Support\Storage
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class DatabaseStorage implements MemoryStorageInterface
{
    /**
     * PDO database connection.
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * Database table name for conversation storage.
     *
     * @var string
     */
    protected $tableName;

    /**
     * Constructor for DatabaseStorage.
     *
     * @param \PDO   $pdo       PDO database connection.
     * @param string $tableName Table name for conversation storage.
     *
     * @throws \RuntimeException If table doesn't exist and can't be created.
     */
    public function __construct(\PDO $pdo, string $tableName = 'chatbot_conversations')
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;

        // Ensure table exists
        $this->createTableIfNotExists();
    }

    /**
     * {@inheritDoc}
     */
    public function store(string $sessionId, array $data): bool
    {
        $jsonData = json_encode($data);

        if ($jsonData === false) {
            return false;
        }

        $sql = "INSERT INTO {$this->tableName} (session_id, data, updated_at) 
                VALUES (:session_id, :data, :updated_at)
                ON DUPLICATE KEY UPDATE data = :data2, updated_at = :updated_at2";

        // For PostgreSQL
        if ($this->getDriverName() === 'pgsql') {
            $sql = "INSERT INTO {$this->tableName} (session_id, data, updated_at) 
                    VALUES (:session_id, :data, :updated_at)
                    ON CONFLICT (session_id) 
                    DO UPDATE SET data = :data2, updated_at = :updated_at2";
        }

        // For SQLite
        if ($this->getDriverName() === 'sqlite') {
            $sql = "INSERT OR REPLACE INTO {$this->tableName} (session_id, data, updated_at) 
                    VALUES (:session_id, :data, :updated_at)";
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $timestamp = date('Y-m-d H:i:s');

            $params = [
                ':session_id' => $sessionId,
                ':data' => $jsonData,
                ':updated_at' => $timestamp
            ];

            // Add duplicate parameters for MySQL/PostgreSQL
            if ($this->getDriverName() !== 'sqlite') {
                $params[':data2'] = $jsonData;
                $params[':updated_at2'] = $timestamp;
            }

            return $stmt->execute($params);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve(string $sessionId): ?array
    {
        $sql = "SELECT data FROM {$this->tableName} WHERE session_id = :session_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':session_id' => $sessionId]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result === false || !isset($result['data'])) {
                return null;
            }

            $decoded = json_decode($result['data'], true);

            return is_array($decoded) ? $decoded : null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $sessionId): bool
    {
        $sql = "DELETE FROM {$this->tableName} WHERE session_id = :session_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':session_id' => $sessionId]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        $sql = "DELETE FROM {$this->tableName}";

        try {
            return $this->pdo->exec($sql) !== false;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $sessionId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE session_id = :session_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':session_id' => $sessionId]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result !== false && $result['count'] > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Create the conversations table if it doesn't exist.
     *
     * @return void
     * @throws \RuntimeException If table creation fails.
     */
    protected function createTableIfNotExists(): void
    {
        $driver = $this->getDriverName();

        if ($driver === 'mysql') {
            $sql = "CREATE TABLE IF NOT EXISTS {$this->tableName} (
                session_id VARCHAR(255) PRIMARY KEY,
                data TEXT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_updated_at (updated_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        } elseif ($driver === 'pgsql') {
            $sql = "CREATE TABLE IF NOT EXISTS {$this->tableName} (
                session_id VARCHAR(255) PRIMARY KEY,
                data TEXT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            CREATE INDEX IF NOT EXISTS idx_{$this->tableName}_updated_at 
                ON {$this->tableName} (updated_at)";
        } elseif ($driver === 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS {$this->tableName} (
                session_id TEXT PRIMARY KEY,
                data TEXT NOT NULL,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            );
            CREATE INDEX IF NOT EXISTS idx_{$this->tableName}_updated_at 
                ON {$this->tableName} (updated_at)";
        } else {
            throw new \RuntimeException("Unsupported database driver: {$driver}");
        }

        try {
            $this->pdo->exec($sql);
        } catch (\PDOException $e) {
            throw new \RuntimeException(
                "Failed to create table {$this->tableName}: " . $e->getMessage()
            );
        }
    }

    /**
     * Get the PDO driver name.
     *
     * @return string
     */
    protected function getDriverName(): string
    {
        return $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Get the PDO instance.
     *
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
}
