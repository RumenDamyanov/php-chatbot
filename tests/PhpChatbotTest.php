<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Models\DefaultAiModel;
use Rumenx\PhpChatbot\Models\OpenAiModel;
use Rumenx\PhpChatbot\Contracts\AiModelInterface;

it('can instantiate PhpChatbot and use DefaultAiModel', function () {
    $model = new DefaultAiModel();
    $chatbot = new PhpChatbot($model, ['prompt' => 'Test prompt']);
    $response = $chatbot->ask('Hello!');
    expect($response)->toContain('default AI response');
    expect($chatbot->getModel())->toBeInstanceOf(AiModelInterface::class);
    $chatbot->setConfig(['prompt' => 'New prompt']);
    expect($chatbot->getConfig())->toHaveKey('prompt');
});

it('can swap models at runtime', function () {
    $default = new DefaultAiModel();
    $gpt = new OpenAiModel('dummy-key', 'gpt-3.5-turbo');
    $chatbot = new PhpChatbot($default);
    $chatbot->setModel($gpt);
    expect($chatbot->getModel())->toBeInstanceOf(OpenAiModel::class);
});

it('can get and set config', function () {
    $model = new DefaultAiModel();
    $chatbot = new PhpChatbot($model);
    $chatbot->setConfig(['foo' => 'bar']);
    expect($chatbot->getConfig())->toHaveKey('foo');
});
