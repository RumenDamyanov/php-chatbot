<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;
use Rumenx\PhpChatbot\Support\ChatResponse;
use Rumenx\PhpChatbot\Exceptions\NetworkException;
use Rumenx\PhpChatbot\Exceptions\ApiException;

/**
 * DeepSeek AI Model implementation for the PHP Chatbot package.
 *
 * This class provides integration with the DeepSeek AI API for
 * generating chatbot responses.
 *
 * @category Models
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT License (https://opensource.org/licenses/MIT)
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class DeepSeekAiModel implements AiModelInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $endpoint;

    /**
     * DeepSeekAiModel constructor.
     *
     * @param string $apiKey   The DeepSeek API key.
     * @param string $model    The model name (default: 'deepseek-chat').
     * @param string $endpoint The API endpoint URL.
     */
    public function __construct(
        string $apiKey,
        string $model = 'deepseek-chat',
        string $endpoint = 'https://api.deepseek.com/v1/chat/completions'
    ) {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->endpoint = $endpoint;
    }

    /**
     * Set the DeepSeek model name.
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
     * Get the current DeepSeek model name.
     *
     * @return string The model name.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Get a response from the DeepSeek AI model.
     *
     * @param string               $input   The user input.
     * @param array<string, mixed> $context Optional context for the request.
     *
     * @return ChatResponse The response from DeepSeek.
     */
    public function getResponse(string $input, array $context = []): ChatResponse
    {
        try {
            $systemPrompt = 'You are a helpful chatbot.';
            if (isset($context['prompt']) && is_string($context['prompt'])) {
                $prompt = $context['prompt'];
                $systemPrompt = $prompt;
            }
            $temperature = 0.7;
            if (
                isset($context['temperature'])
                && is_numeric($context['temperature'])
            ) {
                $temp = $context['temperature'];
                $temperature = (float) $temp;
            }
            $maxTokens = 256;
            if (
                isset($context['max_tokens'])
                && is_numeric($context['max_tokens'])
            ) {
                $tokens = $context['max_tokens'];
                $maxTokens = (int) $tokens;
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
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
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
                if (
                    isset($context['logger'])
                    && $context['logger'] instanceof \Psr\Log\LoggerInterface
                ) {
                    $context['logger']->error(
                        'DeepSeekAiModel cURL error: ' . $error,
                        ['data' => $data]
                    );
                }
                curl_close($ch);
                return ChatResponse::fromString('[DeepSeek] Error: ' . $error, $this->model);
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
                    'DeepSeekAiModel API error: No response',
                    ['response' => $response]
                );
            }
            return ChatResponse::fromString('[DeepSeek] No response.', $this->model);
        } catch (\Throwable $e) {
            if (
                isset($context['logger'])
                && $context['logger'] instanceof \Psr\Log\LoggerInterface
            ) {
                $context['logger']->error(
                    'DeepSeekAiModel exception: ' . $e->getMessage(),
                    ['exception' => $e]
                );
            }
            return ChatResponse::fromString('[DeepSeek] Exception: ' . $e->getMessage(), $this->model);
        }
    }
}
