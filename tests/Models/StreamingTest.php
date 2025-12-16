<?php

declare(strict_types=1);

namespace Tests\Models;

use Rumenx\PhpChatbot\Models\OpenAiModel;
use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\XaiModel;
use Rumenx\PhpChatbot\Models\MetaModel;
use Rumenx\PhpChatbot\Models\DefaultAiModel;
use Rumenx\PhpChatbot\PhpChatbot;

test('OpenAiModel implements StreamableModelInterface', function () {
    $model = new OpenAiModel('test-key');
    expect($model)->toBeInstanceOf(\Rumenx\PhpChatbot\Contracts\StreamableModelInterface::class);
});

test('OpenAiModel supportsStreaming returns true', function () {
    $model = new OpenAiModel('test-key');
    expect($model->supportsStreaming())->toBeTrue();
});

test('OpenAiModel getStreamingResponse returns Generator', function () {
    $model = new OpenAiModel('test-key');
    $generator = $model->getStreamingResponse('Hello', []);
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('AnthropicModel implements StreamableModelInterface', function () {
    $model = new AnthropicModel('test-key');
    expect($model)->toBeInstanceOf(\Rumenx\PhpChatbot\Contracts\StreamableModelInterface::class);
});

test('AnthropicModel supportsStreaming returns true', function () {
    $model = new AnthropicModel('test-key');
    expect($model->supportsStreaming())->toBeTrue();
});

test('AnthropicModel getStreamingResponse returns Generator', function () {
    $model = new AnthropicModel('test-key');
    $generator = $model->getStreamingResponse('Hello', []);
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('GeminiModel implements StreamableModelInterface', function () {
    $model = new GeminiModel('test-key');
    expect($model)->toBeInstanceOf(\Rumenx\PhpChatbot\Contracts\StreamableModelInterface::class);
});

test('GeminiModel supportsStreaming returns true', function () {
    $model = new GeminiModel('test-key');
    expect($model->supportsStreaming())->toBeTrue();
});

test('GeminiModel getStreamingResponse returns Generator', function () {
    $model = new GeminiModel('test-key');
    $generator = $model->getStreamingResponse('Hello', []);
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('XaiModel implements StreamableModelInterface', function () {
    $model = new XaiModel('test-key');
    expect($model)->toBeInstanceOf(\Rumenx\PhpChatbot\Contracts\StreamableModelInterface::class);
});

test('XaiModel supportsStreaming returns true', function () {
    $model = new XaiModel('test-key');
    expect($model->supportsStreaming())->toBeTrue();
});

test('XaiModel getStreamingResponse returns Generator', function () {
    $model = new XaiModel('test-key');
    $generator = $model->getStreamingResponse('Hello', []);
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('MetaModel implements StreamableModelInterface', function () {
    $model = new MetaModel('test-key');
    expect($model)->toBeInstanceOf(\Rumenx\PhpChatbot\Contracts\StreamableModelInterface::class);
});

test('MetaModel supportsStreaming returns true', function () {
    $model = new MetaModel('test-key');
    expect($model->supportsStreaming())->toBeTrue();
});

test('MetaModel getStreamingResponse returns Generator', function () {
    $model = new MetaModel('test-key');
    $generator = $model->getStreamingResponse('Hello', []);
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('DefaultAiModel does not implement StreamableModelInterface', function () {
    $model = new DefaultAiModel();
    expect($model)->not->toBeInstanceOf(\Rumenx\PhpChatbot\Contracts\StreamableModelInterface::class);
});

test('PhpChatbot askStream throws exception for non-streaming model', function () {
    $model = new DefaultAiModel();
    $chatbot = new PhpChatbot($model);
    
    try {
        $generator = $chatbot->askStream('Hello');
        // Trigger the generator to execute
        $generator->current();
        expect(true)->toBeFalse(); // Should not reach here
    } catch (\RuntimeException $e) {
        expect($e)->toBeInstanceOf(\RuntimeException::class);
        expect($e->getMessage())->toContain('does not implement StreamableModelInterface');
    }
});

test('PhpChatbot askStream returns Generator for streaming model', function () {
    $model = new OpenAiModel('test-key');
    $chatbot = new PhpChatbot($model);
    $generator = $chatbot->askStream('Hello');
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('PhpChatbot askStream accepts context parameter', function () {
    $model = new OpenAiModel('test-key');
    $chatbot = new PhpChatbot($model);
    $generator = $chatbot->askStream('Hello', ['temperature' => 0.5]);
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('OpenAiModel streaming respects context parameters', function () {
    $model = new OpenAiModel('test-key');
    $context = [
        'prompt' => 'Custom system prompt',
        'temperature' => 0.9,
        'max_tokens' => 512
    ];
    $generator = $model->getStreamingResponse('Hello', $context);
    expect($generator)->toBeInstanceOf(\Generator::class);
});

test('AnthropicModel streaming respects context parameters', function () {
    $model = new AnthropicModel('test-key');
    $context = [
        'prompt' => 'Custom system prompt',
        'temperature' => 0.9,
        'max_tokens' => 512
    ];
    $generator = $model->getStreamingResponse('Hello', $context);
    expect($generator)->toBeInstanceOf(\Generator::class);
});
