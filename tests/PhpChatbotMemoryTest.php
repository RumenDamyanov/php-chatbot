<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Support\ConversationMemory;
use Rumenx\PhpChatbot\Support\Storage\FileStorage;
use Rumenx\PhpChatbot\Models\DefaultAiModel;

describe('PhpChatbot with ConversationMemory', function () {
    beforeEach(function () {
        $this->storagePath = sys_get_temp_dir() . '/chatbot_test_integration_' . uniqid();
        $this->storage = new FileStorage($this->storagePath);
        $this->memory = new ConversationMemory($this->storage, 10, true);
        $this->model = new DefaultAiModel('test-api-key');
        $this->chatbot = new PhpChatbot($this->model, [], $this->memory);
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

    it('stores conversation history when asking questions', function () {
        $sessionId = 'test-session';
        
        $this->chatbot->ask('Hello', ['sessionId' => $sessionId]);

        $history = $this->chatbot->getConversationHistory($sessionId);

        expect($history)->toHaveCount(2); // user + assistant
        expect($history[0]['role'])->toBe('user');
        expect($history[0]['content'])->toBe('Hello');
        expect($history[1]['role'])->toBe('assistant');
    });

    it('maintains conversation context across multiple questions', function () {
        $sessionId = 'test-session';
        
        $this->chatbot->ask('Hello', ['sessionId' => $sessionId]);
        $this->chatbot->ask('How are you?', ['sessionId' => $sessionId]);

        $history = $this->chatbot->getConversationHistory($sessionId);

        expect($history)->toHaveCount(4); // 2 user + 2 assistant
        expect($history[0]['content'])->toBe('Hello');
        expect($history[2]['content'])->toBe('How are you?');
    });

    it('isolates conversations by session ID', function () {
        $this->chatbot->ask('Hello from session 1', ['sessionId' => 'session-1']);
        $this->chatbot->ask('Hello from session 2', ['sessionId' => 'session-2']);

        $history1 = $this->chatbot->getConversationHistory('session-1');
        $history2 = $this->chatbot->getConversationHistory('session-2');

        expect($history1[0]['content'])->toBe('Hello from session 1');
        expect($history2[0]['content'])->toBe('Hello from session 2');
    });

    it('clears conversation history', function () {
        $sessionId = 'test-session';
        
        $this->chatbot->ask('Hello', ['sessionId' => $sessionId]);
        expect($this->chatbot->getConversationHistory($sessionId))->not->toBeEmpty();

        $result = $this->chatbot->clearConversationHistory($sessionId);

        expect($result)->toBeTrue();
        expect($this->chatbot->getConversationHistory($sessionId))->toBeEmpty();
    });

    it('works without sessionId (no memory)', function () {
        $response = $this->chatbot->ask('Hello');

        expect($response)->toBeString();
    });

    it('returns empty history for non-existent session', function () {
        $history = $this->chatbot->getConversationHistory('non-existent');

        expect($history)->toBeEmpty();
    });

    it('allows getting memory manager', function () {
        expect($this->chatbot->getMemory())->toBeInstanceOf(ConversationMemory::class);
    });

    it('allows setting memory manager', function () {
        $newMemory = new ConversationMemory($this->storage, 5);
        
        $this->chatbot->setMemory($newMemory);

        expect($this->chatbot->getMemory())->toBe($newMemory);
        expect($this->chatbot->getMemory()->getMaxHistory())->toBe(5);
    });

    it('works without memory manager', function () {
        $chatbot = new PhpChatbot($this->model);

        $response = $chatbot->ask('Hello', ['sessionId' => 'test']);

        expect($response)->toBeString();
        expect($chatbot->getConversationHistory('test'))->toBeEmpty();
    });

    it('returns false when clearing history without memory manager', function () {
        $chatbot = new PhpChatbot($this->model);

        $result = $chatbot->clearConversationHistory('test');

        expect($result)->toBeFalse();
    });

    it('passes conversation history to model context', function () {
        $sessionId = 'test-session';
        
        // Add some history manually
        $this->memory->addMessage($sessionId, 'user', 'Previous question');
        $this->memory->addMessage($sessionId, 'assistant', 'Previous answer');

        // Now ask a new question
        $this->chatbot->ask('New question', ['sessionId' => $sessionId]);

        $history = $this->chatbot->getConversationHistory($sessionId);

        // Should have: previous user + previous assistant + new user + new assistant
        expect($history)->toHaveCount(4);
    });

    it('can disable memory by setting null', function () {
        $sessionId = 'test-session';
        
        $this->chatbot->ask('Hello', ['sessionId' => $sessionId]);
        expect($this->chatbot->getConversationHistory($sessionId))->toHaveCount(2);

        $this->chatbot->setMemory(null);
        $this->chatbot->ask('Goodbye', ['sessionId' => $sessionId]);

        // History should not have changed
        expect($this->chatbot->getConversationHistory($sessionId))->toBeEmpty();
    });

    it('stores conversation history when streaming', function () {
        // Create a streamable model with mocked SSE response
        $sseResponse = "data: " . json_encode([
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion.chunk',
            'created' => time(),
            'model' => 'gpt-4o-mini',
            'choices' => [[
                'index' => 0,
                'delta' => ['content' => 'Hello'],
                'finish_reason' => null
            ]]
        ]) . "\n\n";
        $sseResponse .= "data: " . json_encode([
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion.chunk',
            'created' => time(),
            'model' => 'gpt-4o-mini',
            'choices' => [[
                'index' => 0,
                'delta' => ['content' => ' there'],
                'finish_reason' => null
            ]]
        ]) . "\n\n";
        $sseResponse .= "data: [DONE]\n\n";

        $httpClient = new \Tests\Helpers\MockHttpClient();
        $httpClient->setMockResponse($sseResponse);

        $model = new \Rumenx\PhpChatbot\Models\OpenAiModel(
            'test-api-key', 
            'gpt-4o-mini',
            'https://api.openai.com/v1/chat/completions',
            $httpClient
        );
        $chatbot = new PhpChatbot($model, [], $this->memory);

        $sessionId = 'stream-session';
        $fullResponse = '';

        foreach ($chatbot->askStream('Test streaming', ['sessionId' => $sessionId]) as $chunk) {
            $fullResponse .= $chunk;
        }

        $history = $chatbot->getConversationHistory($sessionId);

        expect($history)->toHaveCount(2); // user + assistant
        expect($history[0]['role'])->toBe('user');
        expect($history[0]['content'])->toBe('Test streaming');
        expect($history[1]['role'])->toBe('assistant');
        expect($history[1]['content'])->toBe('Hello there');
    });

    it('maintains context when streaming multiple times', function () {
        $httpClient = new \Tests\Helpers\MockHttpClient();
        
        // First streaming response
        $sseResponse1 = "data: " . json_encode([
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion.chunk',
            'created' => time(),
            'model' => 'gpt-4o-mini',
            'choices' => [[
                'index' => 0,
                'delta' => ['content' => 'First response'],
                'finish_reason' => null
            ]]
        ]) . "\n\ndata: [DONE]\n\n";

        // Second streaming response
        $sseResponse2 = "data: " . json_encode([
            'id' => 'chatcmpl-124',
            'object' => 'chat.completion.chunk',
            'created' => time(),
            'model' => 'gpt-4o-mini',
            'choices' => [[
                'index' => 0,
                'delta' => ['content' => 'Second response'],
                'finish_reason' => null
            ]]
        ]) . "\n\ndata: [DONE]\n\n";

        $model = new \Rumenx\PhpChatbot\Models\OpenAiModel(
            'test-api-key',
            'gpt-4o-mini',
            'https://api.openai.com/v1/chat/completions',
            $httpClient
        );
        $chatbot = new PhpChatbot($model, [], $this->memory);

        $sessionId = 'stream-session-2';

        // First streaming call
        $httpClient->setMockResponse($sseResponse1);
        foreach ($chatbot->askStream('First question', ['sessionId' => $sessionId]) as $chunk) {
            // Consume stream
        }

        // Second streaming call
        $httpClient->reset()->setMockResponse($sseResponse2);
        foreach ($chatbot->askStream('Second question', ['sessionId' => $sessionId]) as $chunk) {
            // Consume stream
        }

        $history = $chatbot->getConversationHistory($sessionId);

        expect($history)->toHaveCount(4); // 2 user + 2 assistant
        expect($history[0]['content'])->toBe('First question');
        expect($history[1]['content'])->toBe('First response');
        expect($history[2]['content'])->toBe('Second question');
        expect($history[3]['content'])->toBe('Second response');
    });
});
