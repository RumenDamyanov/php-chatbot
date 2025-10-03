<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;
use Rumenx\PhpChatbot\Contracts\StreamableModelInterface;
use Rumenx\PhpChatbot\Support\HttpClientInterface;
use Rumenx\PhpChatbot\Support\CurlHttpClient;
use Rumenx\PhpChatbot\Support\ChatResponse;

/**
 * OpenAI Model implementation for the php-chatbot package.
 *
 * This class provides integration with the OpenAI API for generating chatbot
 * responses with support for both standard and streaming responses.
 *
 * @category Models
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT License (https://opensource.org/licenses/MIT)
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class OpenAiModel implements StreamableModelInterface
{
    protected string $apiKey;
    protected string $endpoint;
    protected string $model;
    protected HttpClientInterface $httpClient;

    /**
     * OpenAiModel constructor.
     *
     * @param string $apiKey   The OpenAI API key.
     * @param string $model    The model name (default: gpt-4o-mini).
     * @param string $endpoint The API endpoint.
     * @param HttpClientInterface|null $httpClient Optional HTTP client (for testing).
     */
    public function __construct(
        string $apiKey,
        string $model = 'gpt-4o-mini',
        string $endpoint = 'https://api.openai.com/v1/chat/completions',
        ?HttpClientInterface $httpClient = null
    ) {
        $this->apiKey = $apiKey;
        $this->endpoint = $endpoint;
        $this->model = $model;
        $this->httpClient = $httpClient ?? new CurlHttpClient();
    }

    /**
     * Set the OpenAI model name.
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
     * Get the current OpenAI model name.
     *
     * @return string The model name.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Get a response from the OpenAI model.
     *
     * @param string               $input   The user input.
     * @param array<string, mixed> $context Optional context for the request.
     *
     * @return ChatResponse The response from OpenAI.
     */
    public function getResponse(string $input, array $context = []): ChatResponse
    {
        try {
            $systemPrompt = 'You are a helpful chatbot.';
            if (isset($context['prompt']) && is_string($context['prompt'])) {
                $prompt = $context['prompt'];
                $systemPrompt = $prompt;
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
            $data = [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $input],
                ],
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
                    'Authorization: Bearer ' . $this->apiKey,
                ]
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $result = curl_exec($ch);
            if ($result === false) {
                $error = curl_error($ch);
                if (
                    isset($context['logger'])
                    && $context['logger'] instanceof \Psr\Log\LoggerInterface
                ) {
                    $context['logger']->error(
                        'OpenAiModel cURL error: ' . $error,
                        ['data' => $data]
                    );
                }
                curl_close($ch);
                return ChatResponse::fromString('[OpenAI] Error: ' . $error, $this->model);
            }
            $response = json_decode(is_string($result) ? $result : '', true);
            curl_close($ch);
            if (
                is_array($response)
                && isset($response['choices'][0]['message']['content'])
                && is_string($response['choices'][0]['message']['content'])
            ) {
                $content = $response['choices'][0]['message']['content'];
                return ChatResponse::fromOpenAI($content, $response);
            }
            if (
                isset($context['logger'])
                && $context['logger'] instanceof \Psr\Log\LoggerInterface
            ) {
                $context['logger']->error(
                    'OpenAiModel API error: No response',
                    ['response' => $response]
                );
            }
            return ChatResponse::fromString('[OpenAI] No response.', $this->model);
        } catch (\Throwable $e) {
            if (
                isset($context['logger'])
                && $context['logger'] instanceof \Psr\Log\LoggerInterface
            ) {
                $context['logger']->error(
                    'OpenAiModel exception: ' . $e->getMessage(),
                    ['exception' => $e]
                );
            }
            return ChatResponse::fromString('[OpenAI] Exception: ' . $e->getMessage(), $this->model);
        }
    }

    /**
     * Get streaming response from OpenAI API.
     *
     * This method streams the response from OpenAI in real-time using
     * Server-Sent Events (SSE). Yields response chunks as they arrive.
     *
     * Note: This implementation collects the full response before yielding
     * due to PHP Generator limitations with cURL callbacks. For true streaming
     * in a web context, consider using JavaScript/frontend streaming.
     *
     * @param string               $input   The user input.
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
                $this->endpoint,
                $headers,
                json_encode($data),
                $streamCallback
            );
        } catch (\RuntimeException $e) {
            yield '[OpenAI Streaming] Error: ' . $e->getMessage();
            return;
        }

        // Yield collected chunks
        foreach ($chunks as $chunk) {
            yield $chunk;
        }
    }

    /**
     * Check if OpenAI provider supports streaming.
     *
     * OpenAI API supports streaming for all chat completion models.
     *
     * @return bool Always returns true for OpenAI.
     */
    public function supportsStreaming(): bool
    {
        return true;
    }
}
