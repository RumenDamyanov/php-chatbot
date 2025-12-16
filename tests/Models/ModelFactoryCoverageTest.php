<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\ModelFactory;
use Rumenx\PhpChatbot\Models\OpenAiModel;
use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\XaiModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\MetaModel;
use Rumenx\PhpChatbot\Models\DefaultAiModel;
use Rumenx\PhpChatbot\Support\ChatResponse;

class CustomModel implements \Rumenx\PhpChatbot\Contracts\AiModelInterface {
    public function getResponse(string $input, array $context = []): ChatResponse {
        return ChatResponse::fromString('custom', 'custom-model');
    }
    
    public function getModel(): string {
        return 'custom-model';
    }
    
    public function setModel(string $model): void {
        // No-op for test mock
    }
}

it('ModelFactory creates all core models with minimal config', function () {
    expect(ModelFactory::make(['model' => OpenAiModel::class, 'openai' => ['api_key' => 'k']]))->toBeInstanceOf(OpenAiModel::class);
    expect(ModelFactory::make(['model' => AnthropicModel::class, 'anthropic' => ['api_key' => 'k']]))->toBeInstanceOf(AnthropicModel::class);
    expect(ModelFactory::make(['model' => XaiModel::class, 'xai' => ['api_key' => 'k']]))->toBeInstanceOf(XaiModel::class);
    expect(ModelFactory::make(['model' => GeminiModel::class, 'gemini' => ['api_key' => 'k']]))->toBeInstanceOf(GeminiModel::class);
    expect(ModelFactory::make(['model' => MetaModel::class, 'meta' => ['api_key' => 'k']]))->toBeInstanceOf(MetaModel::class);
    expect(ModelFactory::make(['model' => DefaultAiModel::class]))->toBeInstanceOf(DefaultAiModel::class);
});

it('ModelFactory creates custom user model', function () {
    expect(ModelFactory::make(['model' => CustomModel::class]))->toBeInstanceOf(CustomModel::class);
});

it('ModelFactory throws on missing/invalid model', function () {
    expect(fn() => ModelFactory::make([]))->toThrow(InvalidArgumentException::class);
    expect(fn() => ModelFactory::make(['model' => 'NotAClass']))->toThrow(InvalidArgumentException::class);
});

it('ModelFactory passes extra config keys and ignores unknown', function () {
    $model = ModelFactory::make([
        'model' => OpenAiModel::class,
        'openai' => ['api_key' => 'k', 'model' => 'gpt', 'endpoint' => 'e'],
        'extra' => 'ignored',
    ]);
    expect($model)->toBeInstanceOf(OpenAiModel::class);
});

it('ModelFactory uses default values for missing keys', function () {
    $model = ModelFactory::make([
        'model' => OpenAiModel::class,
        'openai' => ['api_key' => 'k'],
    ]);
    expect($model)->toBeInstanceOf(OpenAiModel::class);
});
