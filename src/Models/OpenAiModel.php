<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

class OpenAiModel implements AiModelInterface
{
    protected string $apiKey;
    protected string $endpoint;
    protected string $model;

    public function __construct(
        string $apiKey,
        string $model = 'gpt-3.5-turbo',
        string $endpoint = 'https://api.openai.com/v1/chat/completions'
    ) {
        $this->apiKey = $apiKey;
        $this->endpoint = $endpoint;
        $this->model = $model;
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
            $messages = [
                ['role' => 'system', 'content' => $context['prompt'] ?? 'You are a helpful chatbot.'],
                ['role' => 'user', 'content' => $input],
            ];
            $data = [
                'model' => $context['openai_model'] ?? $this->model,
                'messages' => $messages,
                'temperature' => $context['temperature'] ?? 0.7,
                'max_tokens' => $context['max_tokens'] ?? 256,
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
                if (isset($context['logger']) && $context['logger'] instanceof \Psr\Log\LoggerInterface) {
                    $context['logger']->error('OpenAiModel cURL error: ' . $error, ['data' => $data]);
                }
                curl_close($ch);
                return json_encode(['status' => 'error', 'message' => '[OpenAI] Error: ' . $error]);
            }
            $response = json_decode($result, true);
            curl_close($ch);
            if (isset($response['choices'][0]['message']['content'])) {
                return $response['choices'][0]['message']['content'];
            }
            if (isset($context['logger']) && $context['logger'] instanceof \Psr\Log\LoggerInterface) {
                $context['logger']->error('OpenAiModel API error: No response', ['response' => $response]);
            }
            return json_encode(['status' => 'error', 'message' => '[OpenAI] No response.']);
        } catch (\Throwable $e) {
            if (isset($context['logger']) && $context['logger'] instanceof \Psr\Log\LoggerInterface) {
                $context['logger']->error('OpenAiModel exception: ' . $e->getMessage(), ['exception' => $e]);
            }
            return json_encode(['status' => 'error', 'message' => '[OpenAI] Exception: ' . $e->getMessage()]);
        }
    }
}
