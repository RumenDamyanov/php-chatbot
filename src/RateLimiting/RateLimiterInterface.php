<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\RateLimiting;

/**
 * Interface for rate limiting implementations.
 *
 * Rate limiters control the frequency of requests to prevent abuse
 * and stay within API provider limits.
 *
 * @package Rumenx\PhpChatbot\RateLimiting
 */
interface RateLimiterInterface
{
    /**
     * Check if a request is allowed under the rate limit.
     *
     * @param string $key           Unique identifier for the rate limit bucket (e.g., user ID, IP).
     * @param int    $maxRequests   Maximum number of requests allowed.
     * @param int    $windowSeconds Time window in seconds.
     *
     * @return bool True if the request is allowed, false if rate limit exceeded.
     */
    public function allow(string $key, int $maxRequests, int $windowSeconds): bool;

    /**
     * Get the number of requests remaining in the current window.
     *
     * @param string $key           Unique identifier for the rate limit bucket.
     * @param int    $maxRequests   Maximum number of requests allowed.
     * @param int    $windowSeconds Time window in seconds.
     *
     * @return int Number of requests remaining.
     */
    public function remaining(string $key, int $maxRequests, int $windowSeconds): int;

    /**
     * Get the time in seconds until the rate limit resets.
     *
     * @param string $key           Unique identifier for the rate limit bucket.
     * @param int    $windowSeconds Time window in seconds.
     *
     * @return int Seconds until reset, or 0 if no active limit.
     */
    public function resetIn(string $key, int $windowSeconds): int;

    /**
     * Reset the rate limit for a specific key.
     *
     * @param string $key Unique identifier for the rate limit bucket.
     *
     * @return void
     */
    public function reset(string $key): void;

    /**
     * Clear all rate limit data (useful for testing).
     *
     * @return void
     */
    public function clear(): void;
}

