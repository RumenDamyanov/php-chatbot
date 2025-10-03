<?php

namespace Rumenx\PhpChatbot\Support\Storage;

use Rumenx\PhpChatbot\Contracts\MemoryStorageInterface;

/**
 * Class RedisStorage
 *
 * Redis-based storage implementation for conversation memory.
 * Provides fast, distributed storage with optional TTL (time-to-live) support.
 *
 * Requires the Redis PHP extension or predis/predis package.
 *
 * @category AI
 * @package  Rumenx\PhpChatbot\Support\Storage
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class RedisStorage implements MemoryStorageInterface
{
    /**
     * Redis client instance.
     *
     * @var \Redis|object
     */
    protected $redis;

    /**
     * Key prefix for conversation storage.
     *
     * @var string
     */
    protected $keyPrefix;

    /**
     * Time-to-live for conversation data in seconds (0 = no expiration).
     *
     * @var int
     */
    protected $ttl;

    /**
     * Constructor for RedisStorage.
     *
     * @param \Redis|object $redis     Redis client instance (or Predis\Client).
     * @param string        $keyPrefix Key prefix for conversations.
     * @param int           $ttl       Time-to-live in seconds (0 = no expiration).
     *
     * @throws \RuntimeException If Redis connection is not valid.
     */
    public function __construct($redis, string $keyPrefix = 'chatbot:memory:', int $ttl = 0)
    {
        // Validate it's a proper Redis client (either extension or Predis)
        // Or duck-typed object with required methods (for testing)
        $isValidRedis = $redis instanceof \Redis;
        $isPredis = class_exists('Predis\Client') && is_a($redis, 'Predis\Client');
        $hasRequiredMethods = method_exists($redis, 'set') &&
                             method_exists($redis, 'get') &&
                             method_exists($redis, 'del');

        if (!$isValidRedis && !$isPredis && !$hasRequiredMethods) {
            throw new \RuntimeException(
                'Invalid Redis client. Requires \Redis extension or Predis\Client instance.'
            );
        }

        $this->redis = $redis;
        $this->keyPrefix = $keyPrefix;
        $this->ttl = $ttl;
    }

    /**
     * {@inheritDoc}
     */
    public function store(string $sessionId, array $data): bool
    {
        $key = $this->getKey($sessionId);
        $jsonData = json_encode($data);

        if ($jsonData === false) {
            return false;
        }

        if ($this->ttl > 0) {
            return $this->redis->setex($key, $this->ttl, $jsonData) !== false;
        }

        return $this->redis->set($key, $jsonData) !== false;
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve(string $sessionId): ?array
    {
        $key = $this->getKey($sessionId);
        $data = $this->redis->get($key);

        if ($data === false || $data === null) {
            return null;
        }

        $decoded = json_decode($data, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $sessionId): bool
    {
        $key = $this->getKey($sessionId);
        $result = $this->redis->del($key);
        return is_int($result) && $result > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        $pattern = $this->keyPrefix . '*';

        try {
            // Get all keys matching the pattern
            $keys = $this->redis->keys($pattern);

            if (empty($keys)) {
                return true;
            }

            // Delete all matching keys
            $result = $this->redis->del($keys);
            return is_int($result) && $result > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $sessionId): bool
    {
        $key = $this->getKey($sessionId);
        $result = $this->redis->exists($key);
        return is_int($result) ? $result > 0 : (bool) $result;
    }

    /**
     * Get the Redis key for a session.
     *
     * @param string $sessionId The session identifier.
     *
     * @return string The Redis key.
     */
    protected function getKey(string $sessionId): string
    {
        return $this->keyPrefix . $sessionId;
    }

    /**
     * Get the Redis client instance.
     *
     * @return \Redis|object
     */
    public function getRedisClient()
    {
        return $this->redis;
    }

    /**
     * Get the key prefix.
     *
     * @return string
     */
    public function getKeyPrefix(): string
    {
        return $this->keyPrefix;
    }

    /**
     * Get the TTL value.
     *
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * Set the TTL value.
     *
     * @param int $ttl Time-to-live in seconds (0 = no expiration).
     *
     * @return void
     */
    public function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }
}
