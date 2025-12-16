<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Health;

/**
 * Health monitor that aggregates multiple health checks.
 *
 * Runs multiple health checkers and provides an overall system health status.
 *
 * @package Rumenx\PhpChatbot\Health
 */
class HealthMonitor
{
    /**
     * Registered health checkers.
     *
     * @var array<string, HealthCheckerInterface>
     */
    private array $checkers = [];

    /**
     * Register a health checker.
     *
     * @param string                   $key     Unique identifier for this checker.
     * @param HealthCheckerInterface   $checker The health checker to register.
     *
     * @return self
     */
    public function register(string $key, HealthCheckerInterface $checker): self
    {
        $this->checkers[$key] = $checker;
        return $this;
    }

    /**
     * Run all health checks.
     *
     * @return array<string, HealthCheckResult>
     */
    public function checkAll(): array
    {
        $results = [];

        foreach ($this->checkers as $key => $checker) {
            $results[$key] = $checker->check();
        }

        return $results;
    }

    /**
     * Run a specific health check.
     *
     * @param string $key The checker identifier.
     *
     * @return HealthCheckResult|null Null if checker not found.
     */
    public function check(string $key): ?HealthCheckResult
    {
        if (!isset($this->checkers[$key])) {
            return null;
        }

        return $this->checkers[$key]->check();
    }

    /**
     * Get overall system health status.
     *
     * Aggregates all health check results into a single status:
     * - HEALTHY: All checks are healthy
     * - DEGRADED: Some non-critical checks failed or are degraded
     * - UNHEALTHY: Any critical check failed
     * - UNKNOWN: No checks registered or all results unknown
     *
     * @return HealthCheckResult
     */
    public function getOverallHealth(): HealthCheckResult
    {
        if (empty($this->checkers)) {
            return HealthCheckResult::unknown('No health checks registered');
        }

        $results = $this->checkAll();
        $hasCriticalFailure = false;
        $hasDegraded = false;
        $allHealthy = true;
        $details = [];

        foreach ($results as $key => $result) {
            $checker = $this->checkers[$key];

            $details[$key] = [
                'status' => $result->getStatus()->value,
                'message' => $result->getMessage(),
                'critical' => $checker->isCritical(),
            ];

            if ($result->isUnhealthy()) {
                $allHealthy = false;
                if ($checker->isCritical()) {
                    $hasCriticalFailure = true;
                }
            } elseif ($result->isDegraded()) {
                $allHealthy = false;
                $hasDegraded = true;
            }
        }

        // Determine overall status
        if ($hasCriticalFailure) {
            return new HealthCheckResult(
                HealthStatus::UNHEALTHY,
                'One or more critical health checks failed',
                $details,
                null,
                new \DateTimeImmutable()
            );
        }

        if ($hasDegraded) {
            return new HealthCheckResult(
                HealthStatus::DEGRADED,
                'Some health checks are degraded',
                $details,
                null,
                new \DateTimeImmutable()
            );
        }

        if ($allHealthy) {
            return new HealthCheckResult(
                HealthStatus::HEALTHY,
                'All health checks passed',
                $details,
                null,
                new \DateTimeImmutable()
            );
        }

        return HealthCheckResult::unknown('Unable to determine overall health');
    }

    /**
     * Get all registered checkers.
     *
     * @return array<string, HealthCheckerInterface>
     */
    public function getCheckers(): array
    {
        return $this->checkers;
    }

    /**
     * Remove a health checker.
     *
     * @param string $key The checker identifier.
     *
     * @return bool True if removed, false if not found.
     */
    public function unregister(string $key): bool
    {
        if (!isset($this->checkers[$key])) {
            return false;
        }

        unset($this->checkers[$key]);
        return true;
    }

    /**
     * Clear all health checkers.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->checkers = [];
    }
}
