<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

class XaiModel implements AiModelInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $endpoint;

    public function __construct(
        string $apiKey,
        string $model = 'grok-1',
        string $endpoint = 'https://api.x.ai/v1/chat/completions'
    ) {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->endpoint = $endpoint;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getResponse(string $input, array $context = []): string
    {
        try {
            $systemPrompt = $context['prompt'] ?? 'You are a helpful chatbot.';
            $data = [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $input],
                ],
                'max_tokens' => $context['max_tokens'] ?? 256,
                'temperature' => $context['temperature'] ?? 0.7,
            ];
            $ch = curl_init($this->endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $result = curl_exec($ch);
            if ($result === false) {
                $error = curl_error($ch);
                curl_close($ch);
                return '[xAI] Error: ' . $error;
            }
            $response = json_decode($result, true);
            curl_close($ch);
            return $response['choices'][0]['message']['content'] ?? '[xAI] No response.';
        } catch (\Throwable $e) {
            return '[xAI] Exception: ' . $e->getMessage();
        }
    }
}
