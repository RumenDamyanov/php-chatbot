<?php

declare(strict_types=1);

namespace Tests\Support;

use Rumenx\PhpChatbot\Support\StreamBuffer;

test('StreamBuffer can be instantiated', function () {
    $buffer = new StreamBuffer();
    expect($buffer)->toBeInstanceOf(StreamBuffer::class);
});

test('StreamBuffer hasChunks returns false initially', function () {
    $buffer = new StreamBuffer();
    expect($buffer->hasChunks())->toBeFalse();
});

test('StreamBuffer parses OpenAI SSE format', function () {
    $buffer = new StreamBuffer();
    $sseData = 'data: {"choices":[{"delta":{"content":"Hello"}}]}' . "\n\n";
    $buffer->add($sseData);
    expect($buffer->hasChunks())->toBeTrue();
    expect($buffer->getChunk())->toBe('Hello');
});

test('StreamBuffer parses Anthropic SSE format', function () {
    $buffer = new StreamBuffer();
    $sseData = 'data: {"type":"content_block_delta","delta":{"text":"World"}}' . "\n\n";
    $buffer->add($sseData);
    expect($buffer->hasChunks())->toBeTrue();
    expect($buffer->getChunk())->toBe('World');
});

test('StreamBuffer parses Gemini response format', function () {
    $buffer = new StreamBuffer();
    $sseData = 'data: {"candidates":[{"content":{"parts":[{"text":"Gemini"}]}}]}' . "\n\n";
    $buffer->add($sseData);
    expect($buffer->hasChunks())->toBeTrue();
    expect($buffer->getChunk())->toBe('Gemini');
});

test('StreamBuffer handles multiple chunks', function () {
    $buffer = new StreamBuffer();
    $sseData = 'data: {"choices":[{"delta":{"content":"Hello"}}]}' . "\n\n" .
               'data: {"choices":[{"delta":{"content":" World"}}]}' . "\n\n";
    $buffer->add($sseData);
    expect($buffer->hasChunks())->toBeTrue();
    expect($buffer->getChunk())->toBe('Hello');
    expect($buffer->hasChunks())->toBeTrue();
    expect($buffer->getChunk())->toBe(' World');
    expect($buffer->hasChunks())->toBeFalse();
});

test('StreamBuffer handles incomplete SSE data', function () {
    $buffer = new StreamBuffer();
    // First part of incomplete data
    $buffer->add('data: {"choices":[{"delta":');
    expect($buffer->hasChunks())->toBeFalse();
    
    // Complete the data
    $buffer->add('{"content":"Test"}}]}' . "\n\n");
    expect($buffer->hasChunks())->toBeTrue();
    expect($buffer->getChunk())->toBe('Test');
});

test('StreamBuffer ignores empty content', function () {
    $buffer = new StreamBuffer();
    $sseData = 'data: {"choices":[{"delta":{"content":""}}]}' . "\n\n";
    $buffer->add($sseData);
    expect($buffer->hasChunks())->toBeFalse();
});

test('StreamBuffer handles [DONE] marker', function () {
    $buffer = new StreamBuffer();
    $sseData = 'data: [DONE]' . "\n\n";
    $buffer->add($sseData);
    expect($buffer->hasChunks())->toBeFalse();
});

test('StreamBuffer getAllChunks returns all chunks', function () {
    $buffer = new StreamBuffer();
    $sseData = 'data: {"choices":[{"delta":{"content":"Hello"}}]}' . "\n\n" .
               'data: {"choices":[{"delta":{"content":" World"}}]}' . "\n\n";
    $buffer->add($sseData);
    $chunks = $buffer->getAllChunks();
    expect($chunks)->toBeArray();
    expect($chunks)->toHaveCount(2);
    expect($chunks[0])->toBe('Hello');
    expect($chunks[1])->toBe(' World');
});

test('StreamBuffer clear removes all chunks', function () {
    $buffer = new StreamBuffer();
    $sseData = 'data: {"choices":[{"delta":{"content":"Hello"}}]}' . "\n\n";
    $buffer->add($sseData);
    expect($buffer->hasChunks())->toBeTrue();
    $buffer->clear();
    expect($buffer->hasChunks())->toBeFalse();
});

test('StreamBuffer handles invalid JSON gracefully', function () {
    $buffer = new StreamBuffer();
    $sseData = 'data: {invalid json}' . "\n\n";
    $buffer->add($sseData);
    expect($buffer->hasChunks())->toBeFalse();
});

test('StreamBuffer handles mixed SSE formats', function () {
    $buffer = new StreamBuffer();
    $sseData = 'data: {"choices":[{"delta":{"content":"OpenAI"}}]}' . "\n\n" .
               'data: {"type":"content_block_delta","delta":{"text":"Anthropic"}}' . "\n\n" .
               'data: {"candidates":[{"content":{"parts":[{"text":"Gemini"}]}}]}' . "\n\n";
    $buffer->add($sseData);
    
    expect($buffer->getChunk())->toBe('OpenAI');
    expect($buffer->getChunk())->toBe('Anthropic');
    expect($buffer->getChunk())->toBe('Gemini');
    expect($buffer->hasChunks())->toBeFalse();
});

test('StreamBuffer getChunk returns null when empty', function () {
    $buffer = new StreamBuffer();
    expect($buffer->getChunk())->toBeNull();
});

test('StreamBuffer handles multiple add calls', function () {
    $buffer = new StreamBuffer();
    $buffer->add('data: {"choices":[{"delta":{"content":"Part1"}}]}' . "\n\n");
    $buffer->add('data: {"choices":[{"delta":{"content":"Part2"}}]}' . "\n\n");
    
    expect($buffer->getChunk())->toBe('Part1');
    expect($buffer->getChunk())->toBe('Part2');
});
