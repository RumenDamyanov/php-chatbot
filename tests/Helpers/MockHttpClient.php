<?php

namespace Tests\Helpers;

use Rumenx\PhpChatbot\Support\HttpClientInterface;

/**
 * Mock HTTP Client for testing streaming functionality.
 * 
 * Simulates SSE streaming responses without actual network calls.
 */
class MockHttpClient implements HttpClientInterface
{
    /** @var string|null Mock response data to return */
    private ?string $mockResponse = null;

    /** @var bool Whether to simulate an error */
    private bool $shouldFail = false;

    /** @var string Error message to throw */
    private string $errorMessage = 'Mock HTTP error';

    /**
     * Set mock response data (SSE format for streaming).
     * 
     * @param string $response The mock response data
     * @return self
     */
    public function setMockResponse(string $response): self
    {
        $this->mockResponse = $response;
        return $this;
    }

    /**
     * Configure the mock to simulate a failure.
     * 
     * @param string $message Error message
     * @return self
     */
    public function setFailure(string $message = 'Mock HTTP error'): self
    {
        $this->shouldFail = true;
        $this->errorMessage = $message;
        return $this;
    }

    /**
     * Reset the mock to success state.
     * 
     * @return self
     */
    public function reset(): self
    {
        $this->shouldFail = false;
        $this->mockResponse = null;
        $this->errorMessage = 'Mock HTTP error';
        return $this;
    }

    /**
     * Execute a mock HTTP POST request with streaming support.
     * 
     * @param string $url The URL to request (ignored in mock)
     * @param array<string, mixed> $headers HTTP headers (ignored in mock)
     * @param string $body The request body (ignored in mock)
     * @param callable|null $streamCallback Optional callback for streaming responses
     * @return string The response body
     * @throws \RuntimeException If configured to fail
     */
    public function post(string $url, array $headers, string $body, ?callable $streamCallback = null): string
    {
        if ($this->shouldFail) {
            throw new \RuntimeException($this->errorMessage);
        }

        $response = $this->mockResponse ?? '';

        // If streaming callback provided, simulate chunked delivery
        if ($streamCallback !== null && $response !== '') {
            // Simulate SSE streaming by sending the response through the callback
            $streamCallback(null, $response);
            return '';
        }

        return $response;
    }
}
