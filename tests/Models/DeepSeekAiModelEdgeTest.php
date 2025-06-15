<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\DeepSeekAiModel;

it('DeepSeekAiModel returns default prompt if context missing', function () {
    $model = new DeepSeekAiModel('dummy');
    $response = $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('DeepSeekAiModel uses custom prompt and temperature', function () {
    $model = new DeepSeekAiModel('dummy');
    $response = $model->getResponse('test', [
        'prompt' => 'Custom!',
        'temperature' => 0.1,
    ]);
    expect($response)->toContain('No response');
});

it('DeepSeekAiModel handles non-string prompt', function () {
    $model = new DeepSeekAiModel('dummy');
    $response = $model->getResponse('test', [
        'prompt' => 123,
    ]);
    expect($response)->toContain('No response');
});

it('DeepSeekAiModel handles cURL error gracefully', function () {
    $model = new DeepSeekAiModel('dummy', 'deepseek-chat', 'http://localhost:9999/invalid');
    $response = $model->getResponse('test');
    expect($response)->toContain('DeepSeek');
});

it('DeepSeekAiModel returns fallback if choices missing', function () {
    $model = new class('dummy') extends DeepSeekAiModel {
        public function getResponse(string $input, array $context = []): string {
            // Simulate missing choices
            return json_encode(['status' => 'error', 'message' => '[DeepSeek] No response.']);
        }
    };
    $response = $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('DeepSeekAiModel handles exception', function () {
    $model = new class('dummy') extends DeepSeekAiModel {
        public function getResponse(string $input, array $context = []): string {
            throw new \Exception('Simulated');
        }
    };
    $result = null;
    try {
        $model->getResponse('test');
    } catch (\Exception $e) {
        $result = $e->getMessage();
    }
    expect($result)->toBe('Simulated');
});
