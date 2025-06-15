<?php

declare(strict_types=1);

it('dev dependencies are present in composer.json', function () {
    $composer = json_decode(file_get_contents(__DIR__ . '/../../composer.json'), true);
    expect($composer)->toHaveKey('require-dev');
    $devDeps = $composer['require-dev'];
    expect($devDeps)->toHaveKey('pestphp/pest');
    expect($devDeps)->toHaveKey('phpstan/phpstan');
    expect($devDeps)->toHaveKey('squizlabs/php_codesniffer');
    // Optionally check for other dev packages
});
