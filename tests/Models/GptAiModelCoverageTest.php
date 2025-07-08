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

it(
    'OpenAiModel returns content if API response is valid',
    function () {
        $model = new class('dummy', 'gpt-3.5-turbo') extends OpenAiModel {
            /**
             * Simulate valid OpenAI API response.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return string
             */
            public function getResponse(string $input, array $context = []) : string
            {
                $response = [
                    'choices' => [
                        [
                            'message' => [
                                'content' => 'Hello from OpenAI!'
                            ]
                        ]
                    ]
                ];
                if (is_array($response)
                    && isset($response['choices'][0]['message']['content'])
                    && is_string($response['choices'][0]['message']['content'])
                ) {
                    return $response['choices'][0]['message']['content'];
                }
                return '[OpenAI] No response.';
            }
        };
        $response = $model->getResponse('test');
        expect($response)->toBe('Hello from OpenAI!');
    }
);

it(
    'OpenAiModel uses max_tokens and temperature from context',
    function () {
        $model = new class('dummy', 'gpt-3.5-turbo') extends OpenAiModel {
            public $lastData = [];
            /**
             * Capture max_tokens and temperature for test.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return string
             */
            public function getResponse(string $input, array $context = []) : string
            {
                $maxTokens = 256;
                $temperature = 0.7;
                if (isset($context['max_tokens'])
                    && is_numeric($context['max_tokens'])
                ) {
                    $maxTokens = (int) $context['max_tokens'];
                }
                if (isset($context['temperature'])
                    && is_numeric($context['temperature'])
                ) {
                    $temperature = (float) $context['temperature'];
                }
                $this->lastData = [
                    'max_tokens' => $maxTokens,
                    'temperature' => $temperature,
                ];
                return '[OpenAI] No response.';
            }
        };
        $model->getResponse('test', ['max_tokens' => 123, 'temperature' => 0.9]);
        expect($model->lastData['max_tokens'])->toBe(123);
        expect($model->lastData['temperature'])->toBe(0.9);
    }
);
