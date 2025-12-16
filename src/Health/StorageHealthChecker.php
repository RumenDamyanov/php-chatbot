<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Health;

use Rumenx\PhpChatbot\Contracts\MemoryStorageInterface;

/**
 * Health checker for memory storage systems.
 *
 * Verifies that storage can read and write data successfully.
 *
 * @package Rumenx\PhpChatbot\Health
 */
class StorageHealthChecker implements HealthCheckerInterface
{
    /**
     * Test session ID for health checks.
     */
    private const TEST_SESSION = 'health_check_test';

    /**
     * Create a new storage health checker.
     *
     * @param MemoryStorageInterface $storage   The storage to check.
     * @param string                 $name      Custom name for this check.
     * @param bool                   $critical  Whether this check is critical.
     */
    public function __construct(
        private readonly MemoryStorageInterface $storage,
        private readonly string $name = 'Storage',
        private readonly bool $critical = false
    ) {
    }

    /**
     * Perform a health check on the storage system.
     *
     * @return HealthCheckResult
     */
    public function check(): HealthCheckResult
    {
        $startTime = microtime(true);

        try {
            // Test write
            $testData = [
                ['role' => 'user', 'content' => 'health check'],
                ['role' => 'assistant', 'content' => 'ok'],
            ];

            $this->storage->save(self::TEST_SESSION, $testData);

            // Test read
            $retrieved = $this->storage->load(self::TEST_SESSION);

            // Verify data integrity
            if ($retrieved !== $testData) {
                return new HealthCheckResult(
                    HealthStatus::UNHEALTHY,
                    'Storage data integrity check failed',
                    [
                        'expected_count' => count($testData),
                        'actual_count' => count($retrieved),
                    ],
                    microtime(true) - $startTime,
                    new \DateTimeImmutable()
                );
            }

            // Clean up test data
            $this->storage->clear(self::TEST_SESSION);

            $duration = microtime(true) - $startTime;

            // Check performance
            if ($duration > 1.0) {
                return new HealthCheckResult(
                    HealthStatus::DEGRADED,
                    'Storage operations are slow',
                    [
                        'response_time' => round($duration, 3),
                        'threshold' => 1.0,
                    ],
                    $duration,
                    new \DateTimeImmutable()
                );
            }

            return new HealthCheckResult(
                HealthStatus::HEALTHY,
                'Storage is working normally',
                [
                    'response_time' => round($duration, 3),
                    'read_write_test' => 'passed',
                ],
                $duration,
                new \DateTimeImmutable()
            );
        } catch (\Throwable $e) {
            return new HealthCheckResult(
                HealthStatus::UNHEALTHY,
                'Storage health check failed: ' . $e->getMessage(),
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

