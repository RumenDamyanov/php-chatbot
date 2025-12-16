<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Contracts\AiModelInterface;
use Rumenx\PhpChatbot\Support\ChatResponse;

class DummyModel implements AiModelInterface {
    public array $calls = [];
    public function getResponse(string $input, array $context = []): ChatResponse {
        $this->calls[] = [$input, $context];
        if (isset($context['throw'])) {
            throw new \RuntimeException('Dummy error');
        }
        return ChatResponse::fromString('dummy:' . $input, 'dummy-model');
    }
    
    public function getModel(): string {
        return 'dummy-model';
    }
    
    public function setModel(string $model): void {
        // No-op for test mock
    }
}

it('PhpChatbot ask() works with empty input and config', function () {
    $model = new DummyModel();
    $chatbot = new PhpChatbot($model);
    $response = $chatbot->ask('');
    expect($response)->toBe('dummy:');
});

it('PhpChatbot ask() context overrides config', function () {
    $model = new DummyModel();
    $chatbot = new PhpChatbot($model, ['foo' => 'bar']);
    $chatbot->ask('test', ['foo' => 'baz']);
    expect($model->calls[0][1]['foo'])->toBe('baz');
});

it('PhpChatbot setModel swaps model and delegates', function () {
    $model1 = new DummyModel();
    $model2 = new DummyModel();
    $chatbot = new PhpChatbot($model1);
    $chatbot->setModel($model2);
    $chatbot->ask('hi');
    expect($model1->calls)->toBeEmpty();
    expect($model2->calls)->not->toBeEmpty();
});

it('PhpChatbot setConfig with empty and large config', function () {
    $model = new DummyModel();
    $chatbot = new PhpChatbot($model, ['a' => 1]);
    $chatbot->setConfig([]);
    expect($chatbot->getConfig())->toBe([]);
    $large = array_fill(0, 100, 'x');
    $chatbot->setConfig($large);
    expect($chatbot->getConfig())->toBe($large);
});

it('PhpChatbot getModel/getConfig after mutation', function () {
    $model = new DummyModel();
    $chatbot = new PhpChatbot($model, ['foo' => 'bar']);
    $model2 = new DummyModel();
    $chatbot->setModel($model2);
    $chatbot->setConfig(['baz' => 'qux']);
    expect($chatbot->getModel())->toBe($model2);
    expect($chatbot->getConfig())->toBe(['baz' => 'qux']);
});

it('PhpChatbot ask() handles model exception', function () {
    $model = new DummyModel();
    $chatbot = new PhpChatbot($model);
    $result = null;
    try {
        $chatbot->ask('fail', ['throw' => true]);
    } catch (\RuntimeException $e) {
        $result = $e->getMessage();
    }
    expect($result)->toBe('Dummy error');
});
