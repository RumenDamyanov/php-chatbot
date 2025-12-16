<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Cache;

use Rumenx\PhpChatbot\Support\ChatResponse;

/**
 * Interface for response caching implementations.
 *
 * Caches AI responses to reduce API costs and improve response times
 * for repeated or similar queries.
 *
 * @package Rumenx\PhpChatbot\Cache
 */
interface CacheInterface
{
    /**
     * Get a cached response by key.
     *
     * @param string $key Cache key.
     *
     * @return ChatResponse|null The cached response, or null if not found or expired.
     */
    public function get(string $key): ?ChatResponse;

    /**
     * Store a response in the cache.
     *
     * @param string       $key      Cache key.
     * @param ChatResponse $response The response to cache.
     * @param int          $ttl      Time-to-live in seconds (0 = forever).
     *
     * @return bool True if stored successfully, false otherwise.
     */
    public function set(string $key, ChatResponse $response, int $ttl = 3600): bool;

    /**
     * Check if a key exists in the cache.
     *
     * @param string $key Cache key.
     *
     * @return bool True if the key exists and is not expired.
     */
    public function has(string $key): bool;

    /**
     * Delete a cached response.
     *
     * @param string $key Cache key.
     *
     * @return bool True if deleted successfully, false otherwise.
     */
    public function delete(string $key): bool;

    /**
     * Clear all cached responses.
     *
     * @return bool True if cleared successfully, false otherwise.
     */
    public function clear(): bool;

    /**
     * Generate a cache key from input and context.
     *
     * Creates a deterministic key based on the input message and relevant
     * context parameters (model, prompt, temperature, etc.).
     *
     * @param string               $input   User input message.
     * @param array<string, mixed> $context Context parameters.
     *
     * @return string The generated cache key.
     */
    public function generateKey(string $input, array $context = []): string;
}

