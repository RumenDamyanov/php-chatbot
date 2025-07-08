<?php
declare(strict_types=1);

require_once __DIR__ . '/../../FrameworkStubs/Illuminate/Support/ServiceProvider.php';

// Provide a config() helper for the test environment that returns a valid model class
if (!function_exists('config')) {
    /**
     * Return a valid model class for the provider test.
     *
     * @param string|null $key
     * @return array
     */
    function config($key = null)
    {
        return [
            'model' => \Rumenx\PhpChatbot\Models\DefaultAiModel::class
        ];
    }
}

use Rumenx\PhpChatbot\Adapters\Laravel\PhpChatbotServiceProvider;

it(
    'can instantiate the Laravel adapter',
    function () {
        $provider = new PhpChatbotServiceProvider();
        expect($provider)->toBeInstanceOf(PhpChatbotServiceProvider::class);
        // Test register and boot methods (no-op stubs)
        expect(fn() => $provider->register())->not->toThrow(Exception::class);
        expect(fn() => $provider->boot())->not->toThrow(Exception::class);
    }
);
