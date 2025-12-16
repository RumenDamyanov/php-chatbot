<?php

declare(strict_types=1);
/**
 * PhpChatbotServiceProviderStubTest
 *
 * PHP version 8.0+
 *
 * @category Test
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
declare(strict_types=1);

use Rumenx\PhpChatbot\Adapters\Laravel\PhpChatbotServiceProviderStub;

describe('PhpChatbotServiceProviderStub', function () {
    it('can be instantiated', function () {
        $provider = new PhpChatbotServiceProviderStub();
        expect($provider)->toBeInstanceOf(PhpChatbotServiceProviderStub::class);
    });
    it('register() does nothing and does not throw', function () {
        $provider = new PhpChatbotServiceProviderStub();
        expect(fn () => $provider->register())->not->toThrow(Exception::class);
    });
    it('boot() does nothing and does not throw', function () {
        $provider = new PhpChatbotServiceProviderStub();
        expect(fn () => $provider->boot())->not->toThrow(Exception::class);
    });
    it(
        'register() and boot() can be called multiple times safely',
        function () {
            $provider = new PhpChatbotServiceProviderStub();
            expect(fn () => $provider->register())->not->toThrow(Exception::class);
            expect(fn () => $provider->register())->not->toThrow(Exception::class);
            expect(fn () => $provider->boot())->not->toThrow(Exception::class);
            expect(fn () => $provider->boot())->not->toThrow(Exception::class);
        }
    );
    it(
        'has no unexpected public methods or properties',
        function () {
            $reflection = new ReflectionClass(PhpChatbotServiceProviderStub::class);
            $publicMethods = array_map(
                fn ($m) => $m->getName(),
                $reflection->getMethods(ReflectionMethod::IS_PUBLIC)
            );
            sort($publicMethods);
            expect($publicMethods)->toBe(['boot', 'register']);
            $publicProperties = $reflection->getProperties(
                ReflectionProperty::IS_PUBLIC
            );
            expect($publicProperties)->toBe([]);
        }
    );
    it(
        'class docblock exists and is correct',
        function () {
            $reflection = new ReflectionClass(PhpChatbotServiceProviderStub::class);
            $doc = $reflection->getDocComment();
            expect($doc)->not->toBeFalse();
            expect($doc)->toContain('PhpChatbotServiceProviderStub');
        }
    );
});
