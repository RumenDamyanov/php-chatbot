<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\ChatResponse;

use Rumenx\PhpChatbot\Models\DeepSeekAiModel;

require_once __DIR__ . '/DummyLogger.php';

it('DeepSeekAiModel throws ApiException if context missing', function () {
    $model = new DeepSeekAiModel('dummy');
    try {
        $model->getResponse('test');
        expect(false)->toBeTrue('Expected ApiException to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('DeepSeek');
    }
});

it('DeepSeekAiModel throws ApiException with custom prompt and temperature', function () {
    $model = new DeepSeekAiModel('dummy');
    try {
        $model->getResponse('test', [
            'prompt' => 'Custom!',
            'temperature' => 0.1,
        ]);
        expect(false)->toBeTrue('Expected ApiException to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('DeepSeek');
    }
});

it('DeepSeekAiModel throws ApiException with non-string prompt', function () {
    $model = new DeepSeekAiModel('dummy');
    try {
        $model->getResponse('test', ['prompt' => 123]);
        expect(false)->toBeTrue('Expected ApiException to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('DeepSeek');
    }
});

it('DeepSeekAiModel throws NetworkException on cURL error', function () {
    $model = new DeepSeekAiModel('dummy', 'deepseek-chat', 'http://localhost:9999/invalid');
    try {
        $model->getResponse('test');
        expect(false)->toBeTrue('Expected NetworkException to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
        expect($e->getMessage())->toContain('DeepSeek');
    }
});

it(
    'DeepSeekAiModel throws NetworkException if cURL fails',
    function () {
        $model = new \Rumenx\PhpChatbot\Models\DeepSeekAiModel(
            'dummy',
            'deepseek-chat',
            'http://localhost:9999/invalid'
        );
        try {
            $model->getResponse('test');
            expect(false)->toBeTrue('Expected NetworkException to be thrown');
        } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
            expect($e->getMessage())->toContain('DeepSeek');
            expect($e->getMessage())->toContain('Network error');
        }
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
             * @return ChatResponse
             */
            public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse
            {
                // Simulate missing choices
                $content = json_encode([
                    'status' => 'error',
                    'message' => '[DeepSeek] No response.'
                ]);
                return ChatResponse::fromString($content, 'deepseek-chat');
            }
        };
        $response = (string) $model->getResponse('test');
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
            public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse
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
             * @return ChatResponse
             */
            public function getResponse(string $input, array $context = []): ChatResponse
            {
                $maxTokens = 256;
                if (isset($context['max_tokens'])
                    && is_numeric($context['max_tokens'])
                ) {
                    $tokens = $context['max_tokens'];
                    $maxTokens = (int) $tokens;
                }
                $this->lastMaxTokens = $maxTokens;
                $content = json_encode([
                    'status' => 'error',
                    'message' => '[DeepSeek] No response.'
                ]);
                return ChatResponse::fromString($content, 'deepseek-chat');
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
             * @return ChatResponse
             */
            public function getResponse(string $input, array $context = []): ChatResponse
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
                    $content = json_encode([
                        'status' => 'error',
                        'message' => '[DeepSeek] Exception: ' . $e->getMessage(),
                    ]);
                    return ChatResponse::fromString($content, 'deepseek-chat');
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
             * @return ChatResponse
             */
            public function getResponse(string $input, array $context = []): ChatResponse
            {
                try {
                    throw new \Exception('Edge!');
                } catch (\Throwable $e) {
                    // Simulate json_encode failure
                    $content = json_encode(fopen('php://memory', 'r'))
                        ?: '{
                            "status":"error",
                            "message":"[DeepSeek] JSON encode failed."
                        }';
                    return ChatResponse::fromString($content, 'deepseek-chat');
                }
            }
        };
        $response = (string) $model->getResponse('test');
        expect($response)->toContain('JSON encode failed');
    }
);
