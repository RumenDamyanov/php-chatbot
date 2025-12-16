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
 * Meta Model implementation for the php-chatbot package.
 *
 * This class provides integration with the Meta API for generating chatbot
 * responses.
 *
 * @category Models
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT License (https://opensource.org/licenses/MIT)
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class MetaModel implements StreamableModelInterface
{
    /**
     * The API key for Meta.
     *
     * @var string
     */
    protected string $apiKey;
    protected string $model;
    protected string $endpoint;
    protected HttpClientInterface $httpClient;

    /**
     * MetaModel constructor.
     *
     * @param string $apiKey   The API key for Meta.
     * @param string $model    The model name (default: llama-3.3-70b-versatile).
     * @param string $endpoint The API endpoint.
     * @param HttpClientInterface|null $httpClient Optional HTTP client for dependency injection.
     */
    public function __construct(
        string $apiKey,
        string $model = 'llama-3.3-70b-versatile',
        string $endpoint = 'https://api.meta.com/v1/chat',
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
     * Get a response from the Meta model.
     *
     * @param string               $input   The user input.
     * @param array<string, mixed> $context Optional context for the request.
     *
     * @return ChatResponse The response from Meta.
     */
    public function getResponse(string $input, array $context = []): ChatResponse
    {
        try {
            $systemPrompt = 'You are a helpful chatbot.';
            if (
                isset($context['prompt'])
                && is_string($context['prompt'])
            ) {
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
                $errorCode = curl_errno($ch);
                if (
                    isset($context['logger'])
                    && $context['logger'] instanceof \Psr\Log\LoggerInterface
                ) {
                    $context['logger']->error(
                        'MetaModel cURL error: ' . $error,
                        ['data' => $data, 'curl_error_code' => $errorCode]
                    );
                }
                curl_close($ch);

                // Throw NetworkException for cURL errors
                throw new NetworkException(
                    '[Meta] Network error: ' . $error,
                    $errorCode,
                    $error
                );
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $response = json_decode(is_string($result) ? $result : '', true);

            // Check for successful response
            if (
                is_array($response)
                && isset($response['choices'][0]['message']['content'])
                && is_string($response['choices'][0]['message']['content'])
            ) {
                $content = $response['choices'][0]['message']['content'];
                return ChatResponse::fromOpenAI($content, $response);
            }

            // Handle API errors
            if (
                isset($context['logger'])
                && $context['logger'] instanceof \Psr\Log\LoggerInterface
            ) {
                $context['logger']->error(
                    'MetaModel API error: No valid response',
                    ['response' => $response, 'http_code' => $httpCode]
                );
            }

            // Throw ApiException for invalid API responses
            throw new ApiException(
                '[Meta] Invalid API response: No content in response',
                $httpCode,
                is_string($result) ? $result : json_encode($response)
            );
        } catch (NetworkException | ApiException $e) {
            // Re-throw our custom exceptions
            throw $e;
        } catch (\Throwable $e) {
            // Wrap unexpected exceptions
            if (
                isset($context['logger'])
                && $context['logger'] instanceof \Psr\Log\LoggerInterface
            ) {
                $context['logger']->error(
                    'MetaModel unexpected exception: ' . $e->getMessage(),
                    ['exception' => get_class($e)]
                );
            }
            throw new ApiException(
                '[Meta] Unexpected error: ' . $e->getMessage(),
                0,
                '',
                $e
            );
        }
    }

    /**
     * Send a message to the Meta model (placeholder).
     *
     * @param string               $message The message to send.
     * @param array<string, mixed> $context Optional context for the request.
     *
     * @return string The response from the Meta model.
     */
    public function sendMessage(string $message, array $context = []): string
    {
        return '[Meta/' . $this->model . '] This is a placeholder response.';
    }

    /**
     * Get a streaming response from the Meta model.
     *
     * This method returns a Generator that yields response chunks as they
     * become available. Due to PHP Generator limitations with cURL callbacks,
     * chunks are collected during the HTTP transfer and yielded afterward.
     *
     * Meta LLaMA models use OpenAI-compatible API format.
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
            yield '[Meta Streaming] Error: ' . $e->getMessage();
            return;
        }

        // Yield collected chunks
        foreach ($chunks as $chunk) {
            yield $chunk;
        }
    }

    /**
     * Check if Meta provider supports streaming.
     *
     * Meta LLaMA models use OpenAI-compatible API and support streaming.
     *
     * @return bool Always returns true for Meta.
     */
    public function supportsStreaming(): bool
    {
        return true;
    }
}
