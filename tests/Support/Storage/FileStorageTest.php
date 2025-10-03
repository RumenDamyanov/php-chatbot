<?php

use Rumenx\PhpChatbot\Support\Storage\FileStorage;

describe('FileStorage', function () {
    beforeEach(function () {
        $this->storagePath = sys_get_temp_dir() . '/chatbot_test_storage_' . uniqid();
        $this->storage = new FileStorage($this->storagePath);
    });

    afterEach(function () {
        // Clean up test files
        if (is_dir($this->storagePath)) {
            $files = glob($this->storagePath . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($this->storagePath);
        }
    });

    it('creates storage directory if it does not exist', function () {
        expect(is_dir($this->storagePath))->toBeTrue();
    });

    it('throws exception if directory cannot be created', function () {
        // Try to create storage in a non-writable location
        expect(fn() => new FileStorage('/root/chatbot_test'))
            ->toThrow(RuntimeException::class, 'Failed to create storage directory');
    })->skip(function () {
        // Skip if running as root
        return posix_getuid() === 0;
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

    it('deletes conversation data', function () {
        $sessionId = 'test-session';
        $data = ['messages' => []];

        $this->storage->store($sessionId, $data);
        expect($this->storage->exists($sessionId))->toBeTrue();

        $result = $this->storage->delete($sessionId);

        expect($result)->toBeTrue();
        expect($this->storage->exists($sessionId))->toBeFalse();
    });

    it('returns true when deleting non-existent session', function () {
        $result = $this->storage->delete('non-existent');

        expect($result)->toBeTrue();
    });

    it('clears all conversation data', function () {
        $this->storage->store('session-1', ['messages' => []]);
        $this->storage->store('session-2', ['messages' => []]);
        $this->storage->store('session-3', ['messages' => []]);

        $result = $this->storage->clear();

        expect($result)->toBeTrue();
        expect($this->storage->exists('session-1'))->toBeFalse();
        expect($this->storage->exists('session-2'))->toBeFalse();
        expect($this->storage->exists('session-3'))->toBeFalse();
    });

    it('checks if conversation exists', function () {
        $sessionId = 'test-session';

        expect($this->storage->exists($sessionId))->toBeFalse();

        $this->storage->store($sessionId, ['messages' => []]);

        expect($this->storage->exists($sessionId))->toBeTrue();
    });

    it('sanitizes session IDs to prevent directory traversal', function () {
        $sessionId = '../../../etc/passwd';
        $data = ['messages' => []];

        $this->storage->store($sessionId, $data);

        // File should be created in storage directory with sanitized name
        $files = glob($this->storagePath . '/*.json');
        expect($files)->toHaveCount(1);
        expect(basename($files[0]))->not->toContain('..');
    });

    it('handles concurrent access with file locking', function () {
        $sessionId = 'concurrent-session';
        $data = ['messages' => [['role' => 'user', 'content' => 'Test']]];

        // Store initial data
        $this->storage->store($sessionId, $data);

        // Simulate concurrent read
        $retrieved1 = $this->storage->retrieve($sessionId);
        $retrieved2 = $this->storage->retrieve($sessionId);

        expect($retrieved1)->toBe($data);
        expect($retrieved2)->toBe($data);
    });

    it('exposes storage path', function () {
        expect($this->storage->getStoragePath())->toBe($this->storagePath);
    });

    it('stores data as formatted JSON', function () {
        $sessionId = 'test-session';
        $data = ['messages' => [['role' => 'user', 'content' => 'Hello']]];

        $this->storage->store($sessionId, $data);

        $files = glob($this->storagePath . '/*.json');
        $content = file_get_contents($files[0]);

        // Check if JSON is pretty-printed
        expect($content)->toContain("\n");
        expect(json_decode($content, true))->toBe($data);
    });

    it('handles special characters in session ID', function () {
        $sessionId = 'user@example.com_session#123';
        $data = ['messages' => []];

        $result = $this->storage->store($sessionId, $data);

        expect($result)->toBeTrue();
        expect($this->storage->exists($sessionId))->toBeTrue();
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

    it('handles JSON encoding failure gracefully', function () {
        $sessionId = 'test-session';
        // Create data with circular reference that cannot be JSON encoded
        $obj = new stdClass();
        $obj->self = $obj;
        $data = ['messages' => [$obj]];

        $result = $this->storage->store($sessionId, $data);

        expect($result)->toBeFalse();
    });

    it('handles corrupted JSON file gracefully', function () {
        $sessionId = 'test-session';
        
        // Create a corrupted JSON file manually
        $filePath = $this->storagePath . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $sessionId) . '.json';
        file_put_contents($filePath, 'invalid json{');

        $result = $this->storage->retrieve($sessionId);

        expect($result)->toBeNull();
    });

    it('handles empty storage directory on clear', function () {
        $result = $this->storage->clear();

        expect($result)->toBeTrue();
    });

    it('returns true for exists on newly stored data', function () {
        $sessionId = 'test-session';
        
        expect($this->storage->exists($sessionId))->toBeFalse();
        
        $this->storage->store($sessionId, ['messages' => []]);
        
        expect($this->storage->exists($sessionId))->toBeTrue();
    });

    it('sanitizes multiple special characters', function () {
        $sessionId = '../../../etc/passwd/../test@#$%';
        $data = ['messages' => [['role' => 'user', 'content' => 'Test']]];

        $this->storage->store($sessionId, $data);

        $files = glob($this->storagePath . '/*.json');
        expect($files)->toHaveCount(1);
        
        $filename = basename($files[0]);
        expect($filename)->not->toContain('..');
        expect($filename)->not->toContain('/');
        expect($filename)->not->toContain('@');
    });
});
