<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

/**
 * Default AI Model implementation for the php-chatbot package.
 *
 * This class provides a fallback/default AI model for chatbot responses.
 *
 * @category Models
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT License (https://opensource.org/licenses/MIT)
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class DefaultAiModel implements AiModelInterface
{
    /**
     * Get a response from the Default AI model.
     *
     * @param string               $input   The user input.
     * @param array<string, mixed> $context Optional context for the request.
     *
     * @return string The response from the Default AI model.
     */
    public function getResponse(string $input, array $context = []): string
    {
        $prompt = isset($context['prompt']) && is_string($context['prompt'])
            ? $context['prompt']
            : 'You are a helpful chatbot.';
        $lang = isset($context['language']) && is_string($context['language'])
            ? $context['language']
            : 'en';
        $userContext = !empty($context['history']) && is_array($context['history'])
            ? 'Previous conversation: '
                . implode(' ', array_map('strval', $context['history']))
            : '';
        return "[DefaultAI-$lang] $prompt\n"
            . "$userContext\n"
            . "User: $input\n"
            . "Bot: This is a default AI response.";
    }
}
