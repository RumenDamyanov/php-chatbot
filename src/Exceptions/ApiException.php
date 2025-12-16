<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Exceptions;

/**
 * Exception thrown when an AI API request fails.
 *
 * This exception is thrown when:
 * - API returns an error response (4xx, 5xx status codes)
 * - API returns invalid or malformed JSON
 * - API authentication fails
 * - Rate limits are exceeded
 * - API service is unavailable
 *
 * @package Rumenx\PhpChatbot\Exceptions
 */
class ApiException extends PhpChatbotException
{
    /**
     * HTTP status code from the API response.
     *
     * @var int|null
     */
    private ?int $statusCode;

    /**
     * Response body from the API.
     *
     * @var string|null
     */
    private ?string $responseBody;

    /**
     * Create a new API exception.
     *
     * @param string          $message      The exception message.
     * @param int|null        $statusCode   HTTP status code (if available).
     * @param string|null     $responseBody API response body (if available).
     * @param \Throwable|null $previous     Previous exception for chaining.
     */
    public function __construct(
        string $message,
        ?int $statusCode = null,
        ?string $responseBody = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int|null The HTTP status code, or null if not available.
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * Get the API response body.
     *
     * @return string|null The response body, or null if not available.
     */
    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    /**
     * Check if this is a rate limit error.
     *
     * @return bool True if status code is 429.
     */
    public function isRateLimitError(): bool
    {
        return $this->statusCode === 429;
    }

    /**
     * Check if this is an authentication error.
     *
     * @return bool True if status code is 401 or 403.
     */
    public function isAuthenticationError(): bool
    {
        return in_array($this->statusCode, [401, 403], true);
    }

    /**
     * Check if this is a server error.
     *
     * @return bool True if status code is 5xx.
     */
    public function isServerError(): bool
    {
        return $this->statusCode !== null && $this->statusCode >= 500 && $this->statusCode < 600;
    }
}

