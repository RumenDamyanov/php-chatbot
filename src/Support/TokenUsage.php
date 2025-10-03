<?php

namespace Rumenx\PhpChatbot\Support;

/**
 * Class TokenUsage
 *
 * Represents token usage information from AI model API responses.
 * Tracks prompt tokens, completion tokens, and total tokens used.
 *
 * @category AI
 * @package  Rumenx\PhpChatbot\Support
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class TokenUsage
{
    /**
     * Number of tokens in the prompt/input.
     *
     * @var int
     */
    protected int $promptTokens;

    /**
     * Number of tokens in the completion/output.
     *
     * @var int
     */
    protected int $completionTokens;

    /**
     * Total number of tokens used (prompt + completion).
     *
     * @var int
     */
    protected int $totalTokens;

    /**
     * Constructor for TokenUsage.
     *
     * @param int $promptTokens     Number of tokens in the prompt.
     * @param int $completionTokens Number of tokens in the completion.
     * @param int $totalTokens      Total number of tokens used.
     */
    public function __construct(
        int $promptTokens,
        int $completionTokens,
        int $totalTokens
    ) {
        $this->promptTokens = $promptTokens;
        $this->completionTokens = $completionTokens;
        $this->totalTokens = $totalTokens;
    }

    /**
     * Get the number of prompt tokens.
     *
     * @return int
     */
    public function getPromptTokens(): int
    {
        return $this->promptTokens;
    }

    /**
     * Get the number of completion tokens.
     *
     * @return int
     */
    public function getCompletionTokens(): int
    {
        return $this->completionTokens;
    }

    /**
     * Get the total number of tokens.
     *
     * @return int
     */
    public function getTotalTokens(): int
    {
        return $this->totalTokens;
    }

    /**
     * Convert token usage to array.
     *
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'prompt_tokens' => $this->promptTokens,
            'completion_tokens' => $this->completionTokens,
            'total_tokens' => $this->totalTokens,
        ];
    }

    /**
     * Create TokenUsage from array.
     *
     * @param array<string, mixed> $data Array containing token usage data.
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            promptTokens: (int) ($data['prompt_tokens'] ?? 0),
            completionTokens: (int) ($data['completion_tokens'] ?? 0),
            totalTokens: (int) ($data['total_tokens'] ?? 0)
        );
    }

    /**
     * Create TokenUsage from OpenAI-style response.
     *
     * @param array<string, mixed> $usage Usage data from OpenAI API.
     *
     * @return self
     */
    public static function fromOpenAI(array $usage): self
    {
        return new self(
            promptTokens: (int) ($usage['prompt_tokens'] ?? 0),
            completionTokens: (int) ($usage['completion_tokens'] ?? 0),
            totalTokens: (int) ($usage['total_tokens'] ?? 0)
        );
    }

    /**
     * Create TokenUsage from Anthropic-style response.
     *
     * @param array<string, mixed> $usage Usage data from Anthropic API.
     *
     * @return self
     */
    public static function fromAnthropic(array $usage): self
    {
        return new self(
            promptTokens: (int) ($usage['input_tokens'] ?? 0),
            completionTokens: (int) ($usage['output_tokens'] ?? 0),
            totalTokens: (int) (
                ($usage['input_tokens'] ?? 0) +
                ($usage['output_tokens'] ?? 0)
            )
        );
    }

    /**
     * Create TokenUsage from Google Gemini-style response.
     *
     * @param array<string, mixed> $usage Usage data from Gemini API.
     *
     * @return self
     */
    public static function fromGemini(array $usage): self
    {
        return new self(
            promptTokens: (int) ($usage['promptTokenCount'] ?? 0),
            completionTokens: (int) ($usage['candidatesTokenCount'] ?? 0),
            totalTokens: (int) ($usage['totalTokenCount'] ?? 0)
        );
    }

    /**
     * Get a human-readable summary of token usage.
     *
     * @return string
     */
    public function getSummary(): string
    {
        return sprintf(
            'Tokens: %d prompt + %d completion = %d total',
            $this->promptTokens,
            $this->completionTokens,
            $this->totalTokens
        );
    }

    /**
     * Check if token usage exceeds a threshold.
     *
     * @param int $threshold Maximum allowed total tokens.
     *
     * @return bool
     */
    public function exceedsThreshold(int $threshold): bool
    {
        return $this->totalTokens > $threshold;
    }

    /**
     * Calculate the percentage of tokens used relative to a limit.
     *
     * @param int $limit The token limit (e.g., model context window).
     *
     * @return float Percentage of tokens used (0-100).
     */
    public function getUsagePercentage(int $limit): float
    {
        if ($limit <= 0) {
            return 0.0;
        }

        return ($this->totalTokens / $limit) * 100;
    }

    /**
     * Get remaining tokens given a limit.
     *
     * @param int $limit The token limit (e.g., model context window).
     *
     * @return int Remaining available tokens.
     */
    public function getRemainingTokens(int $limit): int
    {
        return max(0, $limit - $this->totalTokens);
    }
}
