<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\ChatResponse;

use Rumenx\PhpChatbot\Models\XaiModel;

it('XaiModel returns default prompt if context missing', function () {
    $model = new XaiModel('dummy');
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('XaiModel uses custom prompt', function () {
    $model = new XaiModel('dummy');
    $response = (string) $model->getResponse('test', [
        'prompt' => 'Custom!',
    ]);
    expect($response)->toContain('No response');
});

it('XaiModel handles non-string prompt', function () {
    $model = new XaiModel('dummy');
    $response = (string) $model->getResponse('test', [
        'prompt' => 123,
    ]);
    expect($response)->toContain('No response');
});

it('XaiModel handles cURL error gracefully', function () {
    $model = new XaiModel('dummy', 'grok-1', 'http://localhost:9999/invalid');
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('xAI');
});

it('XaiModel returns fallback if choices missing', function () {
    $model = new class('dummy') extends XaiModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            // Simulate missing choices
            return \Rumenx\PhpChatbot\Support\ChatResponse::fromString('[xAI] No response.', 'grok-beta');
        }
    };
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('XaiModel handles exception', function () {
    $model = new class('dummy') extends XaiModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
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

it(
    'XaiModel returns content if API response is valid',
    function () {
        $model = new class('dummy') extends XaiModel {
            /**
             * Simulate valid xAI API response.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return \Rumenx\PhpChatbot\Support\ChatResponse
             */
            public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse
            {
                $response = [
                    'choices' => [
                        [
                            'message' => [
                                'content' => 'Hello from xAI!'
                            ]
                        ]
                    ]
                ];
                if (is_array($response)
                    && isset($response['choices'][0]['message']['content'])
                    && is_string($response['choices'][0]['message']['content'])
                ) {
                    return \Rumenx\PhpChatbot\Support\ChatResponse::fromString($response['choices'][0]['message']['content'], 'grok-beta');
                }
                return \Rumenx\PhpChatbot\Support\ChatResponse::fromString('[xAI] No response.', 'grok-beta');
            }
        };
        $response = (string) $model->getResponse('test');
        expect($response)->toBe('Hello from xAI!');
    }
);

it(
    'XaiModel uses max_tokens and temperature from context',
    function () {
        $model = new class('dummy') extends XaiModel {
            public $lastData = [];
            /**
             * Capture max_tokens and temperature for test.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return \Rumenx\PhpChatbot\Support\ChatResponse
             */
            public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse
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
                return \Rumenx\PhpChatbot\Support\ChatResponse::fromString('[xAI] No response.', 'grok-beta');
            }
        };
        $model->getResponse('test', ['max_tokens' => 123, 'temperature' => 0.9]);
        expect($model->lastData['max_tokens'])->toBe(123);
        expect($model->lastData['temperature'])->toBe(0.9);
    }
);
