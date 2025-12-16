<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\XaiModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\MetaModel;
use Rumenx\PhpChatbot\Models\DeepSeekAiModel;
use Rumenx\PhpChatbot\Models\DefaultAiModel;
use Rumenx\PhpChatbot\Models\OpenAiModel;

it('AnthropicModel throws exception with invalid key', function () {
    $model = new AnthropicModel('dummy-key', 'claude-3-sonnet-20240229');
    try {
        $model->getResponse('Hi!');
        expect(false)->toBeTrue('Expected ApiException to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('Anthropic');
    }
});

it('XaiModel throws exception with invalid key', function () {
    $model = new XaiModel('dummy-key', 'grok-1');
    try {
        $model->getResponse('Hi!');
        expect(false)->toBeTrue('Expected ApiException to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('xAI');
    }
});

it('GeminiModel throws exception with invalid key', function () {
    $model = new GeminiModel('dummy-key', 'gemini-1.5-pro');
    try {
        $model->getResponse('Hi!');
        expect(false)->toBeTrue('Expected ApiException to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('Google Gemini');
    }
});

it('MetaModel throws exception with invalid key', function () {
    $model = new MetaModel('dummy-key', 'llama-3-70b');
    try {
        $model->getResponse('Hi!');
        expect(false)->toBeTrue('Expected exception to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException | \Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('Meta');
    }
});

it('DeepSeekAiModel throws exception with invalid key', function () {
    $model = new DeepSeekAiModel('dummy-key');
    try {
        $model->getResponse('Hi!');
        expect(false)->toBeTrue('Expected ApiException to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('DeepSeek');
    }
});

it('DefaultAiModel returns a default response', function () {
    $model = new DefaultAiModel();
    $response = (string) $model->getResponse('Hi!');
    expect($response)->toContain('default AI response');
});

it('OpenAiModel throws exception with dummy key', function () {
    $model = new OpenAiModel('dummy-key');
    try {
        $model->getResponse('Hi!');
        expect(false)->toBeTrue('Expected exception to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('OpenAI');
    } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
        expect($e->getMessage())->toContain('OpenAI');
    }
});
