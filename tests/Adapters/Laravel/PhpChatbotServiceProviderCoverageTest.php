<?php

declare(strict_types=1);
/**
 * PhpChatbotServiceProviderCoverageTest
 *
 * PHP version 8.0+
 *
 * @category Test
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @copyright 2024 Rumen Damyanov
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
declare(strict_types=1);

namespace Rumenx\PhpChatbot\Tests\Adapters\Laravel;

require_once __DIR__ . '/../../FrameworkStubs/laravel_helpers.php';
require_once __DIR__ . '/../../FrameworkStubs/Illuminate/Support/ServiceProvider.php';

use PHPUnit\Framework\TestCase;
use Rumenx\PhpChatbot\Adapters\Laravel\PhpChatbotServiceProvider;
use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Models\ModelFactory;

/**
 * Coverage tests for PhpChatbotServiceProvider.
 *
 * @category Test
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/RumenDamyanov/php-chatbot
 * @covers   \Rumenx\PhpChatbot\Adapters\Laravel\PhpChatbotServiceProvider
 */
class PhpChatbotServiceProviderCoverageTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test that register binds the singleton as expected.
     *
     * @return void
     */
    public function testRegisterBindsSingleton(): void
    {
        $test = $this;
        $app = new class ($test) {
            public int $calls = 0;

            public function __construct(private TestCase $test)
            {
            }

            public function singleton(string $abstract, callable $concrete): void
            {
                $this->calls++;
                $this->test->assertSame(PhpChatbot::class, $abstract);
                $chatbot = $concrete(null);
                $this->test->assertInstanceOf(PhpChatbot::class, $chatbot);
            }
        };
        $provider = $this->getMockBuilder(PhpChatbotServiceProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $provider->app = $app;
        $provider->register();
        $this->assertSame(1, $app->calls);
    }

    /**
     * Test that boot publishes assets and calls after resolving.
     *
     * @return void
     */
    public function testBootPublishesAssetsAndCallsAfterResolving(): void
    {
        $provider = $this->getMockBuilder(PhpChatbotServiceProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['publishes', 'callAfterResolving'])
            ->getMock();
        $provider->expects($this->atLeastOnce())
            ->method('publishes');
        $provider->expects($this->once())
            ->method('callAfterResolving');
        $provider->boot();
    }
}
