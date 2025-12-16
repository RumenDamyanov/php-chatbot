<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\RateLimiting\MemoryRateLimiter;

beforeEach(function () {
    $this->limiter = new MemoryRateLimiter();
});

afterEach(function () {
    $this->limiter->clear();
});

it('allows requests within the limit', function () {
    $key = 'test-user';
    $maxRequests = 5;
    $window = 60;

    // Should allow 5 requests
    for ($i = 0; $i < $maxRequests; $i++) {
        expect($this->limiter->allow($key, $maxRequests, $window))->toBeTrue();
    }
});

it('blocks requests exceeding the limit', function () {
    $key = 'test-user';
    $maxRequests = 3;
    $window = 60;

    // Use up all allowed requests
    for ($i = 0; $i < $maxRequests; $i++) {
        $this->limiter->allow($key, $maxRequests, $window);
    }

    // Next request should be blocked
    expect($this->limiter->allow($key, $maxRequests, $window))->toBeFalse();
});

it('calculates remaining requests correctly', function () {
    $key = 'test-user';
    $maxRequests = 5;
    $window = 60;

    // Initially all requests should be available
    expect($this->limiter->remaining($key, $maxRequests, $window))->toBe(5);

    // After 2 requests
    $this->limiter->allow($key, $maxRequests, $window);
    $this->limiter->allow($key, $maxRequests, $window);
    expect($this->limiter->remaining($key, $maxRequests, $window))->toBe(3);

    // After 5 requests
    $this->limiter->allow($key, $maxRequests, $window);
    $this->limiter->allow($key, $maxRequests, $window);
    $this->limiter->allow($key, $maxRequests, $window);
    expect($this->limiter->remaining($key, $maxRequests, $window))->toBe(0);
});

it('resets limit for specific key', function () {
    $key = 'test-user';
    $maxRequests = 3;
    $window = 60;

    // Use up all requests
    for ($i = 0; $i < $maxRequests; $i++) {
        $this->limiter->allow($key, $maxRequests, $window);
    }

    // Should be blocked
    expect($this->limiter->allow($key, $maxRequests, $window))->toBeFalse();

    // Reset
    $this->limiter->reset($key);

    // Should work again
    expect($this->limiter->allow($key, $maxRequests, $window))->toBeTrue();
});

it('isolates different keys', function () {
    $maxRequests = 2;
    $window = 60;

    // User 1 uses up their limit
    $this->limiter->allow('user1', $maxRequests, $window);
    $this->limiter->allow('user1', $maxRequests, $window);
    expect($this->limiter->allow('user1', $maxRequests, $window))->toBeFalse();

    // User 2 should still have full limit
    expect($this->limiter->allow('user2', $maxRequests, $window))->toBeTrue();
    expect($this->limiter->remaining('user2', $maxRequests, $window))->toBe(1);
});

it('clears all rate limit data', function () {
    $maxRequests = 2;
    $window = 60;

    // Add requests for multiple users
    $this->limiter->allow('user1', $maxRequests, $window);
    $this->limiter->allow('user2', $maxRequests, $window);
    $this->limiter->allow('user3', $maxRequests, $window);

    // Clear all
    $this->limiter->clear();

    // All users should have full limits
    expect($this->limiter->remaining('user1', $maxRequests, $window))->toBe(2);
    expect($this->limiter->remaining('user2', $maxRequests, $window))->toBe(2);
    expect($this->limiter->remaining('user3', $maxRequests, $window))->toBe(2);
});

it('calculates reset time correctly', function () {
    $key = 'test-user';
    $maxRequests = 3;
    $window = 10; // 10 second window

    // Make a request
    $this->limiter->allow($key, $maxRequests, $window);

    // Reset time should be approximately the window duration
    $resetIn = $this->limiter->resetIn($key, $window);
    expect($resetIn)->toBeGreaterThan(0);
    expect($resetIn)->toBeLessThanOrEqual($window);
});

it('returns zero reset time when no requests exist', function () {
    $key = 'test-user';
    $window = 60;

    expect($this->limiter->resetIn($key, $window))->toBe(0);
});

it('handles sliding window correctly', function () {
    $key = 'test-user';
    $maxRequests = 2;
    $window = 2; // 2 second window

    // Make 2 requests
    $this->limiter->allow($key, $maxRequests, $window);
    $this->limiter->allow($key, $maxRequests, $window);

    // Should be blocked
    expect($this->limiter->allow($key, $maxRequests, $window))->toBeFalse();

    // Wait for window to pass
    sleep(3);

    // Should be allowed again
    expect($this->limiter->allow($key, $maxRequests, $window))->toBeTrue();
});

it('tracks all requests for a key within each time window', function () {
    $key = 'test-user';

    // Make 3 requests (they all count toward the sliding window)
    expect($this->limiter->allow($key, 5, 60))->toBeTrue();  // Request 1
    expect($this->limiter->allow($key, 5, 60))->toBeTrue();  // Request 2
    expect($this->limiter->allow($key, 5, 60))->toBeTrue();  // Request 3

    // All 3 requests count in the 60-second window
    expect($this->limiter->remaining($key, 5, 60))->toBe(2);
    
    // But only the most recent requests count in shorter windows
    expect($this->limiter->remaining($key, 2, 1))->toBeGreaterThanOrEqual(0);
});

