<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Cache;

use Rumenx\PhpChatbot\Support\ChatResponse;

/**
 * File-based response cache.
 *
 * Stores cached responses as serialized files on disk.
 * Suitable for persistent caching in single-server deployments.
 *
 * @package Rumenx\PhpChatbot\Cache
 */
class FileCache implements CacheInterface
{
    /**
     * Directory where cache files are stored.
     *
     * @var string
     */
    private string $cacheDir;

    /**
     * Create a new file cache.
     *
     * @param string $cacheDir Directory for cache files (will be created if it doesn't exist).
     *
     * @throws \RuntimeException If directory cannot be created or is not writable.
     */
    public function __construct(string $cacheDir)
    {
        $this->cacheDir = rtrim($cacheDir, '/\\');

        // Create directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            if (!@mkdir($this->cacheDir, 0755, true) && !is_dir($this->cacheDir)) {
                throw new \RuntimeException("Failed to create cache directory: {$this->cacheDir}");
            }
        }

        // Check if writable
        if (!is_writable($this->cacheDir)) {
            throw new \RuntimeException("Cache directory is not writable: {$this->cacheDir}");
        }
    }

    /**
     * Get a cached response by key.
     *
     * @param string $key Cache key.
     *
     * @return ChatResponse|null The cached response, or null if not found or expired.
     */
    public function get(string $key): ?ChatResponse
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return null;
        }

        $contents = @file_get_contents($filePath);
        if ($contents === false) {
            return null;
        }

        $data = @unserialize($contents);
        if ($data === false || !is_array($data)) {
            // Corrupted cache file, delete it
            @unlink($filePath);
            return null;
        }

        // Check expiration
        if ($data['expires_at'] !== 0 && $data['expires_at'] < time()) {
            @unlink($filePath);
            return null;
        }

        if (!($data['response'] instanceof ChatResponse)) {
            @unlink($filePath);
            return null;
        }

        return $data['response'];
    }

    /**
     * Store a response in the cache.
     *
     * @param string       $key      Cache key.
     * @param ChatResponse $response The response to cache.
     * @param int          $ttl      Time-to-live in seconds (0 = forever).
     *
     * @return bool True if stored successfully, false otherwise.
     */
    public function set(string $key, ChatResponse $response, int $ttl = 3600): bool
    {
        $filePath = $this->getFilePath($key);
        $expiresAt = $ttl > 0 ? time() + $ttl : 0;

        $data = [
            'response' => $response,
            'expires_at' => $expiresAt,
            'created_at' => time(),
        ];

        $serialized = serialize($data);

        // Write atomically using temp file + rename
        $tempFile = $filePath . '.tmp.' . uniqid('', true);
        
        if (@file_put_contents($tempFile, $serialized, LOCK_EX) === false) {
            @unlink($tempFile);
            return false;
        }

        if (!@rename($tempFile, $filePath)) {
            @unlink($tempFile);
            return false;
        }

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
     * @return bool True if deleted successfully, false otherwise.
     */
    public function delete(string $key): bool
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return false;
        }

        return @unlink($filePath);
    }

    /**
     * Clear all cached responses.
     *
     * @return bool True if cleared successfully, false otherwise.
     */
    public function clear(): bool
    {
        $files = glob($this->cacheDir . '/chatbot_*.cache');
        
        if ($files === false) {
            return false;
        }

        $success = true;
        foreach ($files as $file) {
            if (!@unlink($file)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Generate a cache key from input and context.
     *
     * @param string               $input   User input message.
     * @param array<string, mixed> $context Context parameters.
     *
     * @return string The generated cache key.
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

        return 'chatbot:' . hash('sha256', json_encode($cacheData, JSON_THROW_ON_ERROR));
    }

    /**
     * Get the file path for a cache key.
     *
     * @param string $key Cache key.
     *
     * @return string Full file path.
     */
    private function getFilePath(string $key): string
    {
        // Sanitize key for filename
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->cacheDir . '/chatbot_' . $safeKey . '.cache';
    }

    /**
     * Remove expired cache files (garbage collection).
     *
     * @return int Number of files deleted.
     */
    public function gc(): int
    {
        $files = glob($this->cacheDir . '/chatbot_*.cache');
        
        if ($files === false) {
            return 0;
        }

        $deleted = 0;
        $now = time();

        foreach ($files as $file) {
            $contents = @file_get_contents($file);
            if ($contents === false) {
                continue;
            }

            $data = @unserialize($contents);
            if (!is_array($data)) {
                @unlink($file);
                $deleted++;
                continue;
            }

            // Check if expired
            if ($data['expires_at'] !== 0 && $data['expires_at'] < $now) {
                @unlink($file);
                $deleted++;
            }
        }

        return $deleted;
    }
}

