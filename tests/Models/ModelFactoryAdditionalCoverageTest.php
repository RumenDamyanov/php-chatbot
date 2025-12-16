<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\ModelFactory;
use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\DeepSeekAiModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\MetaModel;
use Rumenx\PhpChatbot\Models\OpenAiModel;
use Rumenx\PhpChatbot\Models\XaiModel;

describe(
    'ModelFactory Additional Coverage Tests',
    function () {
        it(
            'ModelFactory creates AnthropicModel with custom model and endpoint',
            function () {
                $config = [
                    'model' => AnthropicModel::class,
                    'anthropic' => [
                        'api_key' => 'test-key',
                        'model' => 'claude-3-opus-20240229',
                        'endpoint' => 'https://custom.anthropic.com/v1/messages'
                    ]
                ];

                $model = ModelFactory::make($config);

                expect($model)->toBeInstanceOf(AnthropicModel::class);
            }
        );

        it(
            'ModelFactory creates DeepSeekAiModel with custom endpoint',
            function () {
                $config = [
                    'model' => DeepSeekAiModel::class,
                    'deepseek' => [
                        'api_key' => 'test-key',
                        'model' => 'deepseek-chat',
                        'endpoint' => 'https://custom.deepseek.com/v1/chat'
                    ]
                ];

                $model = ModelFactory::make($config);

                expect($model)->toBeInstanceOf(DeepSeekAiModel::class);
            }
        );

        it(
            'ModelFactory creates GeminiModel with custom model and endpoint',
            function () {
                $config = [
                    'model' => GeminiModel::class,
                    'gemini' => [
                        'api_key' => 'test-key',
                        'model' => 'gemini-pro-vision',
                        'endpoint' => 'https://custom.googleapis.com/v1beta/models'
                    ]
                ];

                $model = ModelFactory::make($config);

                expect($model)->toBeInstanceOf(GeminiModel::class);
            }
        );

        it(
            'ModelFactory creates MetaModel with custom model and endpoint',
            function () {
                $config = [
                    'model' => MetaModel::class,
                    'meta' => [
                        'api_key' => 'test-key',
                        'model' => 'llama-2-70b-chat',
                        'endpoint' => 'https://custom.meta.com/v1/chat/completions'
                    ]
                ];

                $model = ModelFactory::make($config);

                expect($model)->toBeInstanceOf(MetaModel::class);
            }
        );

        it(
            'ModelFactory creates OpenAiModel with custom model and endpoint',
            function () {
                $config = [
                    'model' => OpenAiModel::class,
                    'openai' => [
                        'api_key' => 'test-key',
                        'model' => 'gpt-4-turbo',
                        'endpoint' => 'https://custom.openai.com/v1/chat/completions'
                    ]
                ];

                $model = ModelFactory::make($config);

                expect($model)->toBeInstanceOf(OpenAiModel::class);
            }
        );

        it(
            'ModelFactory creates XaiModel with custom model and endpoint',
            function () {
                $config = [
                    'model' => XaiModel::class,
                    'xai' => [
                        'api_key' => 'test-key',
                        'model' => 'grok-2',
                        'endpoint' => 'https://custom.x.ai/v1/chat/completions'
                    ]
                ];

                $model = ModelFactory::make($config);

                expect($model)->toBeInstanceOf(XaiModel::class);
            }
        );
    }
);
