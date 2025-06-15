<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\MetaModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\XaiModel;

it('MetaModel getModel/setModel', function () {
    $model = new MetaModel('dummy', 'llama-3-8b');
    expect($model->getModel())->toBe('llama-3-8b');
    $model->setModel('llama-3-70b');
    expect($model->getModel())->toBe('llama-3-70b');
});

it('MetaModel sendMessage returns placeholder', function () {
    $model = new MetaModel('dummy', 'llama-3-8b');
    $response = $model->sendMessage('Hi!');
    expect($response)->toContain('Meta');
});

it('GeminiModel getModel/setModel', function () {
    $model = new GeminiModel('dummy', 'gemini-1.5-pro');
    expect($model->getModel())->toBe('gemini-1.5-pro');
    $model->setModel('gemini-1.5-flash');
    expect($model->getModel())->toBe('gemini-1.5-flash');
});

it('GeminiModel getResponse returns placeholder', function () {
    $model = new GeminiModel('dummy', 'gemini-1.5-pro');
    $response = $model->getResponse('Hi!');
    expect($response)->toContain('Google Gemini');
});

it('XaiModel getModel/setModel', function () {
    $model = new XaiModel('dummy', 'grok-1');
    expect($model->getModel())->toBe('grok-1');
    $model->setModel('grok-1.5');
    expect($model->getModel())->toBe('grok-1.5');
});

it('XaiModel getResponse returns placeholder', function () {
    $model = new XaiModel('dummy', 'grok-1');
    $response = $model->getResponse('Hi!');
    expect($response)->toContain('xAI');
});
