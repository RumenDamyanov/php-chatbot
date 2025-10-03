<?php

use Rumenx\PhpChatbot\Support\HttpClientInterface;
use Rumenx\PhpChatbot\Support\CurlHttpClient;

test('HttpClientInterface exists and is an interface', function () {
    expect(interface_exists(HttpClientInterface::class))->toBeTrue();
});

test('HttpClientInterface has post method', function () {
    $reflection = new \ReflectionClass(HttpClientInterface::class);
    expect($reflection->hasMethod('post'))->toBeTrue();
});

test('HttpClientInterface post method has correct signature', function () {
    $reflection = new \ReflectionClass(HttpClientInterface::class);
    $method = $reflection->getMethod('post');
    
    expect($method->getNumberOfParameters())->toBe(4);
    expect($method->getNumberOfRequiredParameters())->toBe(3);
});

test('CurlHttpClient implements HttpClientInterface', function () {
    $reflection = new \ReflectionClass(CurlHttpClient::class);
    expect($reflection->implementsInterface(HttpClientInterface::class))->toBeTrue();
});
