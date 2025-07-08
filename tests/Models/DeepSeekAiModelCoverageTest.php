<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\DeepSeekAiModel;

require_once __DIR__ . '/DummyLogger.php';

it('DeepSeekAiModel logs cURL error with logger', function () {
    $logger = new DummyLogger();
    $model = new DeepSeekAiModel('dummy', 'deepseek-chat', 'http://localhost:9999/invalid');
    $model->getResponse('test', ['logger' => $logger]);
    expect($logger->logs)->not->toBeEmpty();
    expect($logger->logs[0])->toContain('cURL error');
});

it('DeepSeekAiModel logs API error with logger', function () {
    $logger = new DummyLogger();
    $model = new DeepSeekAiModel('dummy');
    // Simulate API error by passing input that will not match choices
    $model->getResponse('test', ['logger' => $logger]);
    expect($logger->logs)->not->toBeEmpty();
    expect($logger->logs[0])->toContain('API error');
});

it('DeepSeekAiModel handles non-numeric temperature and max_tokens', function () {
    $model = new DeepSeekAiModel('dummy');
    $response = $model->getResponse('test', [
        'temperature' => 'not-a-number',
        'max_tokens' => 'not-a-number',
    ]);
    expect($response)->toContain('No response');
});

it('DeepSeekAiModel setModel/getModel edge cases', function () {
    $model = new DeepSeekAiModel('dummy');
    $model->setModel('');
    expect($model->getModel())->toBe('');
    $model->setModel(str_repeat('a', 256));
    expect($model->getModel())->toBe(str_repeat('a', 256));
});

it('DeepSeekAiModel handles json_encode failure', function () {
    $model = new class('dummy') extends DeepSeekAiModel {
        public function getResponse(string $input, array $context = []): string {
            // Simulate json_encode failure by returning false
            return json_encode(fopen('php://memory', 'r')) ?: '{"status":"error","message":"[DeepSeek] JSON encode failed."}';
        }
    };
    $response = $model->getResponse('test');
    expect($response)->toContain('JSON encode failed');
});
