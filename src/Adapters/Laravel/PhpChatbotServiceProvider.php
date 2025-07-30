<?php

/**
 * Laravel service provider for php-chatbot.
 *
 * Registers the PhpChatbot singleton and publishes config, views, and assets.
 * Note: This class requires Laravel's ServiceProvider and helpers at runtime.
 *
 * @category   ServiceProvider
 * @package    Rumenx\PhpChatbot
 * @author     Rumen Damyanov <contact@rumenx.com>
 * @license    MIT License (https://opensource.org/licenses/MIT)
 * @link       https://github.com/RumenDamyanov/php-chatbot
 * @phpversion 8.3
 */

namespace Rumenx\PhpChatbot\Adapters\Laravel;

use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Models\ModelFactory;

if (class_exists('Illuminate\\Support\\ServiceProvider')) {
    /**
     * Laravel service provider for php-chatbot.
     *
     * @category ServiceProvider
     * @package  Rumenx\PhpChatbot
     * @author   Rumen Damyanov <contact@rumenx.com>
     * @license  MIT License (https://opensource.org/licenses/MIT)
     * @link     https://github.com/RumenDamyanov/php-chatbot
     */
    class PhpChatbotServiceProvider extends \Illuminate\Support\ServiceProvider
    {
        /**
         * Register the PhpChatbot singleton in the Laravel service container.
         *
         * @return void
         */
        public function register(): void
        {
            $this->app->singleton(
                PhpChatbot::class,
                function ($app) {
                    $config = function_exists('config') ? config('phpchatbot') : [];
                    $model = ModelFactory::make($config);
                    return new PhpChatbot($model, $config);
                }
            );
        }

        /**
         * Publish config, views, and assets for customization.
         * Adds a 'php-chatbot' tag for vendor:publish.
         * Supports booting callbacks for modern Laravel.
         *
         * @return void
         */
        public function boot(): void
        {
            $publish = [];
            if (function_exists('config_path')) {
                $publish[
                    __DIR__ . '/../../Config/phpchatbot.php'
                ] = config_path(
                    'phpchatbot.php'
                );
            }
            if (function_exists('resource_path')) {
                $publish[
                    __DIR__ . '/../../../resources/views'
                ] = resource_path(
                    'views/vendor/php-chatbot'
                );
            }
            if (function_exists('public_path')) {
                $publish[
                    __DIR__ . '/../../../resources/css'
                ] = public_path(
                    'vendor/php-chatbot/css'
                );
            }
            if (!empty($publish) && method_exists($this, 'publishes')) {
                // Single vendor tag for all assets
                $this->publishes(
                    $publish,
                    'php-chatbot'
                );
                // Legacy tags for backward compatibility
                if (isset($publish[__DIR__ . '/../../Config/phpchatbot.php'])) {
                    $this->publishes(
                        [
                            __DIR__ . '/../../Config/phpchatbot.php'
                                => config_path('phpchatbot.php')
                        ],
                        'config'
                    );
                }
                if (isset($publish[__DIR__ . '/../../../resources/views'])) {
                    $this->publishes(
                        [
                            __DIR__ . '/../../../resources/views'
                                => resource_path('views/vendor/php-chatbot')
                        ],
                        'views'
                    );
                }
                if (isset($publish[__DIR__ . '/../../../resources/css'])) {
                    $this->publishes(
                        [
                            __DIR__ . '/../../../resources/css'
                                => public_path('vendor/php-chatbot/css')
                        ],
                        'assets'
                    );
                }
            }
            // Support for booting callbacks (Laravel 9+)
            if (method_exists($this, 'callAfterResolving')) {
                $this->callAfterResolving(
                    PhpChatbot::class,
                    function ($chatbot, $app) {
                        // Place for any booting logic or event hooks if needed
                    }
                );
            }
        }
    }
}
