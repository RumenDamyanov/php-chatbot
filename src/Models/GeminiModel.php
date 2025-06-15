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
    private string $_apiKey;
    /**
     * The model name.
     *
     * @var string
     */
    private string $_model;
    /**
     * The API endpoint.
     *
     * @var string
     */
    private string $_endpoint;

    /**
     * GeminiModel constructor.
     *
     * @param string $apiKey   The API key for Gemini.
     * @param string $model    The model name.
     * @param string $endpoint The API endpoint.
     */
    public function __construct(
        string $apiKey,
        string $model = 'gemini-default',
        string $endpoint = 'https://api.gemini.com/v1/chat'
    ) {
        $this->_apiKey = $apiKey;
        $this->_model = $model;
        $this->_endpoint = $endpoint;
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
        $this->_model = $model;
    }

    /**
     * Get the model name.
     *
     * @return string The model name.
     */
    public function getModel(): string
    {
        return $this->_model;
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
            $url = rtrim($this->_endpoint, '/')
                . '/' . $this->_model
                . ':generateContent?key=' . $this->_apiKey;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [
                    'Content-Type: application/json',
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
            if (is_array($response)
                && isset($response['candidates'][0]['content']['parts'][0]['text'])
                && is_string(
                    $response['candidates'][0]['content']['parts'][0]['text']
                )
            ) {
                return $response['candidates'][0]['content']['parts'][0]['text'];
            }
            return '[Google Gemini] No response.';
        } catch (\Throwable $e) {
            return '[Google Gemini] Exception: ' . $e->getMessage();
        }
    }
}
