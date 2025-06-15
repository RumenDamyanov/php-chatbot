<?php

declare(strict_types=1);

it('config file returns expected array', function () {
    $config = require __DIR__ . '/../../src/Config/phpchatbot.php';
    expect($config)->toBeArray();
    expect($config)->toHaveKey('model');
    expect($config)->toHaveKey('prompt');
    expect($config)->toHaveKey('language');
    expect($config)->toHaveKey('tone');
    expect($config)->toHaveKey('rate_limit');
    expect($config)->toHaveKey('allowed_scripts');
    expect($config)->toHaveKey('emojis');
    expect($config)->toHaveKey('deescalate');
    expect($config)->toHaveKey('funny');
});
