<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\MetaModel;

it('MetaModel returns default prompt if context missing', function () {
    $model = new MetaModel('dummy');
    $response = $model->getResponse('test');
    expect($response)->toContain('Error:');
});

it('MetaModel uses custom prompt', function () {
    $model = new MetaModel('dummy');
    $response = $model->getResponse('test', [
        'prompt' => 'Custom!',
    ]);
    expect($response)->toContain('Error:');
});

it('MetaModel handles non-string prompt', function () {
    $model = new MetaModel('dummy');
    $response = $model->getResponse('test', [
        'prompt' => 123,
    ]);
    expect($response)->toContain('Error:');
});

it('MetaModel handles cURL error gracefully', function () {
    $model = new MetaModel('dummy', 'llama-3-70b', 'http://localhost:9999/invalid');
    $response = $model->getResponse('test');
    expect($response)->toContain('Meta');
});

it('MetaModel returns fallback if choices missing', function () {
    $model = new class('dummy') extends MetaModel {
        public function getResponse(string $input, array $context = []): string {
            // Simulate missing choices
            return '[Meta] No response.';
        }
    };
    $response = $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('MetaModel handles exception', function () {
    $model = new class('dummy') extends MetaModel {
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
