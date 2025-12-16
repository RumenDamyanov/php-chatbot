<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\OpenAiModel;
use Psr\Log\NullLogger;

require_once __DIR__ . '/DummyLogger.php';

it('OpenAiModel throws ApiException with invalid key', function () {
    $logger = new DummyLogger();
    $model = new OpenAiModel('invalid-key');
    $context = ['logger' => $logger];
    try {
        $model->getResponse('test', $context);
        expect(false)->toBeTrue('Expected ApiException to be thrown');
    } catch (\Rumenx\PhpChatbot\Exceptions\ApiException $e) {
        expect($e->getMessage())->toContain('OpenAI');
    } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException $e) {
        expect($e->getMessage())->toContain('OpenAI');
    }
});
