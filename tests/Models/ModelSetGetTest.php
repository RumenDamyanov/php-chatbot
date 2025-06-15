<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\DeepSeekAiModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\MetaModel;
use Rumenx\PhpChatbot\Models\XaiModel;

it('AnthropicModel setModel/getModel', function () {
    $model = new AnthropicModel('dummy', 'claude-3-sonnet');
    $model->setModel('claude-3-haiku');
    expect($model->getModel())->toBe('claude-3-haiku');
});

it('DeepSeekAiModel setModel/getModel', function () {
    $model = new DeepSeekAiModel('dummy', 'deepseek-chat');
    $model->setModel('deepseek-2');
    expect($model->getModel())->toBe('deepseek-2');
});

it('GeminiModel setModel/getModel', function () {
    $model = new GeminiModel('dummy', 'gemini-1.5-pro');
    $model->setModel('gemini-1.5-flash');
    expect($model->getModel())->toBe('gemini-1.5-flash');
});

it('MetaModel setModel/getModel', function () {
    $model = new MetaModel('dummy', 'llama-3-8b');
    $model->setModel('llama-3-70b');
    expect($model->getModel())->toBe('llama-3-70b');
});

it('XaiModel setModel/getModel', function () {
    $model = new XaiModel('dummy', 'grok-1');
    $model->setModel('grok-1.5');
    expect($model->getModel())->toBe('grok-1.5');
});
