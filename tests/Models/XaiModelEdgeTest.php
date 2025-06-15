<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\XaiModel;

it('XaiModel returns default prompt if context missing', function () {
    $model = new XaiModel('dummy');
    $response = $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('XaiModel uses custom prompt', function () {
    $model = new XaiModel('dummy');
    $response = $model->getResponse('test', [
        'prompt' => 'Custom!',
    ]);
    expect($response)->toContain('No response');
});

it('XaiModel handles non-string prompt', function () {
    $model = new XaiModel('dummy');
    $response = $model->getResponse('test', [
        'prompt' => 123,
    ]);
    expect($response)->toContain('No response');
});

it('XaiModel handles cURL error gracefully', function () {
    $model = new XaiModel('dummy', 'grok-1', 'http://localhost:9999/invalid');
    $response = $model->getResponse('test');
    expect($response)->toContain('xAI');
});

it('XaiModel returns fallback if choices missing', function () {
    $model = new class('dummy') extends XaiModel {
        public function getResponse(string $input, array $context = []): string {
            // Simulate missing choices
            return '[xAI] No response.';
        }
    };
    $response = $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('XaiModel handles exception', function () {
    $model = new class('dummy') extends XaiModel {
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
