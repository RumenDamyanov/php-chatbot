<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\MetaModel;

it('AnthropicModel getModel/setModel', function () {
    $model = new AnthropicModel('dummy', 'claude-3-sonnet');
    expect($model->getModel())->toBe('claude-3-sonnet');
    $model->setModel('claude-3-haiku');
    expect($model->getModel())->toBe('claude-3-haiku');
});

it('MetaModel throws exception on getResponse', function () {
    $model = new MetaModel('dummy', 'llama-3-8b');
    try {
        $model->getResponse('Hi!');
        expect(false)->toBeTrue('Expected exception to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException | \Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('Meta');
    }
});
