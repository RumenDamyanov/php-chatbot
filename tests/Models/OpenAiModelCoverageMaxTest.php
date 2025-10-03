<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\ChatResponse;

use Rumenx\PhpChatbot\Models\OpenAiModel;

require_once __DIR__ . '/DummyLogger.php';

it('OpenAiModel setModel and getModel (direct)', function () {
    $model = new OpenAiModel('dummy-key', 'gpt-3.5-turbo');
    $model->setModel('gpt-4');
    expect($model->getModel())->toBe('gpt-4');
});

it('OpenAiModel setModel and getModel work as expected', function () {
    $model = new \Rumenx\PhpChatbot\Models\OpenAiModel('dummy-key', 'gpt-3.5-turbo');
    $model->setModel('gpt-4');
    expect($model->getModel())->toBe('gpt-4');
});

it('OpenAiModel getModel returns initial model', function () {
    $model = new \Rumenx\PhpChatbot\Models\OpenAiModel('dummy-key', 'gpt-3.5-turbo');
    expect($model->getModel())->toBe('gpt-3.5-turbo');
});

it('OpenAiModel logs cURL error with logger', function () {
    $logger = new DummyLogger();
    $model = new OpenAiModel('invalid-key', 'gpt-3.5-turbo', 'http://localhost:9999/invalid');
    $model->getResponse('test', ['logger' => $logger]);
    expect($logger->logs)->not->toBeEmpty();
});

it('OpenAiModel logs API error (no response) with logger', function () {
    $logger = new DummyLogger();
    $model = new class('dummy', 'gpt-3.5-turbo') extends OpenAiModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            if (isset($context['logger'])) {
                $context['logger']->error('OpenAiModel API error: No response', ['response' => []]);
            }
            return \Rumenx\PhpChatbot\Support\ChatResponse::fromString('[OpenAI] No response.', 'gpt-3.5-turbo');
        }
    };
    $model->getResponse('test', ['logger' => $logger]);
    expect($logger->logs)->not->toBeEmpty();
});

it('OpenAiModel logs exception with logger', function () {
    $logger = new DummyLogger();
    $model = new class('dummy', 'gpt-3.5-turbo') extends OpenAiModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            try {
                throw new \Exception('Simulated');
            } catch (\Throwable $e) {
                if (isset($context['logger'])) {
                    $context['logger']->error('OpenAiModel exception: ' . $e->getMessage(), ['exception' => $e]);
                }
                return \Rumenx\PhpChatbot\Support\ChatResponse::fromString('[OpenAI] Exception: ' . $e->getMessage(), 'gpt-3.5-turbo');
            }
        }
    };
    $model->getResponse('test', ['logger' => $logger]);
    expect($logger->logs)->not->toBeEmpty();
});

it(
    'OpenAiModel returns error if cURL fails',
    function () {
        $model = new OpenAiModel(
            'dummy',
            'gpt-3.5-turbo',
            'http://localhost:9999/invalid'
        );
        $response = (string) $model->getResponse('test');
        expect($response)->toContain('OpenAI');
        expect($response)->toContain('Error:');
    }
);
