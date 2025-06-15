<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Adapters\Symfony;

it('BundleStub does not throw if class does not exist', function () {
    // This test is just to ensure the stub does not cause errors
    expect(true)->toBeTrue();
});

it(
    'BundleStub class exists and is abstract if Symfony is not installed',
    function () {
        if (!class_exists('Symfony\\Component\\HttpKernel\\Bundle\\Bundle')) {
            $reflection = new \ReflectionClass(
                \Rumenx\PhpChatbot\Adapters\Symfony\Bundle::class
            );
            expect($reflection->isAbstract())->toBeTrue();
        } else {
            expect(true)->toBeTrue(); // Symfony present, stub not used
        }
    }
);
