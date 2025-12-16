<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Support;

/**
 * HTTP Client Interface for making HTTP requests.
 *
 * This interface provides a minimal abstraction over HTTP operations
 * to enable testing without actual network calls.
 */
interface HttpClientInterface
{
    /**
     * Execute an HTTP POST request with streaming support.
     *
     * @param string $url The URL to request
     * @param array<string, mixed> $headers HTTP headers as key-value pairs
     * @param string $body The request body (typically JSON)
     * @param callable|null $streamCallback Optional callback for streaming responses.
     *                                      Signature: function(string $chunk): int
     *                                      Must return the length of the processed chunk.
     * @return string The response body (empty string if using streaming callback)
     * @throws \RuntimeException If the request fails
     */
    public function post(string $url, array $headers, string $body, ?callable $streamCallback = null): string;
}
