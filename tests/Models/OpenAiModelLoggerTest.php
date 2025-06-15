<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\OpenAiModel;
use Psr\Log\NullLogger;

require_once __DIR__ . '/DummyLogger.php';

it('OpenAiModel logs errors if logger is provided', function () {
    $logger = new DummyLogger();
    $model = new OpenAiModel('invalid-key');
    $context = ['logger' => $logger];
    $model->getResponse('test', $context);
    expect($logger->logs)->not->toBeEmpty();
});
