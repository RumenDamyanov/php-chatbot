<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Support;

use Rumenx\PhpChatbot\Contracts\MemoryStorageInterface;

/**
 * Class ConversationMemory
 *
 * Manages conversation history for chatbot sessions. Handles message storage,
 * retrieval, history trimming, and session management through pluggable storage backends.
 *
 * @category AI
 * @package  Rumenx\PhpChatbot\Support
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class ConversationMemory
{
    /**
     * The storage backend implementation.
     *
     * @var MemoryStorageInterface
     */
    protected $storage;

    /**
     * Maximum number of messages to keep in history per session.
     *
     * @var int
     */
    protected $maxHistory;

    /**
     * Whether memory is enabled.
     *
     * @var bool
     */
    protected $enabled;

    /**
     * Constructor for ConversationMemory.
     *
     * @param MemoryStorageInterface $storage    The storage backend.
     * @param int                    $maxHistory Maximum messages to keep (0 = unlimited).
     * @param bool                   $enabled    Whether memory is enabled.
     */
    public function __construct(
        MemoryStorageInterface $storage,
        int $maxHistory = 20,
        bool $enabled = true
    ) {
        $this->storage = $storage;
        $this->maxHistory = $maxHistory;
        $this->enabled = $enabled;
    }

    /**
     * Add a message to the conversation history.
     *
     * @param string $sessionId The session identifier.
     * @param string $role      The message role ('user' or 'assistant').
     * @param string $content   The message content.
     *
     * @return bool True on success, false on failure.
     */
    public function addMessage(string $sessionId, string $role, string $content): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $history = $this->getHistory($sessionId);

        $history[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => time()
        ];

        // Trim history if max limit is set and exceeded
        if ($this->maxHistory > 0 && count($history) > $this->maxHistory) {
            $history = array_slice($history, -$this->maxHistory);
        }

        return $this->storage->store($sessionId, [
            'messages' => $history,
            'updated_at' => time()
        ]);
    }

    /**
     * Get conversation history for a session.
     *
     * @param string $sessionId The session identifier.
     *
     * @return array<int, array<string, mixed>> Array of messages.
     */
    public function getHistory(string $sessionId): array
    {
        if (!$this->enabled) {
            return [];
        }

        $data = $this->storage->retrieve($sessionId);

        if ($data === null || !isset($data['messages'])) {
            return [];
        }

        return $data['messages'];
    }

    /**
     * Clear conversation history for a session.
     *
     * @param string $sessionId The session identifier.
     *
     * @return bool True on success, false on failure.
     */
    public function clearHistory(string $sessionId): bool
    {
        return $this->storage->delete($sessionId);
    }

    /**
     * Clear all conversation histories.
     *
     * @return bool True on success, false on failure.
     */
    public function clearAll(): bool
    {
        return $this->storage->clear();
    }

    /**
     * Check if history exists for a session.
     *
     * @param string $sessionId The session identifier.
     *
     * @return bool True if exists, false otherwise.
     */
    public function hasHistory(string $sessionId): bool
    {
        return $this->storage->exists($sessionId);
    }

    /**
     * Get formatted history for AI model context.
     *
     * Returns messages in a format suitable for passing to AI models.
     *
     * @param string $sessionId The session identifier.
     *
     * @return array<int, array<string, string>> Array of messages with role and content.
     */
    public function getFormattedHistory(string $sessionId): array
    {
        $history = $this->getHistory($sessionId);

        return array_map(function ($message) {
            return [
                'role' => $message['role'],
                'content' => $message['content']
            ];
        }, $history);
    }

    /**
     * Get the number of messages in a session's history.
     *
     * @param string $sessionId The session identifier.
     *
     * @return int Number of messages.
     */
    public function getMessageCount(string $sessionId): int
    {
        return count($this->getHistory($sessionId));
    }

    /**
     * Enable or disable memory.
     *
     * @param bool $enabled Whether memory should be enabled.
     *
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * Check if memory is enabled.
     *
     * @return bool True if enabled, false otherwise.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Set the maximum history size.
     *
     * @param int $maxHistory Maximum messages to keep (0 = unlimited).
     *
     * @return void
     */
    public function setMaxHistory(int $maxHistory): void
    {
        $this->maxHistory = $maxHistory;
    }

    /**
     * Get the maximum history size.
     *
     * @return int Maximum messages to keep.
     */
    public function getMaxHistory(): int
    {
        return $this->maxHistory;
    }

    /**
     * Get the storage backend.
     *
     * @return MemoryStorageInterface
     */
    public function getStorage(): MemoryStorageInterface
    {
        return $this->storage;
    }
}
