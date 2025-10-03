<?php

namespace Rumenx\PhpChatbot\Support;

use Stringable;

/**
 * Class ChatResponse
 *
 * Represents a complete response from an AI model, including the content
 * and metadata such as token usage and finish reason.
 *
 * This class implements Stringable for backward compatibility,
 * allowing it to be used as a string in existing code.
 *
 * @category AI
 * @package  Rumenx\PhpChatbot\Support
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class ChatResponse implements Stringable
{
    /**
     * The response content (AI-generated text).
     *
     * @var string
     */
    protected string $content;

    /**
     * Response metadata.
     *
     * @var ResponseMetadata
     */
    protected ResponseMetadata $metadata;

    /**
     * Constructor for ChatResponse.
     *
     * @param string           $content  The AI-generated content.
     * @param ResponseMetadata $metadata Response metadata.
     */
    public function __construct(string $content, ResponseMetadata $metadata)
    {
        $this->content = $content;
        $this->metadata = $metadata;
    }

    /**
     * Get the response content.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get the response metadata.
     *
     * @return ResponseMetadata
     */
    public function getMetadata(): ResponseMetadata
    {
        return $this->metadata;
    }

    /**
     * Get token usage information.
     *
     * @return TokenUsage|null
     */
    public function getTokenUsage(): ?TokenUsage
    {
        return $this->metadata->getTokenUsage();
    }

    /**
     * Get the model identifier.
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->metadata->getModel();
    }

    /**
     * Get the finish reason.
     *
     * @return string|null
     */
    public function getFinishReason(): ?string
    {
        return $this->metadata->getFinishReason();
    }

    /**
     * Check if token usage information is available.
     *
     * @return bool
     */
    public function hasTokenUsage(): bool
    {
        return $this->metadata->hasTokenUsage();
    }

    /**
     * Check if the response was truncated.
     *
     * @return bool
     */
    public function wasTruncated(): bool
    {
        return $this->metadata->wasTruncated();
    }

    /**
     * Check if the response was filtered.
     *
     * @return bool
     */
    public function wasFiltered(): bool
    {
        return $this->metadata->wasFiltered();
    }

    /**
     * Check if the response completed normally.
     *
     * @return bool
     */
    public function wasCompletedNormally(): bool
    {
        return $this->metadata->wasCompletedNormally();
    }

    /**
     * Convert response to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'metadata' => $this->metadata->toArray(),
        ];
    }

    /**
     * Get a human-readable summary of the response.
     *
     * @return string
     */
    public function getSummary(): string
    {
        $contentLength = strlen($this->content);
        $preview = substr($this->content, 0, 50);

        if ($contentLength > 50) {
            $preview .= '...';
        }

        return sprintf(
            "%s | Length: %d chars | Preview: \"%s\"",
            $this->metadata->getSummary(),
            $contentLength,
            $preview
        );
    }

    /**
     * Convert to string (for backward compatibility).
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * Create a ChatResponse from a simple string.
     * Useful for testing or when metadata is not available.
     *
     * @param string $content The response content.
     * @param string $model   The model identifier (default: 'unknown').
     *
     * @return self
     */
    public static function fromString(
        string $content,
        string $model = 'unknown'
    ): self {
        $metadata = new ResponseMetadata($model);
        return new self($content, $metadata);
    }

    /**
     * Create a ChatResponse from OpenAI API response.
     *
     * @param string              $content  The response content.
     * @param array<string,mixed> $response Full API response.
     *
     * @return self
     */
    public static function fromOpenAI(string $content, array $response): self
    {
        $tokenUsage = isset($response['usage'])
            ? TokenUsage::fromOpenAI($response['usage'])
            : null;

        $metadata = new ResponseMetadata(
            model: $response['model'] ?? 'unknown',
            tokenUsage: $tokenUsage,
            finishReason: $response['choices'][0]['finish_reason'] ?? null,
            id: $response['id'] ?? null,
            created: $response['created'] ?? null,
            extra: [
                'object' => $response['object'] ?? null,
                'system_fingerprint' => $response['system_fingerprint'] ?? null,
            ]
        );

        return new self($content, $metadata);
    }

    /**
     * Create a ChatResponse from Anthropic API response.
     *
     * @param string              $content  The response content.
     * @param array<string,mixed> $response Full API response.
     *
     * @return self
     */
    public static function fromAnthropic(
        string $content,
        array $response
    ): self {
        $tokenUsage = isset($response['usage'])
            ? TokenUsage::fromAnthropic($response['usage'])
            : null;

        $metadata = new ResponseMetadata(
            model: $response['model'] ?? 'unknown',
            tokenUsage: $tokenUsage,
            finishReason: $response['stop_reason'] ?? null,
            id: $response['id'] ?? null,
            created: null,
            extra: [
                'type' => $response['type'] ?? null,
                'role' => $response['role'] ?? null,
            ]
        );

        return new self($content, $metadata);
    }

    /**
     * Create a ChatResponse from Google Gemini API response.
     *
     * @param string              $content  The response content.
     * @param array<string,mixed> $response Full API response.
     * @param string              $model    Model identifier.
     *
     * @return self
     */
    public static function fromGemini(
        string $content,
        array $response,
        string $model
    ): self {
        $tokenUsage = isset($response['usageMetadata'])
            ? TokenUsage::fromGemini($response['usageMetadata'])
            : null;

        $finishReason = null;
        if (isset($response['candidates'][0]['finishReason'])) {
            $finishReason = strtolower(
                $response['candidates'][0]['finishReason']
            );
        }

        $metadata = new ResponseMetadata(
            model: $model,
            tokenUsage: $tokenUsage,
            finishReason: $finishReason,
            id: null,
            created: null,
            extra: [
                'safety_ratings' =>
                    $response['candidates'][0]['safetyRatings'] ?? null,
            ]
        );

        return new self($content, $metadata);
    }
}
