<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\OpenAiModel;

require_once __DIR__ . '/DummyLogger.php';

it('OpenAiModel setModel and getModel (direct)', function () {
    $model = new OpenAiModel('dummy-key', 'gpt-3.5-turbo');
    $model->setModel('gpt-4');
    expect($model->getModel())->toBe('gpt-4');
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
        public function getResponse(string $input, array $context = []): string {
            if (isset($context['logger'])) {
                $context['logger']->error('OpenAiModel API error: No response', ['response' => []]);
            }
            return json_encode(['status' => 'error', 'message' => '[OpenAI] No response.']);
        }
    };
    $model->getResponse('test', ['logger' => $logger]);
    expect($logger->logs)->not->toBeEmpty();
});

it('OpenAiModel logs exception with logger', function () {
    $logger = new DummyLogger();
    $model = new class('dummy', 'gpt-3.5-turbo') extends OpenAiModel {
        public function getResponse(string $input, array $context = []): string {
            try {
                throw new \Exception('Simulated');
            } catch (\Throwable $e) {
                if (isset($context['logger'])) {
                    $context['logger']->error('OpenAiModel exception: ' . $e->getMessage(), ['exception' => $e]);
                }
                return json_encode(['status' => 'error', 'message' => '[OpenAI] Exception: ' . $e->getMessage()]);
            }
        }
    };
    $model->getResponse('test', ['logger' => $logger]);
    expect($logger->logs)->not->toBeEmpty();
});
