<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

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
class MetaModel implements AiModelInterface
{
    /**
     * The API key for Meta.
     *
     * @var string
     */
    protected string $apiKey;
    protected string $model;
    protected string $endpoint;

    /**
     * MetaModel constructor.
     *
     * @param string $apiKey   The API key for Meta.
     * @param string $model    The model name.
     * @param string $endpoint The API endpoint.
     */
    public function __construct(
        string $apiKey,
        string $model = 'meta-default',
        string $endpoint = 'https://api.meta.com/v1/chat'
    ) {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->endpoint = $endpoint;
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
     * @return string The response from Meta.
     */
    public function getResponse(string $input, array $context = []): string
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
                curl_close($ch);
                return '[Meta] Error: ' . $error;
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
            return '[Meta] No response.';
        } catch (\Throwable $e) {
            return '[Meta] Exception: ' . $e->getMessage();
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
}
