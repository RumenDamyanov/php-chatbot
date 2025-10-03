<?php

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

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

class AnthropicModel implements AiModelInterface
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
     * AnthropicModel constructor.
     *
     * @param string $apiKey   Anthropic API key
     * @param string $model    Model name (default: claude-3-5-sonnet-20241022)
     * @param string $endpoint API endpoint URL
     */
    public function __construct(
        string $apiKey,
        string $model = 'claude-3-5-sonnet-20241022',
        string $endpoint = 'https://api.anthropic.com/v1/messages'
    ) {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->endpoint = $endpoint;
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
     * @return string The response from Claude.
     */
    public function getResponse(string $input, array $context = []): string
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
                    'x-api-key: ' . $this->apiKey,
                ]
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $result = curl_exec($ch);
            if ($result === false) {
                $error = curl_error($ch);
                curl_close($ch);
                return '[Anthropic] Error: ' . $error;
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
            return '[Anthropic] No response.';
        } catch (\Throwable $e) {
            return '[Anthropic] Exception: ' . $e->getMessage();
        }
    }
}
