<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Health\HealthMonitor;
use Rumenx\PhpChatbot\Health\HealthCheckResult;
use Rumenx\PhpChatbot\Health\HealthStatus;
use Rumenx\PhpChatbot\Health\HealthCheckerInterface;

beforeEach(function () {
    $this->monitor = new HealthMonitor();
});

it('registers health checkers', function () {
    $checker = new class implements HealthCheckerInterface {
        public function check(): HealthCheckResult
        {
            return HealthCheckResult::healthy();
        }

        public function getName(): string
        {
            return 'Test';
        }

        public function isCritical(): bool
        {
            return true;
        }
    };

    $this->monitor->register('test', $checker);

    expect($this->monitor->getCheckers())->toHaveCount(1);
    expect($this->monitor->getCheckers())->toHaveKey('test');
});

it('runs all health checks', function () {
    $checker1 = new class implements HealthCheckerInterface {
        public function check(): HealthCheckResult
        {
            return HealthCheckResult::healthy('Check 1');
        }

        public function getName(): string
        {
            return 'Test 1';
        }

        public function isCritical(): bool
        {
            return true;
        }
    };

    $checker2 = new class implements HealthCheckerInterface {
        public function check(): HealthCheckResult
        {
            return HealthCheckResult::healthy('Check 2');
        }

        public function getName(): string
        {
            return 'Test 2';
        }

        public function isCritical(): bool
        {
            return false;
        }
    };

    $this->monitor->register('check1', $checker1);
    $this->monitor->register('check2', $checker2);

    $results = $this->monitor->checkAll();

    expect($results)->toHaveCount(2);
    expect($results)->toHaveKeys(['check1', 'check2']);
    expect($results['check1']->getMessage())->toBe('Check 1');
    expect($results['check2']->getMessage())->toBe('Check 2');
});

it('runs specific health check', function () {
    $checker = new class implements HealthCheckerInterface {
        public function check(): HealthCheckResult
        {
            return HealthCheckResult::healthy('Specific check');
        }

        public function getName(): string
        {
            return 'Specific';
        }

        public function isCritical(): bool
        {
            return true;
        }
    };

    $this->monitor->register('specific', $checker);

    $result = $this->monitor->check('specific');

    expect($result)->not->toBeNull();
    expect($result->getMessage())->toBe('Specific check');
});

it('returns null for non-existent checker', function () {
    $result = $this->monitor->check('non-existent');
    expect($result)->toBeNull();
});

it('calculates overall healthy status', function () {
    $healthyChecker = new class implements HealthCheckerInterface {
        public function check(): HealthCheckResult
        {
            return HealthCheckResult::healthy();
        }

        public function getName(): string
        {
            return 'Healthy';
        }

        public function isCritical(): bool
        {
            return true;
        }
    };

    $this->monitor->register('healthy', $healthyChecker);

    $overall = $this->monitor->getOverallHealth();

    expect($overall->getStatus())->toBe(HealthStatus::HEALTHY);
});

it('calculates overall degraded status', function () {
    $healthyChecker = new class implements HealthCheckerInterface {
        public function check(): HealthCheckResult
        {
            return HealthCheckResult::healthy();
        }

        public function getName(): string
        {
            return 'Healthy';
        }

        public function isCritical(): bool
        {
            return true;
        }
    };

    $degradedChecker = new class implements HealthCheckerInterface {
        public function check(): HealthCheckResult
        {
            return HealthCheckResult::degraded('Slow');
        }

        public function getName(): string
        {
            return 'Degraded';
        }

        public function isCritical(): bool
        {
            return false;
        }
    };

    $this->monitor->register('healthy', $healthyChecker);
    $this->monitor->register('degraded', $degradedChecker);

    $overall = $this->monitor->getOverallHealth();

    expect($overall->getStatus())->toBe(HealthStatus::DEGRADED);
});

it('calculates overall unhealthy status for critical failures', function () {
    $healthyChecker = new class implements HealthCheckerInterface {
        public function check(): HealthCheckResult
        {
            return HealthCheckResult::healthy();
        }

        public function getName(): string
        {
            return 'Healthy';
        }

        public function isCritical(): bool
        {
            return false;
        }
    };

    $unhealthyChecker = new class implements HealthCheckerInterface {
        public function check(): HealthCheckResult
        {
            return HealthCheckResult::unhealthy('Critical failure');
        }

        public function getName(): string
        {
            return 'Critical';
        }

        public function isCritical(): bool
        {
            return true;
        }
    };

    $this->monitor->register('healthy', $healthyChecker);
    $this->monitor->register('critical', $unhealthyChecker);

    $overall = $this->monitor->getOverallHealth();

    expect($overall->getStatus())->toBe(HealthStatus::UNHEALTHY);
});

it('returns unknown status when no checkers registered', function () {
    $overall = $this->monitor->getOverallHealth();

    expect($overall->getStatus())->toBe(HealthStatus::UNKNOWN);
    expect($overall->getMessage())->toContain('No health checks registered');
});

it('unregisters health checkers', function () {
    $checker = new class implements HealthCheckerInterface {
        public function check(): HealthCheckResult
        {
            return HealthCheckResult::healthy();
        }

        public function getName(): string
        {
            return 'Test';
        }

        public function isCritical(): bool
        {
            return true;
        }
    };

    $this->monitor->register('test', $checker);
    expect($this->monitor->getCheckers())->toHaveCount(1);

    expect($this->monitor->unregister('test'))->toBeTrue();
    expect($this->monitor->getCheckers())->toHaveCount(0);
});

it('returns false when unregistering non-existent checker', function () {
    expect($this->monitor->unregister('non-existent'))->toBeFalse();
});

it('clears all checkers', function () {
    $checker1 = new class implements HealthCheckerInterface {
        public function check(): HealthCheckResult
        {
            return HealthCheckResult::healthy();
        }

        public function getName(): string
        {
            return 'Test 1';
        }

        public function isCritical(): bool
        {
            return true;
        }
    };

    $checker2 = new class implements HealthCheckerInterface {
        public function check(): HealthCheckResult
        {
            return HealthCheckResult::healthy();
        }

        public function getName(): string
        {
            return 'Test 2';
        }

        public function isCritical(): bool
        {
            return true;
        }
    };

    $this->monitor->register('check1', $checker1);
    $this->monitor->register('check2', $checker2);
    expect($this->monitor->getCheckers())->toHaveCount(2);

    $this->monitor->clear();
    expect($this->monitor->getCheckers())->toHaveCount(0);
});

