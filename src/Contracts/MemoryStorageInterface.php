<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Contracts;

/**
 * Interface MemoryStorageInterface
 *
 * Defines the contract for conversation memory storage backends.
 * Implementations can use files, Redis, databases, or other storage mechanisms.
 *
 * @category AI
 * @package  Rumenx\PhpChatbot\Contracts
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
interface MemoryStorageInterface
{
    /**
     * Store conversation history for a given session.
     *
     * @param string               $sessionId The unique session identifier.
     * @param array<string, mixed> $data      The conversation data to store.
     *
     * @return bool True on success, false on failure.
     */
    public function store(string $sessionId, array $data): bool;

    /**
     * Retrieve conversation history for a given session.
     *
     * @param string $sessionId The unique session identifier.
     *
     * @return array<string, mixed>|null The conversation data, or null if not found.
     */
    public function retrieve(string $sessionId): ?array;

    /**
     * Delete conversation history for a given session.
     *
     * @param string $sessionId The unique session identifier.
     *
     * @return bool True on success, false on failure.
     */
    public function delete(string $sessionId): bool;

    /**
     * Clear all conversation histories.
     *
     * @return bool True on success, false on failure.
     */
    public function clear(): bool;

    /**
     * Check if conversation history exists for a given session.
     *
     * @param string $sessionId The unique session identifier.
     *
     * @return bool True if exists, false otherwise.
     */
    public function exists(string $sessionId): bool;
}
