<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\XaiModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\MetaModel;
use Rumenx\PhpChatbot\Models\DeepSeekAiModel;
use Rumenx\PhpChatbot\Models\DefaultAiModel;
use Rumenx\PhpChatbot\Models\OpenAiModel;

it('AnthropicModel returns placeholder', function () {
    $model = new AnthropicModel('dummy-key', 'claude-3-sonnet-20240229');
    $response = (string) $model->getResponse('Hi!');
    expect($response)->toContain('Anthropic');
});

it('XaiModel returns placeholder', function () {
    $model = new XaiModel('dummy-key', 'grok-1');
    $response = (string) $model->getResponse('Hi!');
    expect($response)->toContain('xAI');
});

it('GeminiModel returns placeholder', function () {
    $model = new GeminiModel('dummy-key', 'gemini-1.5-pro');
    $response = (string) $model->getResponse('Hi!');
    expect($response)->toContain('Google Gemini');
});

it('MetaModel returns placeholder', function () {
    $model = new MetaModel('dummy-key', 'llama-3-70b');
    $response = (string) $model->getResponse('Hi!');
    expect($response)->toContain('Meta');
});

it('DeepSeekAiModel returns placeholder', function () {
    $model = new DeepSeekAiModel('dummy-key');
    $response = (string) $model->getResponse('Hi!');
    expect($response)->toContain('DeepSeek');
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
