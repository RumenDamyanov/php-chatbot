<?php

use Rumenx\PhpChatbot\Support\ConversationMemory;
use Rumenx\PhpChatbot\Support\Storage\FileStorage;

describe('ConversationMemory', function () {
    beforeEach(function () {
        $this->storagePath = sys_get_temp_dir() . '/chatbot_test_memory_' . uniqid();
        $this->storage = new FileStorage($this->storagePath);
        $this->memory = new ConversationMemory($this->storage, 10, true);
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

    it('adds messages to conversation history', function () {
        $sessionId = 'test-session-1';
        
        $this->memory->addMessage($sessionId, 'user', 'Hello');
        $this->memory->addMessage($sessionId, 'assistant', 'Hi there!');

        $history = $this->memory->getHistory($sessionId);

        expect($history)->toHaveCount(2);
        expect($history[0]['role'])->toBe('user');
        expect($history[0]['content'])->toBe('Hello');
        expect($history[1]['role'])->toBe('assistant');
        expect($history[1]['content'])->toBe('Hi there!');
    });

    it('respects max history limit', function () {
        $sessionId = 'test-session-2';
        $this->memory->setMaxHistory(5);

        // Add 10 messages
        for ($i = 1; $i <= 10; $i++) {
            $this->memory->addMessage($sessionId, 'user', "Message $i");
        }

        $history = $this->memory->getHistory($sessionId);

        expect($history)->toHaveCount(5);
        expect($history[0]['content'])->toBe('Message 6');
        expect($history[4]['content'])->toBe('Message 10');
    });

    it('allows unlimited history when maxHistory is 0', function () {
        $sessionId = 'test-session-3';
        $this->memory->setMaxHistory(0);

        // Add 20 messages
        for ($i = 1; $i <= 20; $i++) {
            $this->memory->addMessage($sessionId, 'user', "Message $i");
        }

        $history = $this->memory->getHistory($sessionId);

        expect($history)->toHaveCount(20);
    });

    it('isolates conversations by session ID', function () {
        $this->memory->addMessage('session-1', 'user', 'Hello from session 1');
        $this->memory->addMessage('session-2', 'user', 'Hello from session 2');

        $history1 = $this->memory->getHistory('session-1');
        $history2 = $this->memory->getHistory('session-2');

        expect($history1)->toHaveCount(1);
        expect($history2)->toHaveCount(1);
        expect($history1[0]['content'])->toBe('Hello from session 1');
        expect($history2[0]['content'])->toBe('Hello from session 2');
    });

    it('clears history for a specific session', function () {
        $this->memory->addMessage('session-1', 'user', 'Message 1');
        $this->memory->addMessage('session-2', 'user', 'Message 2');

        $this->memory->clearHistory('session-1');

        expect($this->memory->getHistory('session-1'))->toBeEmpty();
        expect($this->memory->getHistory('session-2'))->toHaveCount(1);
    });

    it('clears all conversation histories', function () {
        $this->memory->addMessage('session-1', 'user', 'Message 1');
        $this->memory->addMessage('session-2', 'user', 'Message 2');
        $this->memory->addMessage('session-3', 'user', 'Message 3');

        $this->memory->clearAll();

        expect($this->memory->getHistory('session-1'))->toBeEmpty();
        expect($this->memory->getHistory('session-2'))->toBeEmpty();
        expect($this->memory->getHistory('session-3'))->toBeEmpty();
    });

    it('checks if history exists for a session', function () {
        expect($this->memory->hasHistory('session-1'))->toBeFalse();

        $this->memory->addMessage('session-1', 'user', 'Hello');

        expect($this->memory->hasHistory('session-1'))->toBeTrue();
    });

    it('returns formatted history for AI models', function () {
        $sessionId = 'test-session-4';
        
        $this->memory->addMessage($sessionId, 'user', 'Hello');
        $this->memory->addMessage($sessionId, 'assistant', 'Hi there!');
        $this->memory->addMessage($sessionId, 'user', 'How are you?');

        $formatted = $this->memory->getFormattedHistory($sessionId);

        expect($formatted)->toHaveCount(3);
        expect($formatted[0])->toHaveKeys(['role', 'content']);
        expect($formatted[0])->not->toHaveKey('timestamp');
        expect($formatted[0]['role'])->toBe('user');
        expect($formatted[0]['content'])->toBe('Hello');
    });

    it('counts messages in a session', function () {
        $sessionId = 'test-session-5';

        expect($this->memory->getMessageCount($sessionId))->toBe(0);

        $this->memory->addMessage($sessionId, 'user', 'Message 1');
        $this->memory->addMessage($sessionId, 'assistant', 'Response 1');

        expect($this->memory->getMessageCount($sessionId))->toBe(2);
    });

    it('can be disabled', function () {
        $sessionId = 'test-session-6';
        
        $this->memory->setEnabled(false);
        expect($this->memory->isEnabled())->toBeFalse();

        $this->memory->addMessage($sessionId, 'user', 'Hello');

        expect($this->memory->getHistory($sessionId))->toBeEmpty();
    });

    it('can be re-enabled', function () {
        $sessionId = 'test-session-7';
        
        $this->memory->setEnabled(false);
        $this->memory->addMessage($sessionId, 'user', 'Hello');
        expect($this->memory->getHistory($sessionId))->toBeEmpty();

        $this->memory->setEnabled(true);
        $this->memory->addMessage($sessionId, 'user', 'Hello again');

        expect($this->memory->getHistory($sessionId))->toHaveCount(1);
    });

    it('returns empty history for non-existent session', function () {
        expect($this->memory->getHistory('non-existent'))->toBeEmpty();
    });

    it('includes timestamps in stored messages', function () {
        $sessionId = 'test-session-8';
        
        $this->memory->addMessage($sessionId, 'user', 'Hello');
        
        $history = $this->memory->getHistory($sessionId);

        expect($history[0])->toHaveKey('timestamp');
        expect($history[0]['timestamp'])->toBeInt();
    });

    it('exposes storage backend', function () {
        expect($this->memory->getStorage())->toBeInstanceOf(FileStorage::class);
    });

    it('allows getting and setting maxHistory', function () {
        expect($this->memory->getMaxHistory())->toBe(10);

        $this->memory->setMaxHistory(20);

        expect($this->memory->getMaxHistory())->toBe(20);
    });
});
