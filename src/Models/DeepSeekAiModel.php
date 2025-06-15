<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

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
     * @return string The response from DeepSeek.
     */
    public function getResponse(string $input, array $context = []): string
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
            $data = [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $input],
                ],
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
                return json_encode(
                    [
                        'status' => 'error',
                        'message' => '[DeepSeek] Error: ' . $error,
                    ]
                ) ?: '{"status":"error","message":"[DeepSeek] JSON encode failed."}';
            }
            $response = json_decode(is_string($result) ? $result : '', true);
            curl_close($ch);
            if (
                is_array($response)
                && isset($response['choices'][0]['message']['content'])
                && is_string($response['choices'][0]['message']['content'])
            ) {
                return $response['choices'][0]['message']['content'];
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
            return json_encode(
                [
                    'status' => 'error',
                    'message' => '[DeepSeek] No response.',
                ]
            ) ?: '{"status":"error","message":"[DeepSeek] JSON encode failed."}';
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
            return json_encode(
                [
                    'status' => 'error',
                    'message' => '[DeepSeek] Exception: ' . $e->getMessage(),
                ]
            ) ?: '{"status":"error","message":"[DeepSeek] JSON encode failed."}';
        }
    }
}
