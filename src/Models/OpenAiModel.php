<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

/**
 * OpenAI Model implementation for the php-chatbot package.
 *
 * This class provides integration with the OpenAI API for generating chatbot
 * responses.
 *
 * @category Models
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT License (https://opensource.org/licenses/MIT)
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class OpenAiModel implements AiModelInterface
{
    protected string $apiKey;
    protected string $endpoint;
    protected string $model;

    /**
     * OpenAiModel constructor.
     *
     * @param string $apiKey   The OpenAI API key.
     * @param string $model    The model name (default: gpt-4o-mini).
     * @param string $endpoint The API endpoint.
     */
    public function __construct(
        string $apiKey,
        string $model = 'gpt-4o-mini',
        string $endpoint = 'https://api.openai.com/v1/chat/completions'
    ) {
        $this->apiKey = $apiKey;
        $this->endpoint = $endpoint;
        $this->model = $model;
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
     * @return string The response from OpenAI.
     */
    public function getResponse(string $input, array $context = []): string
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
                return '[OpenAI] Error: ' . $error;
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
                    'OpenAiModel API error: No response',
                    ['response' => $response]
                );
            }
            return '[OpenAI] No response.';
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
            return '[OpenAI] Exception: ' . $e->getMessage();
        }
    }
}
