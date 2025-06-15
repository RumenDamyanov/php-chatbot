<?php

declare(strict_types=1);

use Pest\TestSuite;
use Rumenx\PhpChatbot\Models\DefaultAiModel;

it('returns a response from the default AI model', function () {
    $model = new DefaultAiModel();
    $response = $model->getResponse('Hello!');
    expect($response)->toContain('[DefaultAI-');
});
