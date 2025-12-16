<?php

declare(strict_types=1);

/**
 * Anthropic Claude model adapter for php-chatbot.
 *
 * Handles communication with Anthropic's Claude API.
 *
 * @category Model
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\StreamableModelInterface;
use Rumenx\PhpChatbot\Support\HttpClientInterface;
use Rumenx\PhpChatbot\Support\CurlHttpClient;
use Rumenx\PhpChatbot\Support\ChatResponse;
use Rumenx\PhpChatbot\Exceptions\NetworkException;
use Rumenx\PhpChatbot\Exceptions\ApiException;

class AnthropicModel implements StreamableModelInterface
{
    /**
     * Anthropic API key.
     *
     * @var string
     */
    protected string $apiKey;

    /**
     * Model name.
     *
     * @var string
     */
    protected string $model;

    /**
     * API endpoint URL.
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
     * AnthropicModel constructor.
     *
     * @param string $apiKey   Anthropic API key
     * @param string $model    Model name (default: claude-3-5-sonnet-20241022)
     * @param string $endpoint API endpoint URL
     * @param HttpClientInterface|null $httpClient Optional HTTP client (for testing)
     */
    public function __construct(
        string $apiKey,
        string $model = 'claude-3-5-sonnet-20241022',
        string $endpoint = 'https://api.anthropic.com/v1/messages',
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
     * @param string $model The model name to set.
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
     * @return string The current model name.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Get a response from the Anthropic Claude model.
     *
     * @param string               $input   The user input.
     * @param array<string, mixed> $context Optional context for the request.
     *
     * @return ChatResponse The response from Claude.
     */
    public function getResponse(string $input, array $context = []): ChatResponse
    {
        try {
            $systemPrompt = 'You are a helpful chatbot.';
            if (isset($context['prompt']) && is_string($context['prompt'])) {
                $systemPrompt = $context['prompt'];
            }
            $maxTokens = 256;
            if (
                isset($context['max_tokens'])
                && is_numeric($context['max_tokens'])
            ) {
                $tokens = $context['max_tokens'];
                $maxTokens = (int) $tokens;
            }
            $temperature = 0.7;
            if (
                isset($context['temperature'])
                && is_numeric($context['temperature'])
            ) {
                $temp = $context['temperature'];
                $temperature = (float) $temp;
            }
            // Build messages array with conversation history
            $messages = [];

            // 1. Add system prompt
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];

            // 2. Add conversation history if provided
            if (!empty($context['messages']) && is_array($context['messages'])) {
                foreach ($context['messages'] as $msg) {
                    if (
                        is_array($msg) &&
                        isset($msg['role'], $msg['content']) &&
                        is_string($msg['role']) &&
                        is_string($msg['content'])
                    ) {
                        $messages[] = [
                            'role' => $msg['role'],
                            'content' => $msg['content']
                        ];
                    }
                }
            }

            // 3. Add current user message
            $messages[] = ['role' => 'user', 'content' => $input];

            $data = [
                'model' => $this->model,
                'messages' => $messages,  // Now includes conversation history!
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ];
            $ch = curl_init($this->endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [
                    'Content-Type: application/json',
                    'x-api-key: ' . $this->apiKey,
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
                return ChatResponse::fromString('[Anthropic] Error: ' . $error, $this->model);
            }
            $response = json_decode(is_string($result) ? $result : '', true);
            curl_close($ch);
            if (
                is_array($response)
                && isset($response['content'][0]['text'])
                && is_string($response['content'][0]['text'])
            ) {
                $content = $response['content'][0]['text'];
                return ChatResponse::fromAnthropic($content, $response);
            }
            return ChatResponse::fromString('[Anthropic] No response.', $this->model);
        } catch (\Throwable $e) {
            return ChatResponse::fromString('[Anthropic] Exception: ' . $e->getMessage(), $this->model);
        }
    }

    /**
     * Get a streaming response from the Anthropic Claude model.
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
        $systemPrompt = 'You are a helpful chatbot.';
        if (isset($context['prompt']) && is_string($context['prompt'])) {
            $systemPrompt = $context['prompt'];
        }

        $maxTokens = 256;
        if (isset($context['max_tokens']) && is_numeric($context['max_tokens'])) {
            $maxTokens = (int) $context['max_tokens'];
        }

        $temperature = 0.7;
        if (isset($context['temperature']) && is_numeric($context['temperature'])) {
            $temperature = (float) $context['temperature'];
        }

        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $input],
            ],
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
            'stream' => true,
        ];

        $streamBuffer = new \Rumenx\PhpChatbot\Support\StreamBuffer();
        $chunks = [];

        $headers = [
            'Content-Type' => 'application/json',
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
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
                $this->endpoint,
                $headers,
                json_encode($data),
                $streamCallback
            );
        } catch (\RuntimeException $e) {
            yield '[Anthropic Streaming] Error: ' . $e->getMessage();
            return;
        }

        // Yield collected chunks
        foreach ($chunks as $chunk) {
            yield $chunk;
        }
    }

    /**
     * Check if Anthropic provider supports streaming.
     *
     * Anthropic Claude API supports streaming for all models.
     *
     * @return bool Always returns true for Anthropic.
     */
    public function supportsStreaming(): bool
    {
        return true;
    }
}
