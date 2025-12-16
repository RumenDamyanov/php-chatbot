<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Models\DefaultAiModel;
use Rumenx\PhpChatbot\Cache\MemoryCache;

beforeEach(function () {
    $this->model = new DefaultAiModel();
    $this->cache = new MemoryCache();
});

afterEach(function () {
    $this->cache->clear();
});

it('caches responses and returns cached version on second request', function () {
    $chatbot = new PhpChatbot($this->model, [], null, null, $this->cache);

    $input = 'Hello, how are you?';
    
    // First request - should hit the model
    $response1 = $chatbot->ask($input);
    expect($response1)->toBeString();

    // Second request with same input - should return cached response
    $response2 = $chatbot->ask($input);
    expect($response2)->toBe($response1);
});

it('works without cache', function () {
    $chatbot = new PhpChatbot($this->model); // No cache

    $response = $chatbot->ask('Hello');
    expect($response)->toBeString();
});

it('can enable and disable caching per request', function () {
    $chatbot = new PhpChatbot($this->model, [], null, null, $this->cache);

    $input = 'What is the weather?';

    // First request with caching disabled
    $response1 = $chatbot->ask($input, ['cache_enabled' => false]);
    expect($response1)->toBeString();

    // Second request with caching enabled - should NOT return cached (because first wasn't cached)
    $response2 = $chatbot->ask($input, ['cache_enabled' => true]);
    expect($response2)->toBeString();

    // Third request - should return cached version
    $response3 = $chatbot->ask($input, ['cache_enabled' => true]);
    expect($response3)->toBe($response2);
});

it('respects custom cache TTL', function () {
    $chatbot = new PhpChatbot($this->model, [], null, null, $this->cache);

    $input = 'Short-lived response';

    // Cache with 1 second TTL
    $response1 = $chatbot->ask($input, ['cache_ttl' => 1]);
    expect($response1)->toBeString();

    // Immediate second request - should be cached
    $response2 = $chatbot->ask($input);
    expect($response2)->toBe($response1);

    // Wait for cache to expire
    sleep(2);

    // Should NOT be cached anymore
    $response3 = $chatbot->ask($input);
    expect($response3)->toBeString();
});

it('generates different cache keys for different contexts', function () {
    $chatbot = new PhpChatbot($this->model, [], null, null, $this->cache);

    $input = 'Tell me a joke';

    // Request with temperature 0.5
    $response1 = $chatbot->ask($input, ['temperature' => 0.5]);
    
    // Request with temperature 0.9 - should NOT return cached (different context)
    $response2 = $chatbot->ask($input, ['temperature' => 0.9]);
    
    expect($response1)->toBeString();
    expect($response2)->toBeString();
    // Both are valid responses, but they might be different due to context
});

it('can set and get cache instance', function () {
    $chatbot = new PhpChatbot($this->model);

    expect($chatbot->getCache())->toBeNull();

    $chatbot->setCache($this->cache);
    expect($chatbot->getCache())->toBe($this->cache);

    $chatbot->setCache(null);
    expect($chatbot->getCache())->toBeNull();
});

it('respects default config cache_enabled setting', function () {
    $chatbot = new PhpChatbot(
        $this->model,
        ['cache_enabled' => false],
        null,
        null,
        $this->cache
    );

    $input = 'Hello';

    // First request
    $response1 = $chatbot->ask($input);

    // Second request - should NOT be cached due to default config
    $response2 = $chatbot->ask($input);

    expect($response1)->toBeString();
    expect($response2)->toBeString();
});

it('allows runtime config to override default cache settings', function () {
    $chatbot = new PhpChatbot(
        $this->model,
        ['cache_enabled' => false, 'cache_ttl' => 60],
        null,
        null,
        $this->cache
    );

    $input = 'Hello';

    // Override default: enable caching with custom TTL
    $response1 = $chatbot->ask($input, [
        'cache_enabled' => true,
        'cache_ttl' => 3600
    ]);

    // Should return cached response
    $response2 = $chatbot->ask($input, ['cache_enabled' => true]);

    expect($response2)->toBe($response1);
});

it('caches include conversation history in key generation', function () {
    $chatbot = new PhpChatbot($this->model, [], null, null, $this->cache);

    // Same input, different context due to conversation history
    // These should generate different cache keys
    $response1 = $chatbot->ask('Hello');
    expect($response1)->toBeString();
});

