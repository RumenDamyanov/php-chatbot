<?php

namespace Rumenx\PhpChatbot\Contracts;

use Rumenx\PhpChatbot\Support\ChatResponse;

interface AiModelInterface
{
    /**
     * Get a response from the AI model based on user input and context.
     *
     * @param string $input
     * @param array<string, mixed> $context
     * @return ChatResponse
     */
    public function getResponse(string $input, array $context = []): ChatResponse;
}
