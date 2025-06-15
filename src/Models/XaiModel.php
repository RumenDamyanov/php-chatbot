<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

/**
 * XAI Model implementation for the php-chatbot package.
 *
 * This class provides integration with the xAI API for generating chatbot
 * responses.
 *
 * @category Models
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT License (https://opensource.org/licenses/MIT)
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class XaiModel implements AiModelInterface
{
    /**
     * The API key for xAI.
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
     * XaiModel constructor.
     *
     * @param string $apiKey   The API key for xAI.
     * @param string $model    The model name.
     * @param string $endpoint The API endpoint.
     */
    public function __construct(
        string $apiKey,
        string $model = 'xai-default',
        string $endpoint = 'https://api.xai.com/v1/chat'
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
     * @return string              The model name.
     */
    public function getModel(): string
    {
        return $this->_model;
    }

    /**
     * Get a response from the xAI model.
     *
     * @param string               $input   The user input.
     * @param array<string, mixed> $context Optional context for the request.
     *
     * @return string              The response from xAI.
     */
    public function getResponse(string $input, array $context = []): string
    {
        try {
            $systemPrompt = 'You are a helpful chatbot.';
            if (isset($context['prompt'])
                && is_string($context['prompt'])
            ) {
                $prompt = $context['prompt'];
                $systemPrompt = $prompt;
            }
            $maxTokens = 256;
            if (isset($context['max_tokens'])
                && is_numeric($context['max_tokens'])
            ) {
                $tokens = $context['max_tokens'];
                $maxTokens = (int) $tokens;
            }
            $temperature = 0.7;
            if (isset($context['temperature'])
                && is_numeric($context['temperature'])
            ) {
                $temp = $context['temperature'];
                $temperature = (float) $temp;
            }
            $data = [
                'model' => $this->_model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $input],
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ];
            $ch = curl_init($this->_endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->_apiKey,
                ]
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $result = curl_exec($ch);
            if ($result === false) {
                $error = curl_error($ch);
                curl_close($ch);
                return '[xAI] Error: ' . $error;
            }
            $response = json_decode(is_string($result) ? $result : '', true);
            curl_close($ch);
            if (is_array($response)
                && isset($response['choices'][0]['message']['content'])
                && is_string($response['choices'][0]['message']['content'])
            ) {
                return $response['choices'][0]['message']['content'];
            }
            return '[xAI] No response.';
        } catch (\Throwable $e) {
            return '[xAI] Exception: ' . $e->getMessage();
        }
    }
}
