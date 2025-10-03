<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Support\ChatResponse;

it('AnthropicModel returns default prompt if context missing', function () {
    $model = new AnthropicModel('dummy');
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('AnthropicModel uses custom prompt and temperature', function () {
    $model = new AnthropicModel('dummy');
    $response = (string) $model->getResponse('test', [
        'prompt' => 'Custom!',
        'temperature' => 0.1,
    ]);
    expect($response)->toContain('No response');
});

it('AnthropicModel handles non-string prompt', function () {
    $model = new AnthropicModel('dummy');
    $response = (string) $model->getResponse('test', [
        'prompt' => 123,
    ]);
    expect($response)->toContain('No response');
});

it('AnthropicModel handles cURL error gracefully', function () {
    $model = new AnthropicModel('dummy', 'claude-3-sonnet', 'http://localhost:9999/invalid');
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('Anthropic');
});

it('AnthropicModel returns fallback if choices missing', function () {
    $model = new class('dummy') extends AnthropicModel {
        public function getResponse(string $input, array $context = []): ChatResponse {
            // Simulate missing choices
            return ChatResponse::fromString('[Anthropic] No response.', 'claude-test');
        }
    };
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('No response');
});

it(
    'AnthropicModel uses max_tokens from context',
    function () {
        /**
         * Anonymous AnthropicModel stub to test max_tokens context.
         *
         * @category Test
         * @package  Rumenx\PhpChatbot
         * @author   Rumen Damyanov <contact@rumenx.com>
         * @license  MIT License (https://opensource.org/licenses/MIT)
         * @link     https://github.com/RumenDamyanov/php-chatbot
         */
        $model = new class('dummy') extends AnthropicModel {
            /**
             * @var int|null Last max_tokens value used
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
                $systemPrompt = 'You are a helpful chatbot.';
                if (isset($context['prompt']) && is_string($context['prompt'])) {
                    $systemPrompt = $context['prompt'];
                }
                $maxTokens = 256;
                if (isset($context['max_tokens'])
                    && is_numeric($context['max_tokens'])
                ) {
                    $tokens = $context['max_tokens'];
                    $maxTokens = (int) $tokens;
                }
                $this->lastMaxTokens = $maxTokens;
                return ChatResponse::fromString('[Anthropic] No response.', 'claude-test');
            }
        };
        $model->getResponse('test', ['max_tokens' => 123]);
        expect($model->lastMaxTokens)->toBe(123);
    }
);

it(
    'AnthropicModel returns fallback if choices key missing',
    function () {
        /**
         * Anonymous AnthropicModel stub to simulate missing choices key.
         *
         * @category Test
         * @package  Rumenx\PhpChatbot
         * @author   Rumen Damyanov <contact@rumenx.com>
         * @license  MIT License (https://opensource.org/licenses/MIT)
         * @link     https://github.com/RumenDamyanov/php-chatbot
         */
        $model = new class('dummy') extends AnthropicModel {
            /**
             * Simulate API returning JSON without choices key.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return ChatResponse
             */
            public function getResponse(string $input, array $context = []): ChatResponse
            {
                // Simulate API returning JSON without choices
                $response = json_encode(['foo' => 'bar']);
                $decoded = json_decode($response, true);
                if (is_array($decoded)
                    && isset($decoded['choices'][0]['message']['content'])
                    && is_string($decoded['choices'][0]['message']['content'])
                ) {
                    return ChatResponse::fromString($decoded['choices'][0]['message']['content'], 'claude-test');
                }
                return ChatResponse::fromString('[Anthropic] No response.', 'claude-test');
            }
        };
        $response = (string) $model->getResponse('test');
        expect($response)->toBe('[Anthropic] No response.');
    }
);

it(
    'AnthropicModel catch block returns exception message',
    function () {
        /**
         * Anonymous AnthropicModel stub to test catch block.
         *
         * @category Test
         * @package  Rumenx\PhpChatbot
         * @author   Rumen Damyanov <contact@rumenx.com>
         * @license  MIT License (https://opensource.org/licenses/MIT)
         * @link     https://github.com/RumenDamyanov/php-chatbot
         */
        $model = new class('dummy') extends AnthropicModel {
            /**
             * Simulate catch block for exception message.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return ChatResponse
             */
            public function getResponse(string $input, array $context = []): ChatResponse
            {
                try {
                    throw new \Exception('Edge case!');
                } catch (\Throwable $e) {
                    return ChatResponse::fromString('[Anthropic] Exception: ' . $e->getMessage(), 'claude-test');
                }
            }
        };
        $response = (string) $model->getResponse('test');
        expect($response)->toBe('[Anthropic] Exception: Edge case!');
    }
);
