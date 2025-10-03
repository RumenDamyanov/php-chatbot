<?php

namespace Rumenx\PhpChatbot\Support;

/**
 * cURL-based HTTP Client implementation.
 *
 * Provides HTTP functionality using PHP's cURL extension.
 */
class CurlHttpClient implements HttpClientInterface
{
    /**
     * Execute an HTTP POST request with streaming support.
     *
     * @param string $url The URL to request
     * @param array<string, mixed> $headers HTTP headers as key-value pairs
     * @param string $body The request body (typically JSON)
     * @param callable|null $streamCallback Optional callback for streaming responses
     * @return string The response body (empty string if using streaming callback)
     * @throws \RuntimeException If the request fails
     */
    public function post(string $url, array $headers, string $body, ?callable $streamCallback = null): string
    {
        $ch = curl_init($url);

        if ($ch === false) {
            throw new \RuntimeException('Failed to initialize cURL');
        }

        // Convert headers to cURL format
        $curlHeaders = [];
        foreach ($headers as $key => $value) {
            $curlHeaders[] = $key . ': ' . $value;
        }

        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Handle streaming with callback
        if ($streamCallback !== null) {
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, $streamCallback);
        }

        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('cURL request failed: ' . $error);
        }

        curl_close($ch);

        return $streamCallback !== null ? '' : (string) $result;
    }
}
