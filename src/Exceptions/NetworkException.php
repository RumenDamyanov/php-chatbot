<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Exceptions;

/**
 * Exception thrown when a network error occurs.
 *
 * This exception is thrown when:
 * - cURL request fails (connection timeout, DNS resolution failure, etc.)
 * - Network is unreachable
 * - SSL/TLS errors occur
 * - Connection is refused or reset
 *
 * @package Rumenx\PhpChatbot\Exceptions
 */
class NetworkException extends PhpChatbotException
{
    /**
     * cURL error code.
     *
     * @var int|null
     */
    private ?int $curlErrorCode;

    /**
     * cURL error message.
     *
     * @var string|null
     */
    private ?string $curlError;

    /**
     * Create a new network exception.
     *
     * @param string          $message       The exception message.
     * @param int|null        $curlErrorCode cURL error code (if available).
     * @param string|null     $curlError     cURL error message (if available).
     * @param \Throwable|null $previous      Previous exception for chaining.
     */
    public function __construct(
        string $message,
        ?int $curlErrorCode = null,
        ?string $curlError = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->curlErrorCode = $curlErrorCode;
        $this->curlError = $curlError;
    }

    /**
     * Get the cURL error code.
     *
     * @return int|null The cURL error code, or null if not available.
     */
    public function getCurlErrorCode(): ?int
    {
        return $this->curlErrorCode;
    }

    /**
     * Get the cURL error message.
     *
     * @return string|null The cURL error message, or null if not available.
     */
    public function getCurlError(): ?string
    {
        return $this->curlError;
    }

    /**
     * Check if this is a timeout error.
     *
     * @return bool True if this is a timeout error.
     */
    public function isTimeout(): bool
    {
        return $this->curlErrorCode === CURLE_OPERATION_TIMEDOUT;
    }

    /**
     * Check if this is a connection error.
     *
     * @return bool True if this is a connection error.
     */
    public function isConnectionError(): bool
    {
        return in_array($this->curlErrorCode, [
            CURLE_COULDNT_CONNECT,
            CURLE_COULDNT_RESOLVE_HOST,
            CURLE_COULDNT_RESOLVE_PROXY,
        ], true);
    }
}

