<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

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
class GeminiModel implements AiModelInterface
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
     * GeminiModel constructor.
     *
     * @param string $apiKey   The API key for Gemini.
     * @param string $model    The model name (default: gemini-1.5-flash).
     * @param string $endpoint The API endpoint.
     */
    public function __construct(
        string $apiKey,
        string $model = 'gemini-1.5-flash',
        string $endpoint = 'https://api.gemini.com/v1/chat'
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
     * Get a response from the Gemini model.
     *
     * @param string               $input   The user input.
     * @param array<string, mixed> $context Optional context for the request.
     *
     * @return string The response from Gemini.
     */
    public function getResponse(string $input, array $context = []): string
    {
        try {
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
            $result = curl_exec($ch);
            if ($result === false) {
                $error = curl_error($ch);
                curl_close($ch);
                return '[Google Gemini] Error: ' . $error;
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
                return $response['candidates'][0]['content']['parts'][0]['text'];
            }
            // Fallback for missing candidates/content
            return '[Google Gemini] No response.';
        } catch (\Throwable $e) {
            return '[Google Gemini] Exception: ' . $e->getMessage();
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
}
