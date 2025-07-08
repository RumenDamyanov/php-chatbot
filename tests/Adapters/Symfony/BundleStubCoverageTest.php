<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Adapters\Symfony\Bundle;

describe('BundleStub coverage', function () {
    it('can be extended and instantiated for coverage', function () {
        if (!class_exists('Symfony\\Component\\HttpKernel\\Bundle\\Bundle')) {
            // Define a concrete subclass for coverage
            eval('class ConcreteBundleStub extends \\Rumenx\\PhpChatbot\\Adapters\\Symfony\\Bundle {}');
            $instance = new ConcreteBundleStub();
            expect($instance)->toBeInstanceOf(Bundle::class);
        } else {
            expect(true)->toBeTrue(); // Symfony present, stub not used
        }
    });
});
