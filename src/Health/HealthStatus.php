<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Health;

/**
 * Health check status enumeration.
 *
 * Represents the overall health status of a component or system.
 *
 * @package Rumenx\PhpChatbot\Health
 */
enum HealthStatus: string
{
    /**
     * Component is healthy and functioning normally.
     */
    case HEALTHY = 'healthy';

    /**
     * Component is degraded but still functional.
     * May indicate performance issues or partial failures.
     */
    case DEGRADED = 'degraded';

    /**
     * Component is unhealthy and not functioning correctly.
     */
    case UNHEALTHY = 'unhealthy';

    /**
     * Health status is unknown (not checked yet or check failed).
     */
    case UNKNOWN = 'unknown';

    /**
     * Check if status is healthy.
     *
     * @return bool True if status is HEALTHY.
     */
    public function isHealthy(): bool
    {
        return $this === self::HEALTHY;
    }

    /**
     * Check if status is degraded.
     *
     * @return bool True if status is DEGRADED.
     */
    public function isDegraded(): bool
    {
        return $this === self::DEGRADED;
    }

    /**
     * Check if status is unhealthy.
     *
     * @return bool True if status is UNHEALTHY.
     */
    public function isUnhealthy(): bool
    {
        return $this === self::UNHEALTHY;
    }

    /**
     * Check if status is unknown.
     *
     * @return bool True if status is UNKNOWN.
     */
    public function isUnknown(): bool
    {
        return $this === self::UNKNOWN;
    }

    /**
     * Get HTTP status code for this health status.
     *
     * @return int HTTP status code (200, 503, etc.)
     */
    public function toHttpStatus(): int
    {
        return match ($this) {
            self::HEALTHY => 200,
            self::DEGRADED => 200,
            self::UNHEALTHY => 503,
            self::UNKNOWN => 503,
        };
    }
}

