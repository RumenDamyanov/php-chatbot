<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\ModelFactory;

class CustomModel implements Rumenx\PhpChatbot\Contracts\AiModelInterface {
    public function getResponse(string $input, array $context = []): string {
        return 'custom:' . $input;
    }
}

it('ModelFactory creates custom user model', function () {
    $config = [ 'model' => CustomModel::class ];
    $model = ModelFactory::make($config);
    expect($model)->toBeInstanceOf(CustomModel::class);
    expect($model->getResponse('hi'))->toBe('custom:hi');
});
