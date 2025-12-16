<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Models\DefaultAiModel;
use Rumenx\PhpChatbot\RateLimiting\MemoryRateLimiter;
use Rumenx\PhpChatbot\RateLimiting\RateLimitException;

beforeEach(function () {
    $this->model = new DefaultAiModel();
    $this->limiter = new MemoryRateLimiter();
});

afterEach(function () {
    $this->limiter->clear();
});

it('allows requests within rate limit', function () {
    $chatbot = new PhpChatbot($this->model, [], null, $this->limiter);

    $config = [
        'rate_limit_key' => 'test-user',
        'rate_limit_max' => 3,
        'rate_limit_window' => 60,
    ];

    // Should allow 3 requests
    expect($chatbot->ask('Hello 1', $config))->toBeString();
    expect($chatbot->ask('Hello 2', $config))->toBeString();
    expect($chatbot->ask('Hello 3', $config))->toBeString();
});

it('throws exception when rate limit exceeded', function () {
    $chatbot = new PhpChatbot($this->model, [], null, $this->limiter);

    $config = [
        'rate_limit_key' => 'test-user',
        'rate_limit_max' => 2,
        'rate_limit_window' => 60,
    ];

    // Use up the limit
    $chatbot->ask('Hello 1', $config);
    $chatbot->ask('Hello 2', $config);

    // This should throw
    expect(fn() => $chatbot->ask('Hello 3', $config))
        ->toThrow(RateLimitException::class);
});

it('uses sessionId as default rate limit key', function () {
    $chatbot = new PhpChatbot($this->model, [], null, $this->limiter);

    $config = [
        'sessionId' => 'session-123',
        'rate_limit_max' => 2,
        'rate_limit_window' => 60,
    ];

    // Use up the limit
    $chatbot->ask('Hello 1', $config);
    $chatbot->ask('Hello 2', $config);

    // Should be blocked
    expect(fn() => $chatbot->ask('Hello 3', $config))
        ->toThrow(RateLimitException::class);
});

it('uses default key when no session or explicit key provided', function () {
    $chatbot = new PhpChatbot($this->model, [], null, $this->limiter);

    $config = [
        'rate_limit_max' => 2,
        'rate_limit_window' => 60,
    ];

    // Use up the limit
    $chatbot->ask('Hello 1', $config);
    $chatbot->ask('Hello 2', $config);

    // Should be blocked
    expect(fn() => $chatbot->ask('Hello 3', $config))
        ->toThrow(RateLimitException::class);
});

it('isolates different users', function () {
    $chatbot = new PhpChatbot($this->model, [], null, $this->limiter);

    $user1Config = [
        'rate_limit_key' => 'user-1',
        'rate_limit_max' => 2,
        'rate_limit_window' => 60,
    ];

    $user2Config = [
        'rate_limit_key' => 'user-2',
        'rate_limit_max' => 2,
        'rate_limit_window' => 60,
    ];

    // User 1 uses their limit
    $chatbot->ask('Hello 1', $user1Config);
    $chatbot->ask('Hello 2', $user1Config);
    expect(fn() => $chatbot->ask('Hello 3', $user1Config))
        ->toThrow(RateLimitException::class);

    // User 2 should still be able to make requests
    expect($chatbot->ask('Hello 1', $user2Config))->toBeString();
    expect($chatbot->ask('Hello 2', $user2Config))->toBeString();
});

it('works without rate limiter', function () {
    $chatbot = new PhpChatbot($this->model); // No rate limiter

    // Should allow unlimited requests
    for ($i = 0; $i < 10; $i++) {
        expect($chatbot->ask("Hello $i"))->toBeString();
    }
});

it('can set and get rate limiter', function () {
    $chatbot = new PhpChatbot($this->model);

    expect($chatbot->getRateLimiter())->toBeNull();

    $chatbot->setRateLimiter($this->limiter);
    expect($chatbot->getRateLimiter())->toBe($this->limiter);

    $chatbot->setRateLimiter(null);
    expect($chatbot->getRateLimiter())->toBeNull();
});

it('respects default config values', function () {
    $chatbot = new PhpChatbot(
        $this->model,
        [
            'rate_limit_max' => 2,
            'rate_limit_window' => 60,
        ],
        null,
        $this->limiter
    );

    // Should use defaults from constructor config
    $chatbot->ask('Hello 1');
    $chatbot->ask('Hello 2');

    expect(fn() => $chatbot->ask('Hello 3'))
        ->toThrow(RateLimitException::class);
});

it('allows runtime config to override defaults', function () {
    $chatbot = new PhpChatbot(
        $this->model,
        [
            'rate_limit_max' => 2,
            'rate_limit_window' => 60,
        ],
        null,
        $this->limiter
    );

    // Override with higher limit
    $config = [
        'rate_limit_max' => 5,
        'rate_limit_window' => 60,
    ];

    // Should allow 5 requests with overridden config
    for ($i = 0; $i < 5; $i++) {
        expect($chatbot->ask("Hello $i", $config))->toBeString();
    }

    expect(fn() => $chatbot->ask('Hello 6', $config))
        ->toThrow(RateLimitException::class);
});

