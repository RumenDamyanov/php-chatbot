<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Health;

/**
 * Interface for health check implementations.
 *
 * Health checkers verify the availability and functionality of
 * various components (AI providers, storage, dependencies, etc.).
 *
 * @package Rumenx\PhpChatbot\Health
 */
interface HealthCheckerInterface
{
    /**
     * Perform a health check.
     *
     * @return HealthCheckResult The result of the health check.
     */
    public function check(): HealthCheckResult;

    /**
     * Get the name of this health check.
     *
     * @return string A descriptive name (e.g., "OpenAI API", "Redis Connection").
     */
    public function getName(): string;

    /**
     * Check if this health checker is critical.
     *
     * Critical health checks should cause the overall system to be
     * marked as unhealthy if they fail.
     *
     * @return bool True if this check is critical.
     */
    public function isCritical(): bool;
}

