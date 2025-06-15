<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\ModelFactory;

it('ModelFactory throws on missing model class', function () {
    expect(fn() => ModelFactory::make([]))->toThrow(InvalidArgumentException::class);
});

it('ModelFactory throws on non-existent class', function () {
    expect(fn() => ModelFactory::make(['model' => 'NotAClass']))->toThrow(InvalidArgumentException::class);
});
