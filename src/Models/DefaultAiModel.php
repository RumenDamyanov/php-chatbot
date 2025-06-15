<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

class DefaultAiModel implements AiModelInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function getResponse(string $input, array $context = []): string
    {
        $prompt = $context['prompt'] ?? 'You are a helpful chatbot.';
        $lang = $context['language'] ?? 'en';
        $userContext = !empty($context['history']) ? "Previous conversation: " . implode(" ", $context['history']) : "";
        return "[DefaultAI-$lang] $prompt\n$userContext\nUser: $input\nBot: This is a default AI response.";
    }
}
