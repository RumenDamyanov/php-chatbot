<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Contracts;

/**
 * Streamable Model Interface for the php-chatbot package.
 *
 * This interface extends AiModelInterface to provide streaming capabilities
 * for AI models that support real-time response generation.
 *
 * @category Contracts
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT License (https://opensource.org/licenses/MIT)
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
interface StreamableModelInterface extends AiModelInterface
{
    /**
     * Get streaming response as a Generator.
     *
     * This method returns a Generator that yields response chunks as they
     * become available from the AI provider. This enables real-time streaming
     * of responses without waiting for the complete response.
     *
     * @param string               $input   The user input/prompt.
     * @param array<string, mixed> $context Optional context for the request.
     *
     * @return \Generator<int, string> Generator yielding response chunks.
     */
    public function getStreamingResponse(string $input, array $context = []): \Generator;

    /**
     * Check if the provider supports streaming.
     *
     * This method indicates whether the current AI provider/model supports
     * streaming responses. Some models or configurations may not support
     * streaming functionality.
     *
     * @return bool True if streaming is supported, false otherwise.
     */
    public function supportsStreaming(): bool;
}
