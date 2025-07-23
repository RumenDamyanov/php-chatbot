<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Middleware;

/**
 * Middleware for filtering and enhancing chat messages for safe AI responses.
 *
 * This middleware analyzes user messages and appends hidden system instructions
 * to the AI prompt/context, ensuring responses are safe, appropriate, and aligned
 * with guidelines. Instructions are not shown in chat history.
 *
 * @category Middleware
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT License (https://opensource.org/licenses/MIT)
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class ChatMessageFilterMiddleware
{
    /**
     * List of system instructions for the AI.
     *
     * @var array<string>
     */
    protected array $instructions;

    /**
     * List of profanities to filter.
     *
     * @var array<string>
     */
    protected array $profanities;

    /**
     * List of aggression patterns to flag.
     *
     * @var array<string>
     */
    protected array $aggressionPatterns;

    /**
     * Regex pattern for link detection.
     *
     * @var string
     */
    protected string $linkPattern;

    /**
     * ChatMessageFilterMiddleware constructor.
     *
     * @param array<string> $instructions List of system instructions.
     * @param array<string> $profanities List of profanities to filter.
     * @param array<string> $aggressionPatterns List of aggression patterns.
     * @param string $linkPattern Regex for link detection.
     */
    public function __construct(
        array $instructions = [],
        array $profanities = [],
        array $aggressionPatterns = [],
        string $linkPattern = ''
    ) {
        $this->instructions = $instructions;
        $this->profanities = $profanities;
        $this->aggressionPatterns = $aggressionPatterns;
        $this->linkPattern = $linkPattern ?: '/https?:\/\/[\w\.-]+/i';
    }

    /**
     * Filter and enhance the user message and context for the AI model.
     *
     * @param string               $message The user-submitted message.
     * @param array<string, mixed> $context The context array for the AI model.
     *
     * @return array{message: string, context: array<string, mixed>}
     */
    public function handle(string $message, array $context = []): array
    {
        // Basic example: rephrase or flag inappropriate content (expand as needed)
        $filteredMessage = $this->basicFilter($message);

        // Append hidden system instructions to the context (not chat history)
        if (!empty($this->instructions)) {
            $systemPrompt = implode(' ', $this->instructions);
            // Add as a hidden system prompt (not visible to user)
            $context['system_instructions'] = $systemPrompt;
        }

        return [
            'message' => $filteredMessage,
            'context' => $context,
        ];
    }

    /**
     * Basic filter for inappropriate content (expand as needed).
     *
     * @param string $message The user message.
     *
     * @return string
     */
    protected function basicFilter(string $message): string
    {
        // Redact links
        if ($this->linkPattern) {
            $message = preg_replace(
                $this->linkPattern,
                '[link removed]',
                $message
            );
        }
        // Profanity filter
        if (!empty($this->profanities)) {
            $message = str_ireplace($this->profanities, '[censored]', $message);
        }
        // Flag aggression
        if (!empty($this->aggressionPatterns)) {
            $pattern = '/('
                . implode('|', array_map('preg_quote', $this->aggressionPatterns))
                . ')/i';
            if (preg_match($pattern, $message)) {
                $message .= ' [Please use respectful language.]';
            }
        }
        return $message;
    }
}
