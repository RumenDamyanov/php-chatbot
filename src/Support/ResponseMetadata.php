<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Support;

/**
 * Class ResponseMetadata
 *
 * Contains metadata about an AI model's response, including token usage,
 * model information, finish reason, and other relevant data.
 *
 * @category AI
 * @package  Rumenx\PhpChatbot\Support
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class ResponseMetadata
{
    /**
     * Token usage information.
     *
     * @var TokenUsage|null
     */
    protected ?TokenUsage $tokenUsage;

    /**
     * Model identifier used for the request.
     *
     * @var string
     */
    protected string $model;

    /**
     * Reason why the model stopped generating.
     * Common values: 'stop', 'length', 'content_filter', etc.
     *
     * @var string|null
     */
    protected ?string $finishReason;

    /**
     * Provider-specific response ID.
     *
     * @var string|null
     */
    protected ?string $id;

    /**
     * Timestamp when the response was created.
     *
     * @var int|null
     */
    protected ?int $created;

    /**
     * Additional provider-specific metadata.
     *
     * @var array<string, mixed>
     */
    protected array $extra;

    /**
     * Constructor for ResponseMetadata.
     *
     * @param string               $model        Model identifier.
     * @param TokenUsage|null      $tokenUsage   Token usage information.
     * @param string|null          $finishReason Reason for completion.
     * @param string|null          $id           Response ID.
     * @param int|null             $created      Creation timestamp.
     * @param array<string, mixed> $extra        Additional metadata.
     */
    public function __construct(
        string $model,
        ?TokenUsage $tokenUsage = null,
        ?string $finishReason = null,
        ?string $id = null,
        ?int $created = null,
        array $extra = []
    ) {
        $this->model = $model;
        $this->tokenUsage = $tokenUsage;
        $this->finishReason = $finishReason;
        $this->id = $id;
        $this->created = $created;
        $this->extra = $extra;
    }

    /**
     * Get the model identifier.
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Get token usage information.
     *
     * @return TokenUsage|null
     */
    public function getTokenUsage(): ?TokenUsage
    {
        return $this->tokenUsage;
    }

    /**
     * Check if token usage information is available.
     *
     * @return bool
     */
    public function hasTokenUsage(): bool
    {
        return $this->tokenUsage !== null;
    }

    /**
     * Get the finish reason.
     *
     * @return string|null
     */
    public function getFinishReason(): ?string
    {
        return $this->finishReason;
    }

    /**
     * Get the response ID.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the creation timestamp.
     *
     * @return int|null
     */
    public function getCreated(): ?int
    {
        return $this->created;
    }

    /**
     * Get all extra metadata.
     *
     * @return array<string, mixed>
     */
    public function getExtra(): array
    {
        return $this->extra;
    }

    /**
     * Get a specific extra metadata value.
     *
     * @param string $key     Metadata key.
     * @param mixed  $default Default value if key doesn't exist.
     *
     * @return mixed
     */
    public function getExtraValue(string $key, $default = null)
    {
        return $this->extra[$key] ?? $default;
    }

    /**
     * Check if the response was truncated due to length.
     *
     * @return bool
     */
    public function wasTruncated(): bool
    {
        return in_array(
            $this->finishReason,
            ['length', 'max_tokens', 'truncated'],
            true
        );
    }

    /**
     * Check if the response was stopped due to content filtering.
     *
     * @return bool
     */
    public function wasFiltered(): bool
    {
        return in_array(
            $this->finishReason,
            ['content_filter', 'safety', 'policy_violation'],
            true
        );
    }

    /**
     * Check if the response completed normally.
     *
     * @return bool
     */
    public function wasCompletedNormally(): bool
    {
        return $this->finishReason === 'stop';
    }

    /**
     * Convert metadata to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'token_usage' => $this->tokenUsage?->toArray(),
            'finish_reason' => $this->finishReason,
            'id' => $this->id,
            'created' => $this->created,
            'extra' => $this->extra,
        ];
    }

    /**
     * Get a summary of the response metadata.
     *
     * @return string
     */
    public function getSummary(): string
    {
        $parts = ["Model: {$this->model}"];

        if ($this->tokenUsage) {
            $parts[] = $this->tokenUsage->getSummary();
        }

        if ($this->finishReason) {
            $parts[] = "Finish: {$this->finishReason}";
        }

        return implode(' | ', $parts);
    }
}
