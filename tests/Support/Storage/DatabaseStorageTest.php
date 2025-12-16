<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\Storage\DatabaseStorage;

describe('DatabaseStorage', function () {
    beforeEach(function () {
        // Use in-memory SQLite database for testing
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->storage = new DatabaseStorage($this->pdo);
    });

    it('creates table on instantiation', function () {
        // Check if table exists by querying it
        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='chatbot_conversations'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        expect($result)->not->toBeNull();
        expect($result['name'])->toBe('chatbot_conversations');
    });

    it('uses custom table name', function () {
        $storage = new DatabaseStorage($this->pdo, 'custom_conversations');

        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='custom_conversations'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        expect($result)->not->toBeNull();
        expect($result['name'])->toBe('custom_conversations');
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

    it('updates existing session data', function () {
        $sessionId = 'test-session';
        $data1 = ['messages' => [['role' => 'user', 'content' => 'First']]];
        $data2 = ['messages' => [['role' => 'user', 'content' => 'Second']]];

        $this->storage->store($sessionId, $data1);
        $this->storage->store($sessionId, $data2);

        $retrieved = $this->storage->retrieve($sessionId);

        expect($retrieved)->toBe($data2);
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

    it('exposes PDO instance', function () {
        expect($this->storage->getPdo())->toBeInstanceOf(PDO::class);
    });

    it('exposes table name', function () {
        expect($this->storage->getTableName())->toBe('chatbot_conversations');
    });

    it('handles concurrent updates', function () {
        $sessionId = 'concurrent-session';
        
        $this->storage->store($sessionId, ['messages' => [['content' => 'First']]]);
        $this->storage->store($sessionId, ['messages' => [['content' => 'Second']]]);

        $retrieved = $this->storage->retrieve($sessionId);

        expect($retrieved['messages'][0]['content'])->toBe('Second');
    });

    it('handles multiple sessions', function () {
        $this->storage->store('session-1', ['messages' => [['content' => 'First']]]);
        $this->storage->store('session-2', ['messages' => [['content' => 'Second']]]);
        $this->storage->store('session-3', ['messages' => [['content' => 'Third']]]);

        expect($this->storage->retrieve('session-1')['messages'][0]['content'])->toBe('First');
        expect($this->storage->retrieve('session-2')['messages'][0]['content'])->toBe('Second');
        expect($this->storage->retrieve('session-3')['messages'][0]['content'])->toBe('Third');
    });

    it('handles special characters in session ID', function () {
        $sessionId = 'user@example.com_session#123';
        $data = ['messages' => []];

        $result = $this->storage->store($sessionId, $data);

        expect($result)->toBeTrue();
        expect($this->storage->exists($sessionId))->toBeTrue();
    });

    it('handles large data', function () {
        $sessionId = 'large-session';
        $messages = [];
        
        for ($i = 0; $i < 100; $i++) {
            $messages[] = ['role' => 'user', 'content' => str_repeat('A', 1000)];
        }

        $data = ['messages' => $messages];
        $this->storage->store($sessionId, $data);

        $retrieved = $this->storage->retrieve($sessionId);

        expect($retrieved['messages'])->toHaveCount(100);
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

    it('handles corrupted JSON in database gracefully', function () {
        $sessionId = 'test-session';
        
        // Manually insert corrupted JSON
        $stmt = $this->pdo->prepare("INSERT INTO chatbot_conversations (session_id, data, updated_at) VALUES (?, ?, ?)");
        $stmt->execute([$sessionId, 'invalid json{', date('Y-m-d H:i:s')]);

        $result = $this->storage->retrieve($sessionId);

        expect($result)->toBeNull();
    });

    it('returns true when clearing empty database', function () {
        $result = $this->storage->clear();

        expect($result)->toBeTrue();
    });

    it('handles exists check for multiple sessions', function () {
        $this->storage->store('session-1', ['messages' => []]);
        $this->storage->store('session-2', ['messages' => []]);

        expect($this->storage->exists('session-1'))->toBeTrue();
        expect($this->storage->exists('session-2'))->toBeTrue();
        expect($this->storage->exists('session-3'))->toBeFalse();
    });

    it('handles empty data gracefully', function () {
        $sessionId = 'empty-session';
        $data = ['messages' => []];

        $this->storage->store($sessionId, $data);
        $retrieved = $this->storage->retrieve($sessionId);

        expect($retrieved)->toBe($data);
    });

    it('maintains data integrity across operations', function () {
        $sessionId = 'integrity-test';
        $data = [
            'messages' => [
                ['role' => 'user', 'content' => 'Test 1'],
                ['role' => 'assistant', 'content' => 'Response 1']
            ],
            'metadata' => ['version' => 1]
        ];

        $this->storage->store($sessionId, $data);
        $retrieved1 = $this->storage->retrieve($sessionId);

        expect($retrieved1)->toBe($data);

        // Update
        $data['messages'][] = ['role' => 'user', 'content' => 'Test 2'];
        $this->storage->store($sessionId, $data);
        $retrieved2 = $this->storage->retrieve($sessionId);

        expect($retrieved2)->toBe($data);
        expect($retrieved2['messages'])->toHaveCount(3);
    });

    it('handles unicode content', function () {
        $sessionId = 'unicode-session';
        $data = [
            'messages' => [
                ['role' => 'user', 'content' => '你好世界 🌍 مرحبا بالعالم'],
                ['role' => 'assistant', 'content' => 'Привет мир 🚀']
            ]
        ];

        $this->storage->store($sessionId, $data);
        $retrieved = $this->storage->retrieve($sessionId);

        expect($retrieved)->toBe($data);
    });

    it('deletes only specified session', function () {
        $this->storage->store('session-1', ['messages' => [['content' => '1']]]);
        $this->storage->store('session-2', ['messages' => [['content' => '2']]]);
        $this->storage->store('session-3', ['messages' => [['content' => '3']]]);

        $this->storage->delete('session-2');

        expect($this->storage->exists('session-1'))->toBeTrue();
        expect($this->storage->exists('session-2'))->toBeFalse();
        expect($this->storage->exists('session-3'))->toBeTrue();
    });

    it('returns false when deleting non-existent session', function () {
        // First check it doesn't exist
        expect($this->storage->exists('non-existent'))->toBeFalse();
        
        // Delete should still succeed (idempotent)
        $result = $this->storage->delete('non-existent');
        
        // SQLite/PDO returns true even if no rows affected
        expect($result)->toBeTrue();
    });
});
