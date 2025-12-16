<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Health;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

/**
 * Health checker for AI models.
 *
 * Verifies that an AI model can successfully respond to a test query.
 *
 * @package Rumenx\PhpChatbot\Health
 */
class ModelHealthChecker implements HealthCheckerInterface
{
    /**
     * Test query to send to the model.
     */
    private const TEST_QUERY = 'ping';

    /**
     * Create a new model health checker.
     *
     * @param AiModelInterface $model    The model to check.
     * @param string           $name     Custom name for this check.
     * @param bool             $critical Whether this check is critical.
     */
    public function __construct(
        private readonly AiModelInterface $model,
        private readonly string $name = 'AI Model',
        private readonly bool $critical = true
    ) {
    }

    /**
     * Perform a health check on the AI model.
     *
     * @return HealthCheckResult
     */
    public function check(): HealthCheckResult
    {
        $startTime = microtime(true);

        try {
            // Send a simple test query
            $response = $this->model->getResponse(self::TEST_QUERY, [
                'max_tokens' => 10,
                'temperature' => 0.0,
            ]);

            $duration = microtime(true) - $startTime;
            $responseText = (string) $response;

            // Check if we got a valid response
            if (empty($responseText)) {
                return new HealthCheckResult(
                    HealthStatus::UNHEALTHY,
                    'Model returned empty response',
                    [
                        'model' => $this->model->getModel(),
                        'test_query' => self::TEST_QUERY,
                    ],
                    $duration,
                    new \DateTimeImmutable()
                );
            }

            // Check response time
            if ($duration > 5.0) {
                return new HealthCheckResult(
                    HealthStatus::DEGRADED,
                    'Model response time is slow',
                    [
                        'model' => $this->model->getModel(),
                        'response_time' => round($duration, 3),
                        'threshold' => 5.0,
                    ],
                    $duration,
                    new \DateTimeImmutable()
                );
            }

            return new HealthCheckResult(
                HealthStatus::HEALTHY,
                'Model is responding normally',
                [
                    'model' => $this->model->getModel(),
                    'response_time' => round($duration, 3),
                    'response_length' => strlen($responseText),
                ],
                $duration,
                new \DateTimeImmutable()
            );
        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;

            return new HealthCheckResult(
                HealthStatus::UNHEALTHY,
                'Model health check failed: ' . $e->getMessage(),
                [
                    'model' => $this->model->getModel(),
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                ],
                $duration,
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

