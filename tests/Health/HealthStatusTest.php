<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Health\HealthStatus;

it('has correct enum values', function () {
    expect(HealthStatus::HEALTHY->value)->toBe('healthy');
    expect(HealthStatus::DEGRADED->value)->toBe('degraded');
    expect(HealthStatus::UNHEALTHY->value)->toBe('unhealthy');
    expect(HealthStatus::UNKNOWN->value)->toBe('unknown');
});

it('correctly identifies healthy status', function () {
    expect(HealthStatus::HEALTHY->isHealthy())->toBeTrue();
    expect(HealthStatus::DEGRADED->isHealthy())->toBeFalse();
    expect(HealthStatus::UNHEALTHY->isHealthy())->toBeFalse();
    expect(HealthStatus::UNKNOWN->isHealthy())->toBeFalse();
});

it('correctly identifies degraded status', function () {
    expect(HealthStatus::HEALTHY->isDegraded())->toBeFalse();
    expect(HealthStatus::DEGRADED->isDegraded())->toBeTrue();
    expect(HealthStatus::UNHEALTHY->isDegraded())->toBeFalse();
    expect(HealthStatus::UNKNOWN->isDegraded())->toBeFalse();
});

it('correctly identifies unhealthy status', function () {
    expect(HealthStatus::HEALTHY->isUnhealthy())->toBeFalse();
    expect(HealthStatus::DEGRADED->isUnhealthy())->toBeFalse();
    expect(HealthStatus::UNHEALTHY->isUnhealthy())->toBeTrue();
    expect(HealthStatus::UNKNOWN->isUnhealthy())->toBeFalse();
});

it('correctly identifies unknown status', function () {
    expect(HealthStatus::HEALTHY->isUnknown())->toBeFalse();
    expect(HealthStatus::DEGRADED->isUnknown())->toBeFalse();
    expect(HealthStatus::UNHEALTHY->isUnknown())->toBeFalse();
    expect(HealthStatus::UNKNOWN->isUnknown())->toBeTrue();
});

it('returns correct HTTP status codes', function () {
    expect(HealthStatus::HEALTHY->toHttpStatus())->toBe(200);
    expect(HealthStatus::DEGRADED->toHttpStatus())->toBe(200);
    expect(HealthStatus::UNHEALTHY->toHttpStatus())->toBe(503);
    expect(HealthStatus::UNKNOWN->toHttpStatus())->toBe(503);
});

