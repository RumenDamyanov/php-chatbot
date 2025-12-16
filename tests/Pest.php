<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Set up environment for tests to avoid SSL certificate issues on macOS
 * 
 * This enables a test mode that disables SSL verification when using dummy API keys.
 * Safe for testing as we're not making real authenticated API calls.
 */
function setupTestEnvironment(): void
{
    // Enable test mode to disable SSL verification (fixes macOS SIP certificate issues)
    putenv('PHP_CHATBOT_TEST_MODE=1');
}

// Call setup before each test
uses()->beforeEach(function () {
    setupTestEnvironment();
})->in(__DIR__);
