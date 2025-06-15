<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Adapters\Symfony\DependencyInjection\PhpChatbotExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

it('PhpChatbotExtension sets parameters from config', function () {
    $extension = new PhpChatbotExtension();
    $container = new ContainerBuilder();
    $configs = [['api_key' => 'abc', 'model' => 'gpt-test']];
    // Patch YamlFileLoader to avoid file IO
    $extension->load($configs, $container);
    expect($container->hasParameter('php_chatbot.api_key'))->toBeTrue();
    expect($container->getParameter('php_chatbot.api_key'))->toBe('abc');
    expect($container->getParameter('php_chatbot.model'))->toBe('gpt-test');
});
