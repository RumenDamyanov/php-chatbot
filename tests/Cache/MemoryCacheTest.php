<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Cache\MemoryCache;
use Rumenx\PhpChatbot\Support\ChatResponse;

beforeEach(function () {
    $this->cache = new MemoryCache();
});

afterEach(function () {
    $this->cache->clear();
});

it('stores and retrieves cached responses', function () {
    $response = ChatResponse::fromString('Hello, world!', 'test-model');
    $key = 'test-key';

    expect($this->cache->set($key, $response))->toBeTrue();
    expect($this->cache->get($key))->toBe($response);
});

it('returns null for non-existent keys', function () {
    expect($this->cache->get('non-existent'))->toBeNull();
});

it('respects TTL and expires entries', function () {
    $response = ChatResponse::fromString('Temporary', 'test-model');
    $key = 'temp-key';

    // Store with 1 second TTL
    $this->cache->set($key, $response, 1);
    expect($this->cache->get($key))->toBe($response);

    // Wait for expiration
    sleep(2);
    expect($this->cache->get($key))->toBeNull();
});

it('supports zero TTL for permanent caching', function () {
    $response = ChatResponse::fromString('Permanent', 'test-model');
    $key = 'permanent-key';

    $this->cache->set($key, $response, 0); // 0 = no expiration
    expect($this->cache->get($key))->toBe($response);
});

it('checks if key exists', function () {
    $response = ChatResponse::fromString('Test', 'test-model');
    $key = 'test-key';

    expect($this->cache->has($key))->toBeFalse();
    
    $this->cache->set($key, $response);
    expect($this->cache->has($key))->toBeTrue();
});

it('deletes cached entries', function () {
    $response = ChatResponse::fromString('Test', 'test-model');
    $key = 'test-key';

    $this->cache->set($key, $response);
    expect($this->cache->has($key))->toBeTrue();

    expect($this->cache->delete($key))->toBeTrue();
    expect($this->cache->has($key))->toBeFalse();
});

it('returns false when deleting non-existent key', function () {
    expect($this->cache->delete('non-existent'))->toBeFalse();
});

it('clears all cached entries', function () {
    $this->cache->set('key1', ChatResponse::fromString('Test 1', 'model'));
    $this->cache->set('key2', ChatResponse::fromString('Test 2', 'model'));
    $this->cache->set('key3', ChatResponse::fromString('Test 3', 'model'));

    expect($this->cache->clear())->toBeTrue();
    
    expect($this->cache->has('key1'))->toBeFalse();
    expect($this->cache->has('key2'))->toBeFalse();
    expect($this->cache->has('key3'))->toBeFalse();
});

it('generates deterministic cache keys', function () {
    $input = 'What is the weather?';
    $context = [
        'model' => 'gpt-4',
        'temperature' => 0.7,
        'max_tokens' => 256,
    ];

    $key1 = $this->cache->generateKey($input, $context);
    $key2 = $this->cache->generateKey($input, $context);

    expect($key1)->toBe($key2);
    expect($key1)->toBeString();
    expect($key1)->toStartWith('chatbot:');
});

it('generates different keys for different inputs', function () {
    $context = ['model' => 'gpt-4'];

    $key1 = $this->cache->generateKey('Hello', $context);
    $key2 = $this->cache->generateKey('Goodbye', $context);

    expect($key1)->not->toBe($key2);
});

it('generates different keys for different contexts', function () {
    $input = 'Hello';

    $key1 = $this->cache->generateKey($input, ['temperature' => 0.7]);
    $key2 = $this->cache->generateKey($input, ['temperature' => 0.9]);

    expect($key1)->not->toBe($key2);
});

it('includes custom cache key components', function () {
    $input = 'Hello';
    $context1 = [
        'cache_key_components' => ['user_id' => 123]
    ];
    $context2 = [
        'cache_key_components' => ['user_id' => 456]
    ];

    $key1 = $this->cache->generateKey($input, $context1);
    $key2 = $this->cache->generateKey($input, $context2);

    expect($key1)->not->toBe($key2);
});

it('provides cache statistics', function () {
    $this->cache->set('key1', ChatResponse::fromString('Test 1', 'model'), 3600);
    $this->cache->set('key2', ChatResponse::fromString('Test 2', 'model'), 1);
    
    sleep(2); // Expire key2

    $stats = $this->cache->getStats();
    
    expect($stats)->toBeArray();
    expect($stats)->toHaveKeys(['count', 'expired', 'valid']);
    expect($stats['count'])->toBe(2);
});

