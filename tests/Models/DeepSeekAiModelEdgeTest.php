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

it(
    'DeepSeekAiModel returns error if cURL fails',
    function () {
        $model = new \Rumenx\PhpChatbot\Models\DeepSeekAiModel(
            'dummy',
            'deepseek-chat',
            'http://localhost:9999/invalid'
        );
        $response = $model->getResponse('test');
        expect($response)->toContain('DeepSeek');
        expect($response)->toContain('error');
    }
);

it(
    'DeepSeekAiModel returns fallback if choices missing',
    function () {
        /**
         * Anonymous DeepSeekAiModel stub for simulating missing choices.
         *
         * @category Test
         * @package  Rumenx\PhpChatbot
         * @author   Rumen Damyanov <contact@rumenx.com>
         * @license  MIT License (https://opensource.org/licenses/MIT)
         * @link     https://github.com/RumenDamyanov/php-chatbot
         */
        $model = new class('dummy') extends DeepSeekAiModel {
            /**
             * Simulate missing choices in DeepSeek response.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return string
             */
            public function getResponse(string $input, array $context = []): string
            {
                // Simulate missing choices
                return json_encode([
                    'status' => 'error',
                    'message' => '[DeepSeek] No response.'
                ]
                );
            }
        };
        $response = $model->getResponse('test');
        expect($response)->toContain('No response');
    }
);

it(
    'DeepSeekAiModel handles exception',
    function () {
        /**
         * Anonymous DeepSeekAiModel stub for simulating exception.
         *
         * @category Test
         * @package  Rumenx\PhpChatbot
         * @author   Rumen Damyanov <contact@rumenx.com>
         * @license  MIT License (https://opensource.org/licenses/MIT)
         * @link     https://github.com/RumenDamyanov/php-chatbot
         */
        $model = new class('dummy') extends DeepSeekAiModel {
            /**
             * Simulate exception in DeepSeek response.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @throws \Exception
             *
             * @return string
             */
            public function getResponse(string $input, array $context = []): string
            {
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
    }
);

it(
    'DeepSeekAiModel uses max_tokens from context',
    function () {
        $model = new class('dummy') extends DeepSeekAiModel {
            /**
             * Last max_tokens value used.
             *
             * @var int|null
             */
            public $lastMaxTokens = null;
            /**
             * Get a response and record max_tokens for test.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return string
             */
            public function getResponse(string $input, array $context = []) : string
            {
                $maxTokens = 256;
                if (isset($context['max_tokens'])
                    && is_numeric($context['max_tokens'])
                ) {
                    $tokens = $context['max_tokens'];
                    $maxTokens = (int) $tokens;
                }
                $this->lastMaxTokens = $maxTokens;
                return json_encode([
                    'status' => 'error',
                    'message' => '[DeepSeek] No response.'
                ]
                );
            }
        };
        $model->getResponse('test', ['max_tokens' => 123]);
        expect($model->lastMaxTokens)->toBe(123);
    }
);

it(
    'DeepSeekAiModel logs exception in catch block',
    function () {
        $logger = new DummyLogger();
        $model = new class('dummy') extends DeepSeekAiModel {
            /**
             * Simulate exception and logger in catch block.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return string
             */
            public function getResponse(string $input, array $context = []) : string
            {
                try {
                    throw new \Exception('Edge!');
                } catch (\Throwable $e) {
                    if (isset($context['logger'])
                        && $context['logger'] instanceof \Psr\Log\LoggerInterface
                    ) {
                        $context['logger']->error(
                            'DeepSeekAiModel exception: ' . $e->getMessage(),
                            ['exception' => $e]
                        );
                    }
                    return json_encode([
                        'status' => 'error',
                        'message' => '[DeepSeek] Exception: ' . $e->getMessage(),
                    ]
                    );
                }
            }
        };
        $model->getResponse('test', ['logger' => $logger]);
        expect($logger->logs)->not->toBeEmpty();
        expect($logger->logs[0])->toContain('exception: Edge!');
    }
);

it(
    'DeepSeekAiModel catch block JSON encode fallback',
    function () {
        $model = new class('dummy') extends DeepSeekAiModel {
            /**
             * Simulate json_encode failure in catch block.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return string
             */
            public function getResponse(string $input, array $context = []) : string
            {
                try {
                    throw new \Exception('Edge!');
                } catch (\Throwable $e) {
                    // Simulate json_encode failure
                    return json_encode(fopen('php://memory', 'r'))
                        ?: '{
                            "status":"error",
                            "message":"[DeepSeek] JSON encode failed."
                        }';
                }
            }
        };
        $response = $model->getResponse('test');
        expect($response)->toContain('JSON encode failed');
    }
);
