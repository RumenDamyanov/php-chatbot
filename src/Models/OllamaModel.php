<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Ollama AI Model implementation for the php-chatbot package.
 *
 * Supports local and remote Ollama API endpoints and multiple model types.
 *
 * @category Models
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT License (https://opensource.org/licenses/MIT)
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class OllamaModel implements AiModelInterface
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var string|null
     */
    protected $apiKey;

    /**
     * @var int
     */
    protected $timeout;

    /**
     * OllamaModel constructor.
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'] ?? 'http://localhost:11434';
        $this->model = $config['model'] ?? 'llama2';
        $this->apiKey = $config['api_key'] ?? null;
        $this->timeout = $config['timeout'] ?? 10;
        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid Ollama base_url');
        }
        if (!is_string($this->model) || $this->model === '') {
            throw new InvalidArgumentException('Ollama model name required');
        }
    }

    /**
     * Get a response from the Ollama AI model.
     *
     * @param string               $input   The user input.
     * @param array<string, mixed> $context Optional context for the request.
     *
     * @return string The response from the Ollama AI model.
     */
    public function getResponse(string $input, array $context = []): string
    {
        $prompt = isset($context['prompt']) && is_string($context['prompt'])
            ? $context['prompt']
            : 'You are a helpful chatbot.';
        $history = isset($context['history']) && is_array($context['history'])
            ? implode(' ', array_map('strval', $context['history']))
            : '';
        $fullPrompt = $history ? ($history . "\nUser: $input") : $input;
        $data = [
            'model' => $this->model,
            'prompt' => $fullPrompt,
            'stream' => false
        ];
        $url = rtrim($this->baseUrl, '/') . '/api/generate';
        $headers = [
            'Content-Type: application/json'
        ];
        if ($this->apiKey) {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)$this->timeout);
        $result = curl_exec($ch);
        $err = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($result === false) {
            throw new RuntimeException('Ollama API request failed: ' . $err);
        }
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('Ollama API returned HTTP ' . $status);
        }
        $json = json_decode($result, true);
        if (!is_array($json) || !isset($json['response'])) {
            throw new RuntimeException('Ollama API invalid response: ' . $result);
        }
        return (string)$json['response'];
    }
}
