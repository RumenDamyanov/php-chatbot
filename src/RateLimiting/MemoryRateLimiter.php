<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\RateLimiting;

/**
 * In-memory rate limiter using sliding window algorithm.
 *
 * This implementation stores request timestamps in memory and is suitable
 * for single-server deployments or development/testing. For production
 * multi-server deployments, use RedisRateLimiter instead.
 *
 * @package Rumenx\PhpChatbot\RateLimiting
 */
class MemoryRateLimiter implements RateLimiterInterface
{
    /**
     * Storage for request timestamps.
     * Format: ['key' => [timestamp1, timestamp2, ...]]
     *
     * @var array<string, array<int>>
     */
    private array $buckets = [];

    /**
     * Check if a request is allowed under the rate limit.
     *
     * Uses a sliding window algorithm that tracks individual request timestamps.
     * This provides accurate rate limiting but requires more memory than fixed windows.
     *
     * @param string $key           Unique identifier for the rate limit bucket.
     * @param int    $maxRequests   Maximum number of requests allowed.
     * @param int    $windowSeconds Time window in seconds.
     *
     * @return bool True if the request is allowed, false if rate limit exceeded.
     */
    public function allow(string $key, int $maxRequests, int $windowSeconds): bool
    {
        $now = time();
        $windowStart = $now - $windowSeconds;

        // Initialize bucket if it doesn't exist
        if (!isset($this->buckets[$key])) {
            $this->buckets[$key] = [];
        }

        // Remove timestamps outside the current window
        $this->buckets[$key] = array_values(array_filter(
            $this->buckets[$key],
            fn(int $timestamp): bool => $timestamp > $windowStart
        ));

        // Check if limit is exceeded
        if (count($this->buckets[$key]) >= $maxRequests) {
            return false;
        }

        // Record this request
        $this->buckets[$key][] = $now;

        return true;
    }

    /**
     * Get the number of requests remaining in the current window.
     *
     * @param string $key           Unique identifier for the rate limit bucket.
     * @param int    $maxRequests   Maximum number of requests allowed.
     * @param int    $windowSeconds Time window in seconds.
     *
     * @return int Number of requests remaining (0 or positive).
     */
    public function remaining(string $key, int $maxRequests, int $windowSeconds): int
    {
        $now = time();
        $windowStart = $now - $windowSeconds;

        if (!isset($this->buckets[$key])) {
            return $maxRequests;
        }

        // Count requests in current window
        $requestsInWindow = count(array_filter(
            $this->buckets[$key],
            fn(int $timestamp): bool => $timestamp > $windowStart
        ));

        return max(0, $maxRequests - $requestsInWindow);
    }

    /**
     * Get the time in seconds until the rate limit resets.
     *
     * Returns the time until the oldest request in the window expires.
     *
     * @param string $key           Unique identifier for the rate limit bucket.
     * @param int    $windowSeconds Time window in seconds.
     *
     * @return int Seconds until reset (0 if no active requests).
     */
    public function resetIn(string $key, int $windowSeconds): int
    {
        if (!isset($this->buckets[$key]) || empty($this->buckets[$key])) {
            return 0;
        }

        $now = time();
        $windowStart = $now - $windowSeconds;
        
        // Get oldest timestamp in current window
        $timestamps = array_filter(
            $this->buckets[$key],
            fn(int $timestamp): bool => $timestamp > $windowStart
        );

        if (empty($timestamps)) {
            return 0;
        }

        $oldestTimestamp = min($timestamps);
        $resetTime = $oldestTimestamp + $windowSeconds;

        return max(0, $resetTime - $now);
    }

    /**
     * Reset the rate limit for a specific key.
     *
     * Removes all request records for the given key.
     *
     * @param string $key Unique identifier for the rate limit bucket.
     *
     * @return void
     */
    public function reset(string $key): void
    {
        unset($this->buckets[$key]);
    }

    /**
     * Clear all rate limit data.
     *
     * Useful for testing or clearing state.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->buckets = [];
    }

    /**
     * Get the current state of all buckets (for debugging).
     *
     * @return array<string, array<int>>
     * @internal This method is for debugging and testing only.
     */
    public function getBuckets(): array
    {
        return $this->buckets;
    }
}

