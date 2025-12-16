<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\RateLimiting;

use Rumenx\PhpChatbot\Exceptions\PhpChatbotException;

/**
 * Exception thrown when rate limit is exceeded.
 *
 * This exception includes information about when the rate limit will reset
 * and how many requests are allowed.
 *
 * @package Rumenx\PhpChatbot\RateLimiting
 */
class RateLimitException extends PhpChatbotException
{
    /**
     * Create a new rate limit exception.
     *
     * @param string          $key          The rate limit key that was exceeded.
     * @param int             $maxRequests  Maximum requests allowed.
     * @param int             $windowSeconds Time window in seconds.
     * @param int             $resetIn      Seconds until the rate limit resets.
     * @param \Throwable|null $previous     Previous exception for chaining.
     */
    public function __construct(
        private readonly string $key,
        private readonly int $maxRequests,
        private readonly int $windowSeconds,
        private readonly int $resetIn,
        ?\Throwable $previous = null
    ) {
        $message = sprintf(
            'Rate limit exceeded for "%s": %d requests per %d seconds. Resets in %d seconds.',
            $key,
            $maxRequests,
            $windowSeconds,
            $resetIn
        );

        parent::__construct($message, 429, $previous);
    }

    /**
     * Get the rate limit key.
     *
     * @return string The key that exceeded the rate limit.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the maximum requests allowed.
     *
     * @return int Maximum requests per window.
     */
    public function getMaxRequests(): int
    {
        return $this->maxRequests;
    }

    /**
     * Get the time window in seconds.
     *
     * @return int Window duration in seconds.
     */
    public function getWindowSeconds(): int
    {
        return $this->windowSeconds;
    }

    /**
     * Get seconds until the rate limit resets.
     *
     * @return int Seconds until reset.
     */
    public function getResetIn(): int
    {
        return $this->resetIn;
    }

    /**
     * Get the timestamp when the rate limit will reset.
     *
     * @return int Unix timestamp of reset time.
     */
    public function getResetAt(): int
    {
        return time() + $this->resetIn;
    }
}

