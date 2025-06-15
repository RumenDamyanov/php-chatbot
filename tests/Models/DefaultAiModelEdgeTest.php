<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\DefaultAiModel;

it('DefaultAiModel returns default prompt if context missing', function () {
    $model = new DefaultAiModel();
    $response = $model->getResponse('test');
    expect($response)->toContain('You are a helpful chatbot.');
});

it('DefaultAiModel uses custom prompt and language', function () {
    $model = new DefaultAiModel();
    $response = $model->getResponse('test', [
        'prompt' => 'Custom!',
        'language' => 'fr',
    ]);
    expect($response)->toContain('[DefaultAI-fr] Custom!');
});

it('DefaultAiModel handles history context', function () {
    $model = new DefaultAiModel();
    $response = $model->getResponse('test', [
        'history' => ['foo', 'bar'],
    ]);
    expect($response)->toContain('Previous conversation: foo bar');
});

it('DefaultAiModel handles empty history', function () {
    $model = new DefaultAiModel();
    $response = $model->getResponse('test', [
        'history' => [],
    ]);
    expect($response)->not->toContain('Previous conversation:');
});

it('DefaultAiModel handles non-string prompt/language', function () {
    $model = new DefaultAiModel();
    $response = $model->getResponse('test', [
        'prompt' => 123,
        'language' => ['en'],
    ]);
    expect($response)->toContain('[DefaultAI-en] You are a helpful chatbot.');
});
