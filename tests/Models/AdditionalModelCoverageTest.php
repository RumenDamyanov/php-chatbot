<?php

declare(strict_types=1);

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
            'OpenAiModel throws exception with test API key',
            function () {
                $model = new OpenAiModel('test-api-key');

                $context = [
                    'prompt' => 'Test prompt',
                    'temperature' => 0.5,  // numeric float
                    'max_tokens' => 512    // numeric int
                ];

                try {
                    $model->getResponse('test message', $context);
                    expect(false)->toBeTrue('Expected exception to be thrown');
                } catch (\Rumenx\PhpChatbot\Exceptions\ApiException $e) {
                    expect($e->getMessage())->toContain('OpenAI');
                } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
                    expect($e->getMessage())->toContain('OpenAI');
                }
            }
        );

        it(
            'OpenAiModel throws exception with numeric string params',
            function () {
                $model = new OpenAiModel('test-api-key');

                $context = [
                    'temperature' => '0.8',  // string that is numeric
                    'max_tokens' => '1024'   // string that is numeric
                ];

                try {
                    $model->getResponse('test message', $context);
                    expect(false)->toBeTrue('Expected exception to be thrown');
                } catch (\Rumenx\PhpChatbot\Exceptions\ApiException $e) {
                    expect($e->getMessage())->toContain('OpenAI');
                } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
                    expect($e->getMessage())->toContain('OpenAI');
                }
            }
        );

        it(
            'DeepSeekAiModel throws exception with numeric temperature and max_tokens',
            function () {
                $model = new DeepSeekAiModel('test-api-key');

                $context = [
                    'temperature' => 0.3,  // numeric float
                    'max_tokens' => 256    // numeric int
                ];

                try {
                    $model->getResponse('test message', $context);
                    expect(false)->toBeTrue('Expected exception to be thrown');
                } catch (\Rumenx\PhpChatbot\Exceptions\ApiException | \Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
                    expect($e->getMessage())->toContain('DeepSeek');
                }
            }
        );

        it(
            'DeepSeekAiModel throws exception with numeric values as strings',
            function () {
                $model = new DeepSeekAiModel('test-api-key');

                $context = [
                    'temperature' => '0.9',  // string that is numeric
                    'max_tokens' => '512'    // string that is numeric
                ];

                try {
                    $model->getResponse('test message', $context);
                    expect(false)->toBeTrue('Expected exception to be thrown');
                } catch (\Rumenx\PhpChatbot\Exceptions\ApiException | \Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
                    expect($e->getMessage())->toContain('DeepSeek');
                }
            }
        );

        it(
            'MetaModel throws exception with numeric temperature and max_tokens',
            function () {
                $model = new MetaModel('test-api-key');

                $context = [
                    'temperature' => 0.6,  // numeric float
                    'max_tokens' => 128    // numeric int
                ];

                try {
                    $model->getResponse('test message', $context);
                    expect(false)->toBeTrue('Expected exception to be thrown');
                } catch (\Rumenx\PhpChatbot\Exceptions\ApiException | \Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
                    expect($e->getMessage())->toContain('Meta');
                }
            }
        );

        it(
            'MetaModel throws exception with numeric values as strings',
            function () {
                $model = new MetaModel('test-api-key');

                $context = [
                    'temperature' => '0.4',  // string that is numeric
                    'max_tokens' => '256'    // string that is numeric
                ];

                try {
                    $model->getResponse('test message', $context);
                    expect(false)->toBeTrue('Expected exception to be thrown');
                } catch (\Rumenx\PhpChatbot\Exceptions\ApiException | \Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
                    expect($e->getMessage())->toContain('Meta');
                }
            }
        );

        it(
            'XaiModel throws exception with numeric temperature and max_tokens',
            function () {
                $model = new XaiModel('test-api-key');

                $context = [
                    'temperature' => 0.7,  // numeric float
                    'max_tokens' => 384    // numeric int
                ];

                try {
                    $model->getResponse('test message', $context);
                    expect(false)->toBeTrue('Expected exception to be thrown');
                } catch (\Rumenx\PhpChatbot\Exceptions\ApiException | \Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
                    expect($e->getMessage())->toContain('xAI');
                }
            }
        );

        it(
            'XaiModel throws exception with numeric values as strings',
            function () {
                $model = new XaiModel('test-api-key');

                $context = [
                    'temperature' => '0.2',  // string that is numeric
                    'max_tokens' => '768'    // string that is numeric
                ];

                try {
                    $model->getResponse('test message', $context);
                    expect(false)->toBeTrue('Expected exception to be thrown');
                } catch (\Rumenx\PhpChatbot\Exceptions\ApiException | \Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
                    expect($e->getMessage())->toContain('xAI');
                }
            }
        );
    }
);
