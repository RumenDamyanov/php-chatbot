<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Health\ModelHealthChecker;
use Rumenx\PhpChatbot\Health\HealthStatus;
use Rumenx\PhpChatbot\Models\DefaultAiModel;

beforeEach(function () {
    $this->model = new DefaultAiModel();
    $this->checker = new ModelHealthChecker($this->model);
});

it('passes health check for working model', function () {
    $result = $this->checker->check();

    expect($result->getStatus())->toBe(HealthStatus::HEALTHY);
    expect($result->getMessage())->toContain('responding normally');
    expect($result->getDuration())->toBeGreaterThan(0);
});

it('has correct name', function () {
    expect($this->checker->getName())->toBe('AI Model');
});

it('is critical by default', function () {
    expect($this->checker->isCritical())->toBeTrue();
});

it('allows custom name', function () {
    $checker = new ModelHealthChecker($this->model, 'Custom Model');
    expect($checker->getName())->toBe('Custom Model');
});

it('allows non-critical configuration', function () {
    $checker = new ModelHealthChecker($this->model, 'Model', false);
    expect($checker->isCritical())->toBeFalse();
});

it('includes model information in result', function () {
    $result = $this->checker->check();
    $details = $result->getDetails();

    expect($details)->toHaveKey('model');
    expect($details)->toHaveKey('response_time');
});

