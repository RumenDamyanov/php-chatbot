<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Health\HealthCheckResult;
use Rumenx\PhpChatbot\Health\HealthStatus;

it('creates healthy result', function () {
    $result = HealthCheckResult::healthy('All systems operational');

    expect($result->getStatus())->toBe(HealthStatus::HEALTHY);
    expect($result->getMessage())->toBe('All systems operational');
    expect($result->isHealthy())->toBeTrue();
});

it('creates degraded result', function () {
    $result = HealthCheckResult::degraded('Performance is degraded', ['cpu' => '90%']);

    expect($result->getStatus())->toBe(HealthStatus::DEGRADED);
    expect($result->getMessage())->toBe('Performance is degraded');
    expect($result->isDegraded())->toBeTrue();
    expect($result->getDetails())->toBe(['cpu' => '90%']);
});

it('creates unhealthy result', function () {
    $result = HealthCheckResult::unhealthy('Service unavailable');

    expect($result->getStatus())->toBe(HealthStatus::UNHEALTHY);
    expect($result->getMessage())->toBe('Service unavailable');
    expect($result->isUnhealthy())->toBeTrue();
});

it('creates unknown result', function () {
    $result = HealthCheckResult::unknown();

    expect($result->getStatus())->toBe(HealthStatus::UNKNOWN);
    expect($result->getMessage())->toBe('Status unknown');
});

it('includes timestamp', function () {
    $result = HealthCheckResult::healthy();

    expect($result->getCheckedAt())->toBeInstanceOf(\DateTimeImmutable::class);
});

it('converts to array', function () {
    $result = HealthCheckResult::healthy('OK', ['test' => 'value']);

    $array = $result->toArray();

    expect($array)->toBeArray();
    expect($array)->toHaveKeys(['status', 'message', 'details', 'duration', 'checked_at']);
    expect($array['status'])->toBe('healthy');
    expect($array['message'])->toBe('OK');
    expect($array['details'])->toBe(['test' => 'value']);
});

it('converts to JSON', function () {
    $result = HealthCheckResult::healthy('OK');

    $json = $result->toJson();

    expect($json)->toBeJson();
    expect($json)->toContain('"status": "healthy"');
    expect($json)->toContain('"message": "OK"');
});

it('stores duration', function () {
    $result = new HealthCheckResult(
        HealthStatus::HEALTHY,
        'OK',
        [],
        0.123,
        new \DateTimeImmutable()
    );

    expect($result->getDuration())->toBe(0.123);
});

