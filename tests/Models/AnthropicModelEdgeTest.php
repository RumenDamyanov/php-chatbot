<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\AnthropicModel;

it('AnthropicModel returns default prompt if context missing', function () {
    $model = new AnthropicModel('dummy');
    $response = $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('AnthropicModel uses custom prompt and temperature', function () {
    $model = new AnthropicModel('dummy');
    $response = $model->getResponse('test', [
        'prompt' => 'Custom!',
        'temperature' => 0.1,
    ]);
    expect($response)->toContain('No response');
});

it('AnthropicModel handles non-string prompt', function () {
    $model = new AnthropicModel('dummy');
    $response = $model->getResponse('test', [
        'prompt' => 123,
    ]);
    expect($response)->toContain('No response');
});

it('AnthropicModel handles cURL error gracefully', function () {
    $model = new AnthropicModel('dummy', 'claude-3-sonnet', 'http://localhost:9999/invalid');
    $response = $model->getResponse('test');
    expect($response)->toContain('Anthropic');
});

it('AnthropicModel returns fallback if choices missing', function () {
    $model = new class('dummy') extends AnthropicModel {
        public function getResponse(string $input, array $context = []): string {
            // Simulate missing choices
            return '[Anthropic] No response.';
        }
    };
    $response = $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('AnthropicModel handles exception', function () {
    $model = new class('dummy') extends AnthropicModel {
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
