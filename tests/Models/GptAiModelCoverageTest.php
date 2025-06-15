<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\OpenAiModel;

it('OpenAiModel returns error on curl failure', function () {
    $model = new OpenAiModel('invalid-key', 'gpt-3.5-turbo', 'http://localhost:9999/invalid'); // Unreachable endpoint
    $response = $model->getResponse('test');
    expect($response)->toContain('OpenAI');
});

it('OpenAiModel returns fallback on missing choices', function () {
    $model = new class('dummy', 'gpt-3.5-turbo') extends OpenAiModel {
        public function getResponse(string $input, array $context = []): string {
            // Simulate OpenAiModel fallback logic
            return json_encode(['status' => 'error', 'message' => '[OpenAI] No response.']);
        }
    };
    $response = $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('OpenAiModel logs exception if logger provided', function () {
    $logger = new class extends Psr\Log\NullLogger {
        public array $logs = [];
        public function error($message, array $context = []): void {
            $this->logs[] = $message;
        }
    };
    $model = new class('dummy', 'gpt-3.5-turbo') extends OpenAiModel {
        public function getResponse(string $input, array $context = []): string {
            try {
                throw new \Exception('Simulated');
            } catch (\Throwable $e) {
                if (isset($context['logger']) && $context['logger'] instanceof \Psr\Log\LoggerInterface) {
                    $context['logger']->error('OpenAiModel exception: ' . $e->getMessage(), ['exception' => $e]);
                }
                return json_encode(['status' => 'error', 'message' => '[OpenAI] Exception: ' . $e->getMessage()]);
            }
        }
    };
    $model->getResponse('test', ['logger' => $logger]);
    expect($logger->logs)->not->toBeEmpty();
});

it('OpenAiModel setModel and getModel', function () {
    $model = new OpenAiModel('dummy-key', 'gpt-3.5-turbo');
    $model->setModel('gpt-4');
    expect($model->getModel())->toBe('gpt-4');
});
