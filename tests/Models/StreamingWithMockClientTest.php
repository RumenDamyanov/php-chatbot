<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\OpenAiModel;
use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\XaiModel;
use Rumenx\PhpChatbot\Models\MetaModel;
use Tests\Helpers\MockHttpClient;

/**
 * Tests for streaming execution using MockHttpClient.
 * 
 * This test suite injects MockHttpClient into streaming models to test
 * the actual streaming code paths without requiring real API calls.
 */

test('OpenAiModel streaming executes with mock client returning SSE data', function () {
    $mockClient = new MockHttpClient();
    $mockClient->setMockResponse(
        "data: " . json_encode(['choices' => [['delta' => ['content' => 'Hello']]]]) . "\n\n" .
        "data: " . json_encode(['choices' => [['delta' => ['content' => ' World']]]]) . "\n\n" .
        "data: [DONE]\n\n"
    );
    
    $model = new OpenAiModel('test-key', 'gpt-4', 'https://api.openai.com/v1/chat/completions', $mockClient);
    $chunks = [];
    
    foreach ($model->getStreamingResponse('test input') as $chunk) {
        $chunks[] = $chunk;
    }
    
    expect($chunks)->toHaveCount(2)
        ->and($chunks)->toContain('Hello')
        ->and($chunks)->toContain(' World');
});

test('AnthropicModel streaming executes with mock client returning SSE data', function () {
    $mockClient = new MockHttpClient();
    $mockClient->setMockResponse(
        "event: content_block_delta\ndata: " . json_encode(['type' => 'content_block_delta', 'delta' => ['text' => 'Test']]) . "\n\n" .
        "event: content_block_delta\ndata: " . json_encode(['type' => 'content_block_delta', 'delta' => ['text' => ' Response']]) . "\n\n"
    );
    
    $model = new AnthropicModel('test-key', 'claude-3', 'https://api.anthropic.com/v1/messages', $mockClient);
    $chunks = [];
    
    foreach ($model->getStreamingResponse('test input') as $chunk) {
        $chunks[] = $chunk;
    }
    
    expect($chunks)->toHaveCount(2)
        ->and($chunks)->toContain('Test')
        ->and($chunks)->toContain(' Response');
});

test('GeminiModel streaming executes with mock client returning Gemini format', function () {
    $mockClient = new MockHttpClient();
    // Gemini sends JSON chunks separated by newlines (not SSE format)
    $mockClient->setMockResponse(
        "data: " . json_encode(['candidates' => [['content' => ['parts' => [['text' => 'Gemini response']]]]]]) . "\n\n"
    );
    
    $model = new GeminiModel('test-key', 'gemini-pro', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:streamGenerateContent', $mockClient);
    $chunks = [];
    
    foreach ($model->getStreamingResponse('test input') as $chunk) {
        $chunks[] = $chunk;
    }
    
    expect($chunks)->toHaveCount(1)
        ->and($chunks)->toContain('Gemini response');
});

test('XaiModel streaming executes with mock client returning OpenAI-compatible SSE', function () {
    $mockClient = new MockHttpClient();
    $mockClient->setMockResponse(
        "data: " . json_encode(['choices' => [['delta' => ['content' => 'xAI']]]]) . "\n\n" .
        "data: " . json_encode(['choices' => [['delta' => ['content' => ' Grok']]]]) . "\n\n" .
        "data: [DONE]\n\n"
    );
    
    $model = new XaiModel('test-key', 'grok-2', 'https://api.xai.com/v1/chat', $mockClient);
    $chunks = [];
    
    foreach ($model->getStreamingResponse('test input') as $chunk) {
        $chunks[] = $chunk;
    }
    
    expect($chunks)->toHaveCount(2)
        ->and($chunks)->toContain('xAI')
        ->and($chunks)->toContain(' Grok');
});

test('MetaModel streaming executes with mock client returning LLaMA format', function () {
    $mockClient = new MockHttpClient();
    $mockClient->setMockResponse(
        "data: " . json_encode(['choices' => [['delta' => ['content' => 'Meta']]]]) . "\n\n" .
        "data: " . json_encode(['choices' => [['delta' => ['content' => ' LLaMA']]]]) . "\n\n" .
        "data: [DONE]\n\n"
    );
    
    $model = new MetaModel('test-key', 'llama-3', 'https://api.meta.com/v1/chat', $mockClient);
    $chunks = [];
    
    foreach ($model->getStreamingResponse('test input') as $chunk) {
        $chunks[] = $chunk;
    }
    
    expect($chunks)->toHaveCount(2)
        ->and($chunks)->toContain('Meta')
        ->and($chunks)->toContain(' LLaMA');
});

test('OpenAiModel streaming handles HTTP client failure gracefully', function () {
    $mockClient = new MockHttpClient();
    $mockClient->setFailure('Connection failed');
    
    $model = new OpenAiModel('test-key', 'gpt-4', 'https://api.openai.com/v1/chat/completions', $mockClient);
    $chunks = [];
    
    foreach ($model->getStreamingResponse('test input') as $chunk) {
        $chunks[] = $chunk;
    }
    
    expect($chunks)->toHaveCount(1)
        ->and($chunks[0])->toContain('[OpenAI Streaming] Error')
        ->and($chunks[0])->toContain('Connection failed');
});

test('AnthropicModel streaming handles HTTP client failure gracefully', function () {
    $mockClient = new MockHttpClient();
    $mockClient->setFailure('API timeout');
    
    $model = new AnthropicModel('test-key', 'claude-3', 'https://api.anthropic.com/v1/messages', $mockClient);
    $chunks = [];
    
    foreach ($model->getStreamingResponse('test input') as $chunk) {
        $chunks[] = $chunk;
    }
    
    expect($chunks)->toHaveCount(1)
        ->and($chunks[0])->toContain('[Anthropic Streaming] Error')
        ->and($chunks[0])->toContain('API timeout');
});

test('GeminiModel streaming handles HTTP client failure gracefully', function () {
    $mockClient = new MockHttpClient();
    $mockClient->setFailure('Network error');
    
    $model = new GeminiModel('test-key', 'gemini-pro', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:streamGenerateContent', $mockClient);
    $chunks = [];
    
    foreach ($model->getStreamingResponse('test input') as $chunk) {
        $chunks[] = $chunk;
    }
    
    expect($chunks)->toHaveCount(1)
        ->and($chunks[0])->toContain('[Google Gemini Streaming] Error')
        ->and($chunks[0])->toContain('Network error');
});

test('XaiModel streaming handles HTTP client failure gracefully', function () {
    $mockClient = new MockHttpClient();
    $mockClient->setFailure('Authentication failed');
    
    $model = new XaiModel('test-key', 'grok-2', 'https://api.xai.com/v1/chat', $mockClient);
    $chunks = [];
    
    foreach ($model->getStreamingResponse('test input') as $chunk) {
        $chunks[] = $chunk;
    }
    
    expect($chunks)->toHaveCount(1)
        ->and($chunks[0])->toContain('[xAI Streaming] Error')
        ->and($chunks[0])->toContain('Authentication failed');
});

test('MetaModel streaming handles HTTP client failure gracefully', function () {
    $mockClient = new MockHttpClient();
    $mockClient->setFailure('Rate limit exceeded');
    
    $model = new MetaModel('test-key', 'llama-3', 'https://api.meta.com/v1/chat', $mockClient);
    $chunks = [];
    
    foreach ($model->getStreamingResponse('test input') as $chunk) {
        $chunks[] = $chunk;
    }
    
    expect($chunks)->toHaveCount(1)
        ->and($chunks[0])->toContain('[Meta Streaming] Error')
        ->and($chunks[0])->toContain('Rate limit exceeded');
});

test('OpenAiModel streaming respects custom context parameters', function () {
    $mockClient = new MockHttpClient();
    $mockClient->setMockResponse(
        "data: " . json_encode(['choices' => [['delta' => ['content' => 'Custom']]]]) . "\n\n" .
        "data: [DONE]\n\n"
    );
    
    $model = new OpenAiModel('test-key', 'gpt-4', 'https://api.openai.com/v1/chat/completions', $mockClient);
    $chunks = [];
    
    $context = [
        'prompt' => 'Custom system prompt',
        'max_tokens' => 1024,
        'temperature' => 0.9
    ];
    
    foreach ($model->getStreamingResponse('test input', $context) as $chunk) {
        $chunks[] = $chunk;
    }
    
    expect($chunks)->toContain('Custom');
});

test('MockHttpClient actually invokes streaming callback', function () {
    $mockClient = new MockHttpClient();
    $mockClient->setMockResponse("data: test\n\n");
    
    $callbackInvoked = false;
    $streamCallback = function ($ch, $chunk) use (&$callbackInvoked) {
        $callbackInvoked = true;
        return strlen($chunk);
    };
    
    $mockClient->post('https://test.com', ['Content-Type' => 'application/json'], '{}', $streamCallback);
    
    expect($callbackInvoked)->toBeTrue();
});

test('MockHttpClient passes full response through callback', function () {
    $mockClient = new MockHttpClient();
    $testData = "line 1\nline 2\nline 3";
    $mockClient->setMockResponse($testData);
    
    $receivedData = '';
    $streamCallback = function ($ch, $chunk) use (&$receivedData) {
        $receivedData .= $chunk;
        return strlen($chunk);
    };
    
    $mockClient->post('https://test.com', ['Content-Type' => 'application/json'], '{}', $streamCallback);
    
    expect($receivedData)->toBe($testData);
});
