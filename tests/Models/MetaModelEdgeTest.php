<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\ChatResponse;

use Rumenx\PhpChatbot\Models\MetaModel;

it('MetaModel throws exception if context missing', function () {
    $model = new MetaModel('dummy');
    try {
        $model->getResponse('test');
        expect(false)->toBeTrue('Expected exception to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException | \Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('Meta');
    }
});

it('MetaModel throws exception with custom prompt', function () {
    $model = new MetaModel('dummy');
    try {
        $model->getResponse('test', ['prompt' => 'Custom!']);
        expect(false)->toBeTrue('Expected exception to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException | \Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('Meta');
    }
});

it('MetaModel throws exception with non-string prompt', function () {
    $model = new MetaModel('dummy');
    try {
        $model->getResponse('test', ['prompt' => 123]);
        expect(false)->toBeTrue('Expected exception to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException | \Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('Meta');
    }
});

it('MetaModel throws NetworkException on cURL error', function () {
    $model = new MetaModel('dummy', 'llama-3-70b', 'http://localhost:9999/invalid');
    try {
        $model->getResponse('test');
        expect(false)->toBeTrue('Expected NetworkException to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
        expect($e->getMessage())->toContain('Meta');
    }
});

it('MetaModel returns fallback if choices missing', function () {
    $model = new class('dummy') extends MetaModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            // Simulate missing choices
            return \Rumenx\PhpChatbot\Support\ChatResponse::fromString('[Meta] No response.', 'llama-3-70b');
        }
    };
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('MetaModel handles exception', function () {
    $model = new class('dummy') extends MetaModel {
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
    'MetaModel handles max_tokens and temperature in context',
    function () {
        $model = new class('dummy') extends MetaModel {
            /**
             * Stores the last data array for test assertions.
             *
             * @var array
             */
            public $lastData = [];
            /**
             * Test override to capture context values.
             *
             * @param string $input   The input string.
             * @param array  $context The context array.
             *
             * @return \Rumenx\PhpChatbot\Support\ChatResponse
             */
            public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse
            {
                $tokens = 128;
                $temperature = 0.7;
                if (isset($context['max_tokens'])
                    && is_numeric($context['max_tokens'])
                ) {
                    $tokens = (int) $context['max_tokens'];
                }
                if (isset($context['temperature'])
                    && is_numeric($context['temperature'])
                ) {
                    $temperature = (float) $context['temperature'];
                }
                $this->lastData = [
                    'max_tokens' => $tokens,
                    'temperature' => $temperature,
                ];
                return \Rumenx\PhpChatbot\Support\ChatResponse::fromString('[Meta] No response.', 'llama-3-70b');
            }
        };
        $response = $model->getResponse(
            'test',
            [
                'max_tokens' => 256,
                'temperature' => 0.9,
            ]
        );
        expect($model->lastData['max_tokens'])->toBe(256);
        expect($model->lastData['temperature'])->toBe(0.9);
    }
);

it(
    'MetaModel returns content if API response is valid',
    function () {
        $model = new class('dummy') extends MetaModel {
            /**
             * Test override to simulate valid API response.
             *
             * @param string $input   The input string.
             * @param array  $context The context array.
             *
             * @return \Rumenx\PhpChatbot\Support\ChatResponse
             */
            public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse
            {
                $response = [
                    'choices' => [
                        [
                            'message' => [
                                'content' => 'Hello from Meta!'
                            ]
                        ]
                    ]
                ];
                if (is_array($response)
                    && isset($response['choices'][0]['message']['content'])
                    && is_string($response['choices'][0]['message']['content'])
                ) {
                    return \Rumenx\PhpChatbot\Support\ChatResponse::fromString($response['choices'][0]['message']['content'], 'llama-3-70b');
                }
                return \Rumenx\PhpChatbot\Support\ChatResponse::fromString('[Meta] No response.', 'llama-3-70b');
            }
        };
        $response = (string) $model->getResponse('test');
        expect($response)->toBe('Hello from Meta!');
    }
);

it(
    'MetaModel throws NetworkException if cURL fails',
    function () {
        $model = new MetaModel(
            'dummy',
            'llama-3-70b',
            'http://localhost:9999/invalid'
        );
        try {
            $model->getResponse('test');
            expect(false)->toBeTrue('Expected NetworkException to be thrown');
        } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
            expect($e->getMessage())->toContain('Meta');
            expect($e->getMessage())->toContain('Network error');
        }
    }
);

it(
    'MetaModel catch block returns exception message',
    function () {
        $model = new class('dummy') extends MetaModel {
            /**
             * Simulate catch block for exception message.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return \Rumenx\PhpChatbot\Support\ChatResponse
             */
            public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse
            {
                try {
                    throw new \Exception('Edge case!');
                } catch (\Throwable $e) {
                    return \Rumenx\PhpChatbot\Support\ChatResponse::fromString('[Meta] Exception: ' . $e->getMessage(), 'llama-3-70b');
                }
            }
        };
        $response = (string) $model->getResponse('test');
        expect($response)->toBe('[Meta] Exception: Edge case!');
    }
);

it(
    'MetaModel returns fallback if choices key missing',
    function () {
        $model = new class('dummy') extends MetaModel {
            /**
             * Simulate API returning JSON without choices key.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return \Rumenx\PhpChatbot\Support\ChatResponse
             */
            public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse
            {
                $response = json_encode(['foo' => 'bar']);
                $decoded = json_decode($response, true);
                if (is_array($decoded)
                    && isset($decoded['choices'][0]['message']['content'])
                    && is_string($decoded['choices'][0]['message']['content'])
                ) {
                    return \Rumenx\PhpChatbot\Support\ChatResponse::fromString($decoded['choices'][0]['message']['content'], 'llama-3-70b');
                }
                return \Rumenx\PhpChatbot\Support\ChatResponse::fromString('[Meta] No response.', 'llama-3-70b');
            }
        };
        $response = (string) $model->getResponse('test');
        expect($response)->toBe('[Meta] No response.');
    }
);
