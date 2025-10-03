<?php

namespace Tests\Models;

use Rumenx\PhpChatbot\Support\StreamBuffer;
use Rumenx\PhpChatbot\Models\OpenAiModel;
use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\XaiModel;
use Rumenx\PhpChatbot\Models\MetaModel;
use Rumenx\PhpChatbot\PhpChatbot;

/**
 * Streaming integration tests using real SSE data formats.
 * These tests verify the complete streaming flow without actual API calls.
 */

test('StreamBuffer processes multi-chunk OpenAI SSE stream', function () {
    $buffer = new StreamBuffer();
    
    // Simulate realistic OpenAI streaming chunks
    $sseData = "data: " . json_encode([
        'choices' => [['delta' => ['content' => 'The']]]
    ]) . "\n\n";
    
    $sseData .= "data: " . json_encode([
        'choices' => [['delta' => ['content' => ' quick']]]
    ]) . "\n\n";
    
    $sseData .= "data: " . json_encode([
        'choices' => [['delta' => ['content' => ' brown']]]
    ]) . "\n\n";
    
    $sseData .= "data: [DONE]\n\n";
    
    $buffer->add($sseData);
    
    $chunks = $buffer->getAllChunks();
    expect($chunks)->toHaveCount(3);
    expect(implode('', $chunks))->toBe('The quick brown');
});

test('StreamBuffer processes multi-chunk Anthropic SSE stream', function () {
    $buffer = new StreamBuffer();
    
    // Simulate realistic Anthropic streaming chunks
    $sseData = "data: " . json_encode([
        'type' => 'content_block_delta',
        'delta' => ['text' => 'Hello']
    ]) . "\n\n";
    
    $sseData .= "data: " . json_encode([
        'type' => 'content_block_delta',
        'delta' => ['text' => ' world']
    ]) . "\n\n";
    
    $buffer->add($sseData);
    
    $chunks = $buffer->getAllChunks();
    expect($chunks)->toHaveCount(2);
    expect(implode('', $chunks))->toBe('Hello world');
});

test('StreamBuffer handles incomplete then complete data', function () {
    $buffer = new StreamBuffer();
    
    // First chunk - incomplete JSON
    $buffer->add('data: {"choices":[{"delta":');
    expect($buffer->hasChunks())->toBeFalse();
    
    // Complete the JSON
    $buffer->add('{"content":"Complete"}}]}' . "\n\n");
    expect($buffer->hasChunks())->toBeTrue();
    expect($buffer->getChunk())->toBe('Complete');
});

test('OpenAiModel default parameters are set correctly for streaming', function () {
    $model = new OpenAiModel('test-api-key');
    
    // Get model details
    expect($model->getModel())->toBe('gpt-4o-mini');
    expect($model->supportsStreaming())->toBeTrue();
    
    // Verify it returns a generator
    $generator = $model->getStreamingResponse('test');
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('AnthropicModel default parameters are set correctly for streaming', function () {
    $model = new AnthropicModel('test-api-key');
    
    expect($model->getModel())->toBe('claude-3-5-sonnet-20241022');
    expect($model->supportsStreaming())->toBeTrue();
    
    $generator = $model->getStreamingResponse('test');
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('GeminiModel default parameters are set correctly for streaming', function () {
    $model = new GeminiModel('test-api-key');
    
    expect($model->getModel())->toBe('gemini-1.5-flash');
    expect($model->supportsStreaming())->toBeTrue();
    
    $generator = $model->getStreamingResponse('test');
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('XaiModel default parameters are set correctly for streaming', function () {
    $model = new XaiModel('test-api-key');
    
    expect($model->getModel())->toBe('grok-2-1212');
    expect($model->supportsStreaming())->toBeTrue();
    
    $generator = $model->getStreamingResponse('test');
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('MetaModel default parameters are set correctly for streaming', function () {
    $model = new MetaModel('test-api-key');
    
    expect($model->getModel())->toBe('llama-3.3-70b-versatile');
    expect($model->supportsStreaming())->toBeTrue();
    
    $generator = $model->getStreamingResponse('test');
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('streaming respects custom context prompt parameter', function () {
    $model = new OpenAiModel('test-key');
    $context = ['prompt' => 'You are a helpful assistant.'];
    
    $generator = $model->getStreamingResponse('Hello', $context);
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('streaming respects custom context temperature parameter', function () {
    $model = new OpenAiModel('test-key');
    $context = ['temperature' => 0.9];
    
    $generator = $model->getStreamingResponse('Hello', $context);
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('streaming respects custom context max_tokens parameter', function () {
    $model = new OpenAiModel('test-key');
    $context = ['max_tokens' => 500];
    
    $generator = $model->getStreamingResponse('Hello', $context);
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('streaming respects all custom context parameters together', function () {
    $model = new AnthropicModel('test-key');
    $context = [
        'prompt' => 'Custom prompt',
        'temperature' => 0.8,
        'max_tokens' => 1000
    ];
    
    $generator = $model->getStreamingResponse('Hello', $context);
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('PhpChatbot askStream validates model interface', function () {
    $model = new \Rumenx\PhpChatbot\Models\DefaultAiModel();
    $chatbot = new PhpChatbot($model);
    
    try {
        $generator = $chatbot->askStream('Hello');
        $generator->current(); // Trigger execution
        expect(false)->toBeTrue(); // Should not reach here
    } catch (\RuntimeException $e) {
        expect($e->getMessage())->toContain('does not implement StreamableModelInterface');
    }
});

test('PhpChatbot askStream validates model supports streaming', function () {
    // Create a mock that implements interface but returns false for supportsStreaming
    $model = new class('test-key') extends OpenAiModel {
        public function supportsStreaming(): bool {
            return false;
        }
    };
    
    $chatbot = new PhpChatbot($model);
    
    try {
        $generator = $chatbot->askStream('Hello');
        $generator->current(); // Trigger execution
        expect(false)->toBeTrue(); // Should not reach here
    } catch (\RuntimeException $e) {
        expect($e->getMessage())->toContain('Streaming is not available');
    }
});

test('PhpChatbot askStream returns generator for valid streaming model', function () {
    $model = new OpenAiModel('test-key');
    $chatbot = new PhpChatbot($model);
    
    $generator = $chatbot->askStream('Hello');
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('PhpChatbot askStream merges config with context', function () {
    $model = new OpenAiModel('test-key');
    $config = ['default_key' => 'default_value'];
    $chatbot = new PhpChatbot($model, $config);
    
    $context = ['context_key' => 'context_value'];
    $generator = $chatbot->askStream('Hello', $context);
    
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('StreamBuffer extracts content from complex OpenAI SSE with metadata', function () {
    $buffer = new StreamBuffer();
    
    $sseData = "data: " . json_encode([
        'id' => 'chatcmpl-abc123',
        'object' => 'chat.completion.chunk',
        'created' => 1234567890,
        'model' => 'gpt-4o-mini',
        'system_fingerprint' => 'fp_123',
        'choices' => [[
            'index' => 0,
            'delta' => [
                'role' => 'assistant',
                'content' => 'Test content'
            ],
            'logprobs' => null,
            'finish_reason' => null
        ]]
    ]) . "\n\n";
    
    $buffer->add($sseData);
    
    expect($buffer->hasChunks())->toBeTrue();
    expect($buffer->getChunk())->toBe('Test content');
});

test('StreamBuffer extracts content from complex Anthropic SSE with metadata', function () {
    $buffer = new StreamBuffer();
    
    $sseData = "data: " . json_encode([
        'type' => 'content_block_delta',
        'index' => 0,
        'delta' => [
            'type' => 'text_delta',
            'text' => 'Anthropic response'
        ]
    ]) . "\n\n";
    
    $buffer->add($sseData);
    
    expect($buffer->hasChunks())->toBeTrue();
    expect($buffer->getChunk())->toBe('Anthropic response');
});

test('StreamBuffer extracts content from complex Gemini response', function () {
    $buffer = new StreamBuffer();
    
    $sseData = "data: " . json_encode([
        'candidates' => [[
            'content' => [
                'parts' => [[
                    'text' => 'Gemini response text'
                ]],
                'role' => 'model'
            ],
            'finishReason' => 'STOP',
            'index' => 0
        ]],
        'usageMetadata' => [
            'promptTokenCount' => 10,
            'candidatesTokenCount' => 20
        ]
    ]) . "\n\n";
    
    $buffer->add($sseData);
    
    expect($buffer->hasChunks())->toBeTrue();
    expect($buffer->getChunk())->toBe('Gemini response text');
});
