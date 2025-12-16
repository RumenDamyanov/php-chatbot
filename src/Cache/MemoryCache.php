<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Cache;

use Rumenx\PhpChatbot\Support\ChatResponse;

/**
 * In-memory response cache.
 *
 * Stores cached responses in memory for the duration of the request.
 * Suitable for development, testing, or single-request caching.
 * For persistent caching, use FileCache or RedisCache.
 *
 * @package Rumenx\PhpChatbot\Cache
 */
class MemoryCache implements CacheInterface
{
    /**
     * Cache storage with expiration times.
     * Format: ['key' => ['response' => ChatResponse, 'expires_at' => timestamp]]
     *
     * @var array<string, array{response: ChatResponse, expires_at: int}>
     */
    private array $cache = [];

    /**
     * Get a cached response by key.
     *
     * @param string $key Cache key.
     *
     * @return ChatResponse|null The cached response, or null if not found or expired.
     */
    public function get(string $key): ?ChatResponse
    {
        if (!isset($this->cache[$key])) {
            return null;
        }

        $entry = $this->cache[$key];

        // Check if expired (0 means no expiration)
        if ($entry['expires_at'] !== 0 && $entry['expires_at'] < time()) {
            unset($this->cache[$key]);
            return null;
        }

        return $entry['response'];
    }

    /**
     * Store a response in the cache.
     *
     * @param string       $key      Cache key.
     * @param ChatResponse $response The response to cache.
     * @param int          $ttl      Time-to-live in seconds (0 = forever).
     *
     * @return bool True (always succeeds for memory cache).
     */
    public function set(string $key, ChatResponse $response, int $ttl = 3600): bool
    {
        $expiresAt = $ttl > 0 ? time() + $ttl : 0;

        $this->cache[$key] = [
            'response' => $response,
            'expires_at' => $expiresAt,
        ];

        return true;
    }

    /**
     * Check if a key exists in the cache.
     *
     * @param string $key Cache key.
     *
     * @return bool True if the key exists and is not expired.
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Delete a cached response.
     *
     * @param string $key Cache key.
     *
     * @return bool True if deleted, false if not found.
     */
    public function delete(string $key): bool
    {
        if (!isset($this->cache[$key])) {
            return false;
        }

        unset($this->cache[$key]);
        return true;
    }

    /**
     * Clear all cached responses.
     *
     * @return bool True (always succeeds).
     */
    public function clear(): bool
    {
        $this->cache = [];
        return true;
    }

    /**
     * Generate a cache key from input and context.
     *
     * Creates a deterministic hash based on input and cache-relevant context.
     * Includes: model, prompt, temperature, max_tokens, and any custom cache keys.
     *
     * @param string               $input   User input message.
     * @param array<string, mixed> $context Context parameters.
     *
     * @return string The generated cache key (SHA-256 hash).
     */
    public function generateKey(string $input, array $context = []): string
    {
        // Build array of cache-relevant parameters
        $cacheData = [
            'input' => $input,
            'model' => $context['model'] ?? 'default',
            'prompt' => $context['prompt'] ?? '',
            'temperature' => $context['temperature'] ?? 0.7,
            'max_tokens' => $context['max_tokens'] ?? 256,
        ];

        // Allow custom cache key components
        if (isset($context['cache_key_components']) && is_array($context['cache_key_components'])) {
            $cacheData = array_merge($cacheData, $context['cache_key_components']);
        }

        // Create deterministic hash
        return 'chatbot:' . hash('sha256', json_encode($cacheData, JSON_THROW_ON_ERROR));
    }

    /**
     * Get cache statistics (for debugging).
     *
     * @return array{count: int, expired: int, valid: int}
     * @internal This method is for debugging and testing only.
     */
    public function getStats(): array
    {
        $now = time();
        $expired = 0;
        $valid = 0;

        foreach ($this->cache as $entry) {
            if ($entry['expires_at'] !== 0 && $entry['expires_at'] < $now) {
                $expired++;
            } else {
                $valid++;
            }
        }

        return [
            'count' => count($this->cache),
            'expired' => $expired,
            'valid' => $valid,
        ];
    }
}
