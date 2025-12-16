<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\RateLimiting\RateLimitException;

it('creates exception with correct properties', function () {
    $exception = new RateLimitException(
        key: 'test-user',
        maxRequests: 10,
        windowSeconds: 60,
        resetIn: 45
    );

    expect($exception->getKey())->toBe('test-user');
    expect($exception->getMaxRequests())->toBe(10);
    expect($exception->getWindowSeconds())->toBe(60);
    expect($exception->getResetIn())->toBe(45);
    expect($exception->getCode())->toBe(429); // HTTP 429 Too Many Requests
});

it('generates helpful error message', function () {
    $exception = new RateLimitException(
        key: 'user-123',
        maxRequests: 5,
        windowSeconds: 60,
        resetIn: 30
    );

    $message = $exception->getMessage();
    expect($message)->toContain('user-123');
    expect($message)->toContain('5 requests');
    expect($message)->toContain('60 seconds');
    expect($message)->toContain('30 seconds');
});

it('calculates reset timestamp correctly', function () {
    $resetIn = 30;
    $exception = new RateLimitException(
        key: 'test',
        maxRequests: 10,
        windowSeconds: 60,
        resetIn: $resetIn
    );

    $expectedResetAt = time() + $resetIn;
    $actualResetAt = $exception->getResetAt();

    // Allow 2 seconds tolerance for test execution time
    expect($actualResetAt)->toBeGreaterThanOrEqual($expectedResetAt - 2);
    expect($actualResetAt)->toBeLessThanOrEqual($expectedResetAt + 2);
});

it('extends PhpChatbotException', function () {
    $exception = new RateLimitException(
        key: 'test',
        maxRequests: 10,
        windowSeconds: 60,
        resetIn: 30
    );

    expect($exception)->toBeInstanceOf(\Rumenx\PhpChatbot\Exceptions\PhpChatbotException::class);
    expect($exception)->toBeInstanceOf(\Exception::class);
});

it('supports exception chaining', function () {
    $previous = new \RuntimeException('Previous error');
    $exception = new RateLimitException(
        key: 'test',
        maxRequests: 10,
        windowSeconds: 60,
        resetIn: 30,
        previous: $previous
    );

    expect($exception->getPrevious())->toBe($previous);
});

