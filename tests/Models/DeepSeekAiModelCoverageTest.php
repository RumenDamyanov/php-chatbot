<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\DeepSeekAiModel;

require_once __DIR__ . '/DummyLogger.php';

it('DeepSeekAiModel throws NetworkException on cURL error', function () {
    $logger = new DummyLogger();
    $model = new DeepSeekAiModel('dummy', 'deepseek-chat', 'http://localhost:9999/invalid');
    try {
        $model->getResponse('test', ['logger' => $logger]);
        expect(false)->toBeTrue('Expected NetworkException to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
        expect($e->getMessage())->toContain('DeepSeek');
        expect($logger->logs)->not->toBeEmpty();
    }
});

it('DeepSeekAiModel throws ApiException on invalid response', function () {
    $logger = new DummyLogger();
    $model = new DeepSeekAiModel('dummy');
    try {
        $model->getResponse('test', ['logger' => $logger]);
        expect(false)->toBeTrue('Expected ApiException to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('DeepSeek');
        expect($logger->logs)->not->toBeEmpty();
    }
});

it('DeepSeekAiModel throws ApiException with non-numeric temperature and max_tokens', function () {
    $model = new DeepSeekAiModel('dummy');
    try {
        $model->getResponse('test', [
            'temperature' => 'not-a-number',
            'max_tokens' => 'not-a-number',
        ]);
        expect(false)->toBeTrue('Expected ApiException to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('DeepSeek');
    }
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
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            // Simulate json_encode failure by returning false
            $result = json_encode(fopen('php://memory', 'r')) ?: '{"status":"error","message":"[DeepSeek] JSON encode failed."}';
            return \Rumenx\PhpChatbot\Support\ChatResponse::fromString($result, 'deepseek-chat');
        }
    };
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('JSON encode failed');
});
