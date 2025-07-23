<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rumenx\PhpChatbot\Middleware\ChatMessageFilterMiddleware;

it(
    'appends system instructions to context',
    function () {
        $instructions = [
            'Avoid sharing external links.',
            'Use appropriate language.'
        ];
        $middleware = new ChatMessageFilterMiddleware(
            $instructions,
            ['badword1', 'badword2'],
            ['stupid'],
            '/https?:\/\/[\w\.-]+/i'
        );
        $result = $middleware->handle('Hello!', []);
        expect($result['context']['system_instructions'])
            ->toContain('Avoid sharing external links.');
        expect($result['context']['system_instructions'])
            ->toContain('Use appropriate language.');
    }
);

it(
    'filters links and profanity',
    function () {
        $middleware = new ChatMessageFilterMiddleware(
            [],
            ['badword1', 'badword2'],
            [],
            '/https?:\/\/[\w\.-]+/i'
        );
        $result = $middleware->handle('Check this: http://example.com badword1', []);
        expect($result['message'])->not->toContain('http://example.com');
        expect($result['message'])->toContain('[link removed]');
        expect($result['message'])->toContain('[censored]');
    }
);

it(
    'flags aggression',
    function () {
        $middleware = new ChatMessageFilterMiddleware(
            [],
            [],
            ['stupid'],
            '/https?:\/\/[\w\.-]+/i'
        );
        $result = $middleware->handle('You are stupid!', []);
        expect($result['message'])->toContain('respectful language');
    }
);

it(
    'does not add system instructions if none configured',
    function () {
        $middleware = new ChatMessageFilterMiddleware(
            [],
            [],
            [],
            '/https?:\/\/[\w\.-]+/i'
        );
        $result = $middleware->handle('Hello!', []);
        expect($result['context'])->not->toHaveKey('system_instructions');
    }
);
