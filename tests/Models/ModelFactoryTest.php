<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\ModelFactory;
use Rumenx\PhpChatbot\Models\OpenAiModel;
use Rumenx\PhpChatbot\Models\DefaultAiModel;
use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\XaiModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\MetaModel;

it('ModelFactory creates OpenAiModel', function () {
    $config = [
        'model' => OpenAiModel::class,
        'openai' => [ 'api_key' => 'dummy', 'model' => 'gpt-3.5-turbo' ]
    ];
    $model = ModelFactory::make($config);
    expect($model)->toBeInstanceOf(OpenAiModel::class);
});

it('ModelFactory creates DefaultAiModel', function () {
    $config = [ 'model' => DefaultAiModel::class ];
    $model = ModelFactory::make($config);
    expect($model)->toBeInstanceOf(DefaultAiModel::class);
});

it('ModelFactory creates AnthropicModel', function () {
    $config = [ 'model' => AnthropicModel::class, 'anthropic' => [ 'api_key' => 'dummy' ] ];
    $model = ModelFactory::make($config);
    expect($model)->toBeInstanceOf(AnthropicModel::class);
});

it('ModelFactory creates XaiModel', function () {
    $config = [ 'model' => XaiModel::class, 'xai' => [ 'api_key' => 'dummy' ] ];
    $model = ModelFactory::make($config);
    expect($model)->toBeInstanceOf(XaiModel::class);
});

it('ModelFactory creates GeminiModel', function () {
    $config = [ 'model' => GeminiModel::class, 'gemini' => [ 'api_key' => 'dummy' ] ];
    $model = ModelFactory::make($config);
    expect($model)->toBeInstanceOf(GeminiModel::class);
});

it('ModelFactory creates MetaModel', function () {
    $config = [ 'model' => MetaModel::class, 'meta' => [ 'api_key' => 'dummy' ] ];
    $model = ModelFactory::make($config);
    expect($model)->toBeInstanceOf(MetaModel::class);
});

it('ModelFactory throws on invalid class', function () {
    $config = [ 'model' => 'NotAClass' ];
    expect(fn() => ModelFactory::make($config))->toThrow(InvalidArgumentException::class);
});

it('ModelFactory throws on missing model key', function () {
    $config = [];
    expect(fn() => ModelFactory::make($config))->toThrow(InvalidArgumentException::class);
});

it('ModelFactory does not throw on missing config for known model', function () {
    $config = ['model' => 'Rumenx\\PhpChatbot\\Models\\AnthropicModel'];
    expect(fn() => ModelFactory::make($config))->not->toThrow(InvalidArgumentException::class);
});

it('ModelFactory throws on unknown model', function () {
    $config = ['model' => 'UnknownModelClass'];
    expect(fn() => ModelFactory::make($config))->toThrow(InvalidArgumentException::class);
});
