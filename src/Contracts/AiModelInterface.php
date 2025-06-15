<?php

namespace Rumenx\PhpChatbot\Contracts;

interface AiModelInterface
{
    /**
     * Get a response from the AI model based on user input and context.
     *
     * @param string $input
     * @param array<string, mixed> $context
     * @return string
     */
    public function getResponse(string $input, array $context = []): string;
}
