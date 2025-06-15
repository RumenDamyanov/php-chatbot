<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Adapters\Laravel\PhpChatbotServiceProvider;

// Ensure the stub is loaded in non-Laravel environments
if (!class_exists(PhpChatbotServiceProvider::class)) {
    require_once __DIR__ . '/../../../src/Adapters/Laravel/PhpChatbotServiceProviderStub.php';
}

it('can instantiate the Laravel adapter', function () {
    $provider = new PhpChatbotServiceProvider();
    expect($provider)->toBeInstanceOf(PhpChatbotServiceProvider::class);
    // Test register and boot methods (no-op stubs)
    expect(fn() => $provider->register())->not->toThrow(Exception::class);
    expect(fn() => $provider->boot())->not->toThrow(Exception::class);
});
