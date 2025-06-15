<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\OpenAiModel;
use Psr\Log\NullLogger;

// cURL mocking helper
function mock_curl_success($expectedContent)
{
    // Save original functions
    if (!function_exists('__original_curl_exec')) {
        runkit_function_rename('curl_exec', '__original_curl_exec');
        runkit_function_add('curl_exec', '$ch', 'return json_encode(["choices" => [["message" => ["content" => "' . $expectedContent . '"]]]]);');
    }
    if (!function_exists('__original_curl_close')) {
        runkit_function_rename('curl_close', '__original_curl_close');
        runkit_function_add('curl_close', '$ch', 'return true;');
    }
}

function restore_curl_functions()
{
    if (function_exists('__original_curl_exec')) {
        runkit_function_remove('curl_exec');
        runkit_function_rename('__original_curl_exec', 'curl_exec');
    }
    if (function_exists('__original_curl_close')) {
        runkit_function_remove('curl_close');
        runkit_function_rename('__original_curl_close', 'curl_close');
    }
}

it('OpenAiModel returns content on cURL success', function () {
    $expected = 'Hello from OpenAI!';
    if (!function_exists('runkit_function_rename')) {
        $this->markTestSkipped('runkit7 extension required for cURL mocking.');
    }
    mock_curl_success($expected);
    $model = new OpenAiModel('dummy-key', 'gpt-3.5-turbo', 'http://localhost:9999/valid');
    $result = $model->getResponse('test');
    restore_curl_functions();
    expect($result)->toBe($expected);
});
