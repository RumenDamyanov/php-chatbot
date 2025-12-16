<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Health;

/**
 * Result of a health check.
 *
 * Contains the status, message, and optional metadata about a health check.
 *
 * @package Rumenx\PhpChatbot\Health
 */
class HealthCheckResult
{
    /**
     * Create a new health check result.
     *
     * @param HealthStatus             $status      The health status.
     * @param string                   $message     Human-readable status message.
     * @param array<string, mixed>     $details     Additional details about the check.
     * @param float|null               $duration    Check duration in seconds.
     * @param \DateTimeImmutable|null  $checkedAt   When the check was performed.
     */
    public function __construct(
        private readonly HealthStatus $status,
        private readonly string $message,
        private readonly array $details = [],
        private readonly ?float $duration = null,
        private readonly ?\DateTimeImmutable $checkedAt = null
    ) {
    }

    /**
     * Create a healthy result.
     *
     * @param string               $message Human-readable success message.
     * @param array<string, mixed> $details Additional details.
     *
     * @return self
     */
    public static function healthy(string $message = 'OK', array $details = []): self
    {
        return new self(
            HealthStatus::HEALTHY,
            $message,
            $details,
            null,
            new \DateTimeImmutable()
        );
    }

    /**
     * Create a degraded result.
     *
     * @param string               $message Human-readable warning message.
     * @param array<string, mixed> $details Additional details.
     *
     * @return self
     */
    public static function degraded(string $message, array $details = []): self
    {
        return new self(
            HealthStatus::DEGRADED,
            $message,
            $details,
            null,
            new \DateTimeImmutable()
        );
    }

    /**
     * Create an unhealthy result.
     *
     * @param string               $message Human-readable error message.
     * @param array<string, mixed> $details Additional details.
     *
     * @return self
     */
    public static function unhealthy(string $message, array $details = []): self
    {
        return new self(
            HealthStatus::UNHEALTHY,
            $message,
            $details,
            null,
            new \DateTimeImmutable()
        );
    }

    /**
     * Create an unknown result.
     *
     * @param string               $message Human-readable message.
     * @param array<string, mixed> $details Additional details.
     *
     * @return self
     */
    public static function unknown(string $message = 'Status unknown', array $details = []): self
    {
        return new self(
            HealthStatus::UNKNOWN,
            $message,
            $details,
            null,
            new \DateTimeImmutable()
        );
    }

    /**
     * Get the health status.
     *
     * @return HealthStatus
     */
    public function getStatus(): HealthStatus
    {
        return $this->status;
    }

    /**
     * Get the status message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get additional details.
     *
     * @return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * Get check duration in seconds.
     *
     * @return float|null
     */
    public function getDuration(): ?float
    {
        return $this->duration;
    }

    /**
     * Get when the check was performed.
     *
     * @return \DateTimeImmutable|null
     */
    public function getCheckedAt(): ?\DateTimeImmutable
    {
        return $this->checkedAt;
    }

    /**
     * Check if result is healthy.
     *
     * @return bool
     */
    public function isHealthy(): bool
    {
        return $this->status->isHealthy();
    }

    /**
     * Check if result is degraded.
     *
     * @return bool
     */
    public function isDegraded(): bool
    {
        return $this->status->isDegraded();
    }

    /**
     * Check if result is unhealthy.
     *
     * @return bool
     */
    public function isUnhealthy(): bool
    {
        return $this->status->isUnhealthy();
    }

    /**
     * Convert to array for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'message' => $this->message,
            'details' => $this->details,
            'duration' => $this->duration,
            'checked_at' => $this->checkedAt?->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * Convert to JSON string.
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }
}
