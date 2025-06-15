<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

class GeminiModel implements AiModelInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $endpoint;

    public function __construct(
        string $apiKey,
        string $model = 'gemini-1.5-pro',
        string $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models'
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
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $systemPrompt . "\n" . $input]]],
                ],
            ];
            $url = rtrim($this->endpoint, '/') . '/' . $this->model . ':generateContent?key=' . $this->apiKey;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $result = curl_exec($ch);
            if ($result === false) {
                $error = curl_error($ch);
                curl_close($ch);
                return '[Google Gemini] Error: ' . $error;
            }
            $response = json_decode($result, true);
            curl_close($ch);
            return $response['candidates'][0]['content']['parts'][0]['text'] ?? '[Google Gemini] No response.';
        } catch (\Throwable $e) {
            return '[Google Gemini] Exception: ' . $e->getMessage();
        }
    }
}
