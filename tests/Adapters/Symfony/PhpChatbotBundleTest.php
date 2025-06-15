<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Adapters\Symfony\PhpChatbotBundle;

it('can instantiate the Symfony adapter', function () {
    $bundle = new PhpChatbotBundle();
    expect($bundle)->toBeInstanceOf(PhpChatbotBundle::class);
    // Test build method (no-op stub)
    expect(fn() => $bundle->build(null))->not->toThrow(Exception::class);
});
