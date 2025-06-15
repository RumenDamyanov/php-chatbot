<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

class DeepSeekAiModel implements AiModelInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $endpoint;

    public function __construct(
        string $apiKey,
        string $model = 'deepseek-chat',
        string $endpoint = 'https://api.deepseek.com/v1/chat/completions'
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
            $messages = [
                ['role' => 'system', 'content' => $context['prompt'] ?? 'You are a helpful chatbot.'],
                ['role' => 'user', 'content' => $input],
            ];
            $data = [
                'model' => $context['deepseek_model'] ?? $this->model,
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
                    $context['logger']->error('DeepSeekAiModel cURL error: ' . $error, ['data' => $data]);
                }
                curl_close($ch);
                return json_encode(['status' => 'error', 'message' => '[DeepSeek] Error: ' . $error]);
            }
            $response = json_decode($result, true);
            curl_close($ch);
            if (isset($response['choices'][0]['message']['content'])) {
                return $response['choices'][0]['message']['content'];
            }
            if (isset($context['logger']) && $context['logger'] instanceof \Psr\Log\LoggerInterface) {
                $context['logger']->error('DeepSeekAiModel API error: No response', ['response' => $response]);
            }
            return json_encode(['status' => 'error', 'message' => '[DeepSeek] No response.']);
        } catch (\Throwable $e) {
            if (isset($context['logger']) && $context['logger'] instanceof \Psr\Log\LoggerInterface) {
                $context['logger']->error('DeepSeekAiModel exception: ' . $e->getMessage(), ['exception' => $e]);
            }
            return json_encode(['status' => 'error', 'message' => '[DeepSeek] Exception: ' . $e->getMessage()]);
        }
    }
}
