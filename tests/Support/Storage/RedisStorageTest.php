<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\Storage\RedisStorage;

describe('RedisStorage', function () {
    beforeEach(function () {
        // Create a mock Redis client
        $this->mockRedis = new class {
            private array $data = [];
            private array $ttls = [];

            public function set(string $key, string $value): bool {
                $this->data[$key] = $value;
                return true;
            }

            public function setex(string $key, int $ttl, string $value): bool {
                $this->data[$key] = $value;
                $this->ttls[$key] = $ttl;
                return true;
            }

            public function get(string $key) {
                return $this->data[$key] ?? false;
            }

            public function del($key): int {
                if (is_array($key)) {
                    $count = 0;
                    foreach ($key as $k) {
                        if (isset($this->data[$k])) {
                            unset($this->data[$k]);
                            unset($this->ttls[$k]);
                            $count++;
                        }
                    }
                    return $count;
                }

                if (isset($this->data[$key])) {
                    unset($this->data[$key]);
                    unset($this->ttls[$key]);
                    return 1;
                }
                return 0;
            }

            public function keys(string $pattern): array {
                // Convert Redis pattern to regex (e.g., "test:chatbot:*" -> "test:chatbot:.*")
                $pattern = str_replace('\\*', '*', preg_quote($pattern, '/'));
                $regex = str_replace('*', '.*', $pattern);
                return array_filter(array_keys($this->data), function($key) use ($regex) {
                    return preg_match('/^' . $regex . '$/', $key);
                });
            }

            public function exists(string $key): int {
                return isset($this->data[$key]) ? 1 : 0;
            }

            public function clear(): void {
                $this->data = [];
                $this->ttls = [];
            }
        };

        $this->storage = new RedisStorage($this->mockRedis, 'test:chatbot:', 0);
    });

    afterEach(function () {
        $this->mockRedis->clear();
    });

    it('stores conversation data', function () {
        $sessionId = 'test-session';
        $data = [
            'messages' => [
                ['role' => 'user', 'content' => 'Hello'],
                ['role' => 'assistant', 'content' => 'Hi there!']
            ],
            'updated_at' => time()
        ];

        $result = $this->storage->store($sessionId, $data);

        expect($result)->toBeTrue();
    });

    it('retrieves stored conversation data', function () {
        $sessionId = 'test-session';
        $data = [
            'messages' => [
                ['role' => 'user', 'content' => 'Hello']
            ],
            'updated_at' => time()
        ];

        $this->storage->store($sessionId, $data);
        $retrieved = $this->storage->retrieve($sessionId);

        expect($retrieved)->toBe($data);
    });

    it('returns null for non-existent session', function () {
        $result = $this->storage->retrieve('non-existent');

        expect($result)->toBeNull();
    });

    it('uses key prefix', function () {
        $sessionId = 'test-session';
        $data = ['messages' => []];

        $this->storage->store($sessionId, $data);

        expect($this->storage->getKeyPrefix())->toBe('test:chatbot:');
        expect($this->mockRedis->exists('test:chatbot:test-session'))->toBe(1);
    });

    it('stores data with TTL when configured', function () {
        $storage = new RedisStorage($this->mockRedis, 'test:', 3600);
        $sessionId = 'test-session';
        $data = ['messages' => []];

        $result = $storage->store($sessionId, $data);

        expect($result)->toBeTrue();
        expect($storage->getTtl())->toBe(3600);
    });

    it('stores data without TTL when set to 0', function () {
        $sessionId = 'test-session';
        $data = ['messages' => []];

        $result = $this->storage->store($sessionId, $data);

        expect($result)->toBeTrue();
        expect($this->storage->getTtl())->toBe(0);
    });

    it('deletes conversation data', function () {
        $sessionId = 'test-session';
        $data = ['messages' => []];

        $this->storage->store($sessionId, $data);
        expect($this->storage->exists($sessionId))->toBeTrue();

        $result = $this->storage->delete($sessionId);

        expect($result)->toBeTrue();
        expect($this->storage->exists($sessionId))->toBeFalse();
    });

    it('returns false when deleting non-existent session', function () {
        $result = $this->storage->delete('non-existent');

        expect($result)->toBeFalse();
    });

    it('clears all conversation data', function () {
        $this->storage->store('session-1', ['messages' => []]);
        $this->storage->store('session-2', ['messages' => []]);
        $this->storage->store('session-3', ['messages' => []]);

        expect($this->storage->exists('session-1'))->toBeTrue();
        expect($this->storage->exists('session-2'))->toBeTrue();
        expect($this->storage->exists('session-3'))->toBeTrue();

        $result = $this->storage->clear();

        expect($result)->toBeTrue();
        expect($this->storage->exists('session-1'))->toBeFalse();
        expect($this->storage->exists('session-2'))->toBeFalse();
        expect($this->storage->exists('session-3'))->toBeFalse();
    });

    it('returns true when clearing with no data', function () {
        $result = $this->storage->clear();

        expect($result)->toBeTrue();
    });

    it('checks if conversation exists', function () {
        $sessionId = 'test-session';

        expect($this->storage->exists($sessionId))->toBeFalse();

        $this->storage->store($sessionId, ['messages' => []]);

        expect($this->storage->exists($sessionId))->toBeTrue();
    });

    it('handles JSON encoding failure gracefully', function () {
        $sessionId = 'test-session';
        // Create data with circular reference that cannot be JSON encoded
        $obj = new stdClass();
        $obj->self = $obj;
        $data = ['messages' => [$obj]];

        $result = $this->storage->store($sessionId, $data);

        expect($result)->toBeFalse();
    });

    it('handles invalid JSON when retrieving', function () {
        $sessionId = 'test-session';
        
        // Manually store invalid JSON
        $this->mockRedis->set('test:chatbot:' . $sessionId, 'invalid json{');

        $result = $this->storage->retrieve($sessionId);

        expect($result)->toBeNull();
    });

    it('handles special characters in session ID', function () {
        $sessionId = 'user@example.com_session#123';
        $data = ['messages' => []];

        $result = $this->storage->store($sessionId, $data);

        expect($result)->toBeTrue();
        expect($this->storage->exists($sessionId))->toBeTrue();
    });

    it('exposes Redis client', function () {
        expect($this->storage->getRedisClient())->toBe($this->mockRedis);
    });

    it('allows getting and setting TTL', function () {
        expect($this->storage->getTtl())->toBe(0);

        $this->storage->setTtl(7200);

        expect($this->storage->getTtl())->toBe(7200);
    });

    it('throws exception for invalid Redis client', function () {
        expect(fn() => new RedisStorage('not-a-redis-client'))
            ->toThrow(RuntimeException::class, 'Invalid Redis client');
    });

    it('handles clear with exception', function () {
        $failingRedis = new class {
            public function set() {}
            public function get() {}
            public function del() {}
            public function keys() { throw new Exception('Redis keys error'); }
        };

        $storage = new RedisStorage($failingRedis, 'phpunit:chat:');
        $result = $storage->clear();
        
        expect($result)->toBeFalse();
    });

    it('overwrites existing data on store', function () {
        $sessionId = 'test-session';
        $data1 = ['messages' => [['role' => 'user', 'content' => 'First']]];
        $data2 = ['messages' => [['role' => 'user', 'content' => 'Second']]];

        $this->storage->store($sessionId, $data1);
        $this->storage->store($sessionId, $data2);

        $retrieved = $this->storage->retrieve($sessionId);

        expect($retrieved)->toBe($data2);
    });

    it('throws exception for non-object Redis client', function () {
        new RedisStorage('not-an-object');
    })->throws(RuntimeException::class, 'Invalid Redis client');

    it('throws exception for invalid object without required methods', function () {
        $invalidClient = new stdClass();
        new RedisStorage($invalidClient);
    })->throws(RuntimeException::class, 'Invalid Redis client');
});
