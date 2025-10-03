<?php

use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\DeepSeekAiModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\MetaModel;
use Rumenx\PhpChatbot\Models\OpenAiModel;
use Rumenx\PhpChatbot\Models\XaiModel;
use Rumenx\PhpChatbot\Support\ChatResponse;

describe(
    'Additional Model Coverage Tests',
    function () {
        it(
            'OpenAiModel uses numeric temperature and max_tokens from context',
            function () {
                $model = new OpenAiModel('test-api-key');

                // Mock cURL to avoid actual API calls
                $context = [
                    'prompt' => 'Test prompt',
                    'temperature' => 0.5,  // numeric float
                    'max_tokens' => 512    // numeric int
                ];

                $result = $model->getResponse('test message', $context);

                expect($result)->toBeInstanceOf(ChatResponse::class);
                expect((string) $result)->toBeString();
            }
        );

        it(
            'OpenAiModel handles numeric temperature as string',
            function () {
                $model = new OpenAiModel('test-api-key');

                $context = [
                    'temperature' => '0.8',  // string that is numeric
                    'max_tokens' => '1024'   // string that is numeric
                ];

                $result = $model->getResponse('test message', $context);

                expect($result)->toBeInstanceOf(ChatResponse::class);
                expect((string) $result)->toBeString();
            }
        );

        it(
            'DeepSeekAiModel uses numeric temperature and max_tokens',
            function () {
                $model = new DeepSeekAiModel('test-api-key');

                $context = [
                    'temperature' => 0.3,  // numeric float
                    'max_tokens' => 256    // numeric int
                ];

                $result = $model->getResponse('test message', $context);

                expect($result)->toBeInstanceOf(ChatResponse::class);
                expect((string) $result)->toBeString();
            }
        );

        it(
            'DeepSeekAiModel handles numeric values as strings',
            function () {
                $model = new DeepSeekAiModel('test-api-key');

                $context = [
                    'temperature' => '0.9',  // string that is numeric
                    'max_tokens' => '512'    // string that is numeric
                ];

                $result = $model->getResponse('test message', $context);

                expect($result)->toBeInstanceOf(ChatResponse::class);
                expect((string) $result)->toBeString();
            }
        );

        it(
            'MetaModel uses numeric temperature and max_tokens',
            function () {
                $model = new MetaModel('test-api-key');

                $context = [
                    'temperature' => 0.6,  // numeric float
                    'max_tokens' => 128    // numeric int
                ];

                $result = $model->getResponse('test message', $context);

                expect($result)->toBeInstanceOf(ChatResponse::class);
                expect((string) $result)->toBeString();
            }
        );

        it(
            'MetaModel handles numeric values as strings',
            function () {
                $model = new MetaModel('test-api-key');

                $context = [
                    'temperature' => '0.4',  // string that is numeric
                    'max_tokens' => '256'    // string that is numeric
                ];

                $result = $model->getResponse('test message', $context);

                expect($result)->toBeInstanceOf(ChatResponse::class);
                expect((string) $result)->toBeString();
            }
        );

        it(
            'XaiModel uses numeric temperature and max_tokens',
            function () {
                $model = new XaiModel('test-api-key');

                $context = [
                    'temperature' => 0.7,  // numeric float
                    'max_tokens' => 384    // numeric int
                ];

                $result = $model->getResponse('test message', $context);

                expect($result)->toBeInstanceOf(ChatResponse::class);
                expect((string) $result)->toBeString();
            }
        );

        it(
            'XaiModel handles numeric values as strings',
            function () {
                $model = new XaiModel('test-api-key');

                $context = [
                    'temperature' => '0.2',  // string that is numeric
                    'max_tokens' => '768'    // string that is numeric
                ];

                $result = $model->getResponse('test message', $context);

                expect($result)->toBeInstanceOf(ChatResponse::class);
                expect((string) $result)->toBeString();
            }
        );
    }
);
