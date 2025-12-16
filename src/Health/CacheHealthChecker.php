<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Health;

use Rumenx\PhpChatbot\Cache\CacheInterface;
use Rumenx\PhpChatbot\Support\ChatResponse;

/**
 * Health checker for cache systems.
 *
 * Verifies that cache can store and retrieve responses successfully.
 *
 * @package Rumenx\PhpChatbot\Health
 */
class CacheHealthChecker implements HealthCheckerInterface
{
    /**
     * Test cache key.
     */
    private const TEST_KEY = 'health_check_cache_test';

    /**
     * Create a new cache health checker.
     *
     * @param CacheInterface $cache    The cache to check.
     * @param string         $name     Custom name for this check.
     * @param bool           $critical Whether this check is critical.
     */
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly string $name = 'Cache',
        private readonly bool $critical = false
    ) {
    }

    /**
     * Perform a health check on the cache system.
     *
     * @return HealthCheckResult
     */
    public function check(): HealthCheckResult
    {
        $startTime = microtime(true);

        try {
            // Create test response
            $testResponse = ChatResponse::fromString('health check test', 'test-model');

            // Test write
            $writeSuccess = $this->cache->set(self::TEST_KEY, $testResponse, 60);

            if (!$writeSuccess) {
                return new HealthCheckResult(
                    HealthStatus::UNHEALTHY,
                    'Cache write operation failed',
                    ['operation' => 'set'],
                    microtime(true) - $startTime,
                    new \DateTimeImmutable()
                );
            }

            // Test read
            $retrieved = $this->cache->get(self::TEST_KEY);

            if ($retrieved === null) {
                return new HealthCheckResult(
                    HealthStatus::UNHEALTHY,
                    'Cache read operation failed',
                    ['operation' => 'get'],
                    microtime(true) - $startTime,
                    new \DateTimeImmutable()
                );
            }

            // Test delete
            $deleteSuccess = $this->cache->delete(self::TEST_KEY);

            $duration = microtime(true) - $startTime;

            // Check performance
            if ($duration > 0.5) {
                return new HealthCheckResult(
                    HealthStatus::DEGRADED,
                    'Cache operations are slow',
                    [
                        'response_time' => round($duration, 3),
                        'threshold' => 0.5,
                    ],
                    $duration,
                    new \DateTimeImmutable()
                );
            }

            return new HealthCheckResult(
                HealthStatus::HEALTHY,
                'Cache is working normally',
                [
                    'response_time' => round($duration, 3),
                    'operations_tested' => ['set', 'get', 'delete'],
                    'all_tests_passed' => $deleteSuccess,
                ],
                $duration,
                new \DateTimeImmutable()
            );
        } catch (\Throwable $e) {
            return new HealthCheckResult(
                HealthStatus::UNHEALTHY,
                'Cache health check failed: ' . $e->getMessage(),
                [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                ],
                microtime(true) - $startTime,
                new \DateTimeImmutable()
            );
        }
    }

    /**
     * Get the name of this health check.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Check if this health check is critical.
     *
     * @return bool
     */
    public function isCritical(): bool
    {
        return $this->critical;
    }
}

