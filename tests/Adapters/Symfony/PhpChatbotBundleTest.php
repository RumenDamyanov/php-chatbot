<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Adapters\Symfony\PhpChatbotBundle;

it('can instantiate the Symfony adapter', function () {
    if (!class_exists('Symfony\\Component\\HttpKernel\\Bundle\\Bundle')) {
        test()->markTestSkipped('Symfony not installed');
    }
    $bundle = new PhpChatbotBundle();
    expect($bundle)->toBeInstanceOf(PhpChatbotBundle::class);
}
);
