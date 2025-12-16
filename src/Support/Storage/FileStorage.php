<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Support\Storage;

use Rumenx\PhpChatbot\Contracts\MemoryStorageInterface;

/**
 * Class FileStorage
 *
 * File-based storage implementation for conversation memory.
 * Stores conversation histories as JSON files with file locking for thread safety.
 *
 * @category AI
 * @package  Rumenx\PhpChatbot\Support\Storage
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class FileStorage implements MemoryStorageInterface
{
    /**
     * Directory path where conversation files are stored.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * File extension for conversation files.
     *
     * @var string
     */
    protected $fileExtension = '.json';

    /**
     * Constructor for FileStorage.
     *
     * @param string $storagePath Directory path for storing conversation files.
     *
     * @throws \RuntimeException If directory cannot be created or is not writable.
     */
    public function __construct(string $storagePath)
    {
        $this->storagePath = rtrim($storagePath, DIRECTORY_SEPARATOR);

        // Create directory if it doesn't exist
        if (!is_dir($this->storagePath)) {
            if (!mkdir($this->storagePath, 0755, true)) {
                throw new \RuntimeException(
                    "Failed to create storage directory: {$this->storagePath}"
                );
            }
        }

        // Check if directory is writable
        if (!is_writable($this->storagePath)) {
            throw new \RuntimeException(
                "Storage directory is not writable: {$this->storagePath}"
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function store(string $sessionId, array $data): bool
    {
        $filePath = $this->getFilePath($sessionId);
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);

        if ($jsonData === false) {
            return false;
        }

        $handle = fopen($filePath, 'w');
        if ($handle === false) {
            return false;
        }

        // Acquire exclusive lock
        if (!flock($handle, LOCK_EX)) {
            fclose($handle);
            return false;
        }

        $result = fwrite($handle, $jsonData) !== false;

        // Release lock and close file
        flock($handle, LOCK_UN);
        fclose($handle);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve(string $sessionId): ?array
    {
        $filePath = $this->getFilePath($sessionId);

        if (!file_exists($filePath)) {
            return null;
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return null;
        }

        // Acquire shared lock for reading
        if (!flock($handle, LOCK_SH)) {
            fclose($handle);
            return null;
        }

        $content = stream_get_contents($handle);

        // Release lock and close file
        flock($handle, LOCK_UN);
        fclose($handle);

        // Check if reading failed or empty
        // @phpstan-ignore-next-line (stream_get_contents can return false despite PHPDoc)
        if (!is_string($content) || $content === '') {
            return null;
        }

        $data = json_decode($content, true);

        return is_array($data) ? $data : null;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $sessionId): bool
    {
        $filePath = $this->getFilePath($sessionId);

        if (!file_exists($filePath)) {
            return true; // Already deleted
        }

        return unlink($filePath);
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        $files = glob($this->storagePath . DIRECTORY_SEPARATOR . '*' . $this->fileExtension);

        if ($files === false) {
            return false;
        }

        $success = true;
        foreach ($files as $file) {
            if (!unlink($file)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $sessionId): bool
    {
        return file_exists($this->getFilePath($sessionId));
    }

    /**
     * Get the file path for a session.
     *
     * @param string $sessionId The session identifier.
     *
     * @return string The full file path.
     */
    protected function getFilePath(string $sessionId): string
    {
        // Sanitize session ID to prevent directory traversal
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', $sessionId);

        return $this->storagePath . DIRECTORY_SEPARATOR . $sanitized . $this->fileExtension;
    }

    /**
     * Get the storage path.
     *
     * @return string
     */
    public function getStoragePath(): string
    {
        return $this->storagePath;
    }
}
