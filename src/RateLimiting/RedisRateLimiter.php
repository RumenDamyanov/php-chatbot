<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\RateLimiting;

/**
 * Redis-based rate limiter for distributed systems.
 *
 * Uses Redis sorted sets for efficient sliding window rate limiting
 * across multiple servers. Requires ext-redis or predis.
 *
 * @package Rumenx\PhpChatbot\RateLimiting
 */
class RedisRateLimiter implements RateLimiterInterface
{
    /**
     * Redis client (supports both \Redis and \Predis\Client).
     *
     * @var \Redis|object
     */
    private object $redis;

    /**
     * Key prefix for all rate limit keys.
     *
     * @var string
     */
    private string $prefix;

    /**
     * Create a new Redis rate limiter.
     *
     * @param \Redis|object $redis  Redis client instance.
     * @param string        $prefix Key prefix (default: 'ratelimit:').
     */
    public function __construct(object $redis, string $prefix = 'ratelimit:')
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
    }

    /**
     * Check if a request is allowed under the rate limit.
     *
     * Uses Redis sorted sets with scores as timestamps for accurate
     * sliding window rate limiting in distributed environments.
     *
     * @param string $key           Unique identifier for the rate limit bucket.
     * @param int    $maxRequests   Maximum number of requests allowed.
     * @param int    $windowSeconds Time window in seconds.
     *
     * @return bool True if the request is allowed, false if rate limit exceeded.
     */
    public function allow(string $key, int $maxRequests, int $windowSeconds): bool
    {
        $redisKey = $this->prefix . $key;
        $now = microtime(true);
        $windowStart = $now - $windowSeconds;

        // Remove old entries outside the window
        $this->redis->zRemRangeByScore($redisKey, 0, $windowStart);

        // Count current requests in window
        $currentCount = $this->redis->zCard($redisKey);

        // Check if limit exceeded
        if ($currentCount >= $maxRequests) {
            return false;
        }

        // Add current request with unique identifier
        $requestId = $now . ':' . uniqid('', true);
        $this->redis->zAdd($redisKey, $now, $requestId);

        // Set expiration to cleanup old keys
        $this->redis->expire($redisKey, $windowSeconds + 1);

        return true;
    }

    /**
     * Get the number of requests remaining in the current window.
     *
     * @param string $key           Unique identifier for the rate limit bucket.
     * @param int    $maxRequests   Maximum number of requests allowed.
     * @param int    $windowSeconds Time window in seconds.
     *
     * @return int Number of requests remaining.
     */
    public function remaining(string $key, int $maxRequests, int $windowSeconds): int
    {
        $redisKey = $this->prefix . $key;
        $now = microtime(true);
        $windowStart = $now - $windowSeconds;

        // Remove old entries
        $this->redis->zRemRangeByScore($redisKey, 0, $windowStart);

        // Count current requests
        $currentCount = $this->redis->zCard($redisKey);

        return max(0, $maxRequests - $currentCount);
    }

    /**
     * Get the time in seconds until the rate limit resets.
     *
     * @param string $key           Unique identifier for the rate limit bucket.
     * @param int    $windowSeconds Time window in seconds.
     *
     * @return int Seconds until reset.
     */
    public function resetIn(string $key, int $windowSeconds): int
    {
        $redisKey = $this->prefix . $key;
        $now = microtime(true);
        $windowStart = $now - $windowSeconds;

        // Get oldest entry in current window
        $oldest = $this->redis->zRangeByScore($redisKey, $windowStart, '+inf', ['limit' => [0, 1]]);

        if (empty($oldest)) {
            return 0;
        }

        // Get score (timestamp) of oldest entry
        $oldestScore = $this->redis->zScore($redisKey, $oldest[0]);

        if ($oldestScore === false) {
            return 0;
        }

        $resetTime = $oldestScore + $windowSeconds;

        return max(0, (int) ceil($resetTime - $now));
    }

    /**
     * Reset the rate limit for a specific key.
     *
     * @param string $key Unique identifier for the rate limit bucket.
     *
     * @return void
     */
    public function reset(string $key): void
    {
        $redisKey = $this->prefix . $key;
        $this->redis->del($redisKey);
    }

    /**
     * Clear all rate limit data.
     *
     * Warning: This will delete ALL keys matching the prefix pattern.
     * Use with caution in production.
     *
     * @return void
     */
    public function clear(): void
    {
        $pattern = $this->prefix . '*';
        $keys = $this->redis->keys($pattern);

        if (!empty($keys)) {
            $this->redis->del(...$keys);
        }
    }
}
