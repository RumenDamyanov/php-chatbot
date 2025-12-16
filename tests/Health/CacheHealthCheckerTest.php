<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Health\CacheHealthChecker;
use Rumenx\PhpChatbot\Health\HealthStatus;
use Rumenx\PhpChatbot\Cache\MemoryCache;

beforeEach(function () {
    $this->cache = new MemoryCache();
    $this->checker = new CacheHealthChecker($this->cache);
});

afterEach(function () {
    $this->cache->clear();
});

it('passes health check for working cache', function () {
    $result = $this->checker->check();

    expect($result->getStatus())->toBe(HealthStatus::HEALTHY);
    expect($result->getMessage())->toContain('working normally');
});

it('has correct name', function () {
    expect($this->checker->getName())->toBe('Cache');
});

it('is not critical by default', function () {
    expect($this->checker->isCritical())->toBeFalse();
});

it('allows custom name and critical flag', function () {
    $checker = new CacheHealthChecker($this->cache, 'Redis Cache', true);
    
    expect($checker->getName())->toBe('Redis Cache');
    expect($checker->isCritical())->toBeTrue();
});

it('tests all cache operations', function () {
    $result = $this->checker->check();
    $details = $result->getDetails();

    expect($details)->toHaveKey('operations_tested');
    expect($details['operations_tested'])->toBe(['set', 'get', 'delete']);
});

it('includes performance metrics in result', function () {
    $result = $this->checker->check();
    $details = $result->getDetails();

    expect($details)->toHaveKey('response_time');
    expect($details['response_time'])->toBeGreaterThanOrEqual(0);
});

it('measures check duration', function () {
    $result = $this->checker->check();

    expect($result->getDuration())->toBeGreaterThan(0);
});

