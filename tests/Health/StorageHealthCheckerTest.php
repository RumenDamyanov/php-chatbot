<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Health\StorageHealthChecker;
use Rumenx\PhpChatbot\Health\HealthStatus;
use Rumenx\PhpChatbot\Support\Storage\FileStorage;

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir() . '/chatbot_test_' . uniqid();
    mkdir($this->tempDir, 0777, true);
    $this->storage = new FileStorage($this->tempDir);
    $this->checker = new StorageHealthChecker($this->storage);
});

afterEach(function () {
    // Cleanup
    if (is_dir($this->tempDir)) {
        $files = glob($this->tempDir . '/*');
        foreach ($files as $file) {
            @unlink($file);
        }
        @rmdir($this->tempDir);
    }
});

it('performs health check on storage', function () {
    $result = $this->checker->check();

    // Check should complete and return a result
    expect($result)->toBeInstanceOf(\Rumenx\PhpChatbot\Health\HealthCheckResult::class);
    expect($result->getStatus())->toBeInstanceOf(\Rumenx\PhpChatbot\Health\HealthStatus::class);
    expect($result->getMessage())->toBeString();
});

it('has correct name', function () {
    expect($this->checker->getName())->toBe('Storage');
});

it('is not critical by default', function () {
    expect($this->checker->isCritical())->toBeFalse();
});

it('allows custom name and critical flag', function () {
    $checker = new StorageHealthChecker($this->storage, 'File Storage', true);
    
    expect($checker->getName())->toBe('File Storage');
    expect($checker->isCritical())->toBeTrue();
});

it('includes details in result', function () {
    $result = $this->checker->check();
    $details = $result->getDetails();

    expect($details)->toBeArray();
});

it('measures check duration', function () {
    $result = $this->checker->check();

    expect($result->getDuration())->toBeGreaterThan(0);
});

