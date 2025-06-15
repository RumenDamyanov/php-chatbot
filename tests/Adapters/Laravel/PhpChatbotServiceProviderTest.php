<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Adapters\Laravel\PhpChatbotServiceProvider;

it('can instantiate the Laravel adapter', function () {
    $provider = new PhpChatbotServiceProvider();
    expect($provider)->toBeInstanceOf(PhpChatbotServiceProvider::class);
    // Test register and boot methods (no-op stubs)
    expect(fn() => $provider->register())->not->toThrow(Exception::class);
    expect(fn() => $provider->boot())->not->toThrow(Exception::class);
});
