<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\StreamableModelInterface;
use Rumenx\PhpChatbot\Support\HttpClientInterface;
use Rumenx\PhpChatbot\Support\CurlHttpClient;
use Rumenx\PhpChatbot\Support\ChatResponse;
use Rumenx\PhpChatbot\Exceptions\NetworkException;
use Rumenx\PhpChatbot\Exceptions\ApiException;

/**
 * Gemini Model implementation for the php-chatbot package.
 *
 * This class provides integration with the Gemini API for generating chatbot
 * responses.
 *
 * @category Models
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT License (https://opensource.org/licenses/MIT)
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class GeminiModel implements StreamableModelInterface
{
    /**
     * The API key for Gemini.
     *
     * @var string
     */
    protected string $apiKey;
    /**
     * The model name.
     *
     * @var string
     */
    protected string $model;
    /**
     * The API endpoint.
     *
     * @var string
     */
    protected string $endpoint;

    /**
     * HTTP client for making requests.
     *
     * @var HttpClientInterface
     */
    protected HttpClientInterface $httpClient;

    /**
     * GeminiModel constructor.
     *
     * @param string $apiKey   The API key for Gemini.
     * @param string $model    The model name (default: gemini-1.5-flash).
     * @param string $endpoint The API endpoint.
     * @param HttpClientInterface|null $httpClient Optional HTTP client (for testing).
     */
    public function __construct(
        string $apiKey,
        string $model = 'gemini-1.5-flash',
        string $endpoint = 'https://api.gemini.com/v1/chat',
        ?HttpClientInterface $httpClient = null
    ) {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->endpoint = $endpoint;
        $this->httpClient = $httpClient ?? new CurlHttpClient();
    }

    /**
     * Set the model name.
     *
     * @param string $model The model name.
     *
     * @return void
     */
    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    /**
     * Get the model name.
     *
     * @return string The model name.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Get a response from the Gemini model.
     *
     * @param string               $input   The user input.
     * @param array<string, mixed> $context Optional context for the request.
     *
     * @return ChatResponse The response from Gemini.
     */
    public function getResponse(string $input, array $context = []): ChatResponse
    {
        try {
            $systemPrompt = isset($context['prompt'])
                && is_string($context['prompt'])
                ? $context['prompt']
                : 'You are a helpful chatbot.';

            // Build contents array with conversation history (Gemini format)
            $contents = [];

            // Build the conversation: system prompt + history + current message
            $conversationText = $systemPrompt . "\n\n";

            // Add conversation history if provided
            if (!empty($context['messages']) && is_array($context['messages'])) {
                foreach ($context['messages'] as $msg) {
                    if (
                        is_array($msg) &&
                        isset($msg['role'], $msg['content']) &&
                        is_string($msg['role']) &&
                        is_string($msg['content'])
                    ) {
                        $conversationText .= ucfirst($msg['role']) . ": " . $msg['content'] . "\n\n";
                    }
                }
            }

            // Add current user message
            $conversationText .= "User: " . $input;

            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $conversationText]  // Now includes conversation history!
                ]
            ];

            $data = [
                'contents' => $contents
            ];
            $url = rtrim($this->endpoint, '/')
                . '/' . $this->model
                . ':generateContent?key=' . $this->apiKey;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiKey,
                ]
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            
            // Disable SSL verification in test mode (macOS SIP certificate issue workaround)
            if (getenv('PHP_CHATBOT_TEST_MODE') === '1') {
                /** @phpstan-ignore-next-line */
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                /** @phpstan-ignore-next-line */
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            }
            
            $result = curl_exec($ch);
            if ($result === false) {
                $error = curl_error($ch);
                curl_close($ch);
                return ChatResponse::fromString('[Google Gemini] Error: ' . $error, $this->model);
            }
            $response = json_decode(is_string($result) ? $result : '', true);
            curl_close($ch);
            if (
                is_array($response)
                && isset($response['candidates'][0]['content']['parts'][0]['text'])
                && is_string(
                    $response['candidates'][0]['content']['parts'][0]['text']
                )
            ) {
                $content = $response['candidates'][0]['content']['parts'][0]['text'];
                return ChatResponse::fromGemini($content, $response, $this->model);
            }
            // Fallback for missing candidates/content
            return ChatResponse::fromString('[Google Gemini] No response.', $this->model);
        } catch (\Throwable $e) {
            return ChatResponse::fromString('[Google Gemini] Exception: ' . $e->getMessage(), $this->model);
        }
    }

    /**
     * Send a message to the Gemini model (placeholder).
     *
     * @param string               $message The message to send.
     * @param array<string, mixed> $context Optional context for the request.
     *
     * @return string The response from the Gemini model.
     */
    public function sendMessage(string $message, array $context = []): string
    {
        return '[Google Gemini/'
            . $this->model
            . '] This is a placeholder response.';
    }

    /**
     * Get a streaming response from the Gemini model.
     *
     * This method returns a Generator that yields response chunks as they
     * become available. Due to PHP Generator limitations with cURL callbacks,
     * chunks are collected during the HTTP transfer and yielded afterward.
     *
     * @param string               $input   The user input message.
     * @param array<string, mixed> $context Optional context for the request.
     *
     * @return \Generator<int, string> Generator yielding response chunks.
     */
    public function getStreamingResponse(string $input, array $context = []): \Generator
    {
        $systemPrompt = isset($context['prompt'])
            && is_string($context['prompt'])
            ? $context['prompt']
            : 'You are a helpful chatbot.';

        $data = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $systemPrompt . "\n" . $input
                        ]
                    ]
                ]
            ]
        ];

        $url = rtrim($this->endpoint, '/')
            . '/' . $this->model
            . ':streamGenerateContent?key=' . $this->apiKey;

        $streamBuffer = new \Rumenx\PhpChatbot\Support\StreamBuffer();
        $chunks = [];

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];

        $streamCallback = function ($ch, $chunk) use ($streamBuffer, &$chunks) {
            $streamBuffer->add($chunk);

            // Collect chunks as they become available
            while ($streamBuffer->hasChunks()) {
                $content = $streamBuffer->getChunk();
                if ($content !== null) {
                    $chunks[] = $content;
                }
            }

            return strlen($chunk);
        };

        try {
            $this->httpClient->post(
                $url,
                $headers,
                json_encode($data),
                $streamCallback
            );
        } catch (\RuntimeException $e) {
            yield '[Google Gemini Streaming] Error: ' . $e->getMessage();
            return;
        }

        // Yield collected chunks
        foreach ($chunks as $chunk) {
            yield $chunk;
        }
    }

    /**
     * Check if Gemini provider supports streaming.
     *
     * Google Gemini API supports streaming via streamGenerateContent endpoint.
     *
     * @return bool Always returns true for Gemini.
     */
    public function supportsStreaming(): bool
    {
        return true;
    }
}
