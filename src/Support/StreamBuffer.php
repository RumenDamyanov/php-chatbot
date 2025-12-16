<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Support;

/**
 * Streaming Buffer Helper for the php-chatbot package.
 *
 * This class manages the buffer for streaming responses, handling
 * Server-Sent Events (SSE) parsing and chunk extraction.
 *
 * @category Support
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT License (https://opensource.org/licenses/MIT)
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class StreamBuffer
{
    /**
     * Internal buffer for incomplete data.
     *
     * @var string
     */
    private string $buffer = '';

    /**
     * Array to store parsed chunks.
     *
     * @var array<string>
     */
    private array $chunks = [];

    /**
     * Process incoming data chunk.
     *
     * @param string $data The incoming data chunk.
     *
     * @return void
     */
    public function add(string $data): void
    {
        $this->buffer .= $data;
        $lines = explode("\n", $this->buffer);

        // Keep the last incomplete line in buffer
        /** @var string $lastLine */
        $lastLine = array_pop($lines);
        $this->buffer = $lastLine;

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            if (str_starts_with($line, 'data: ')) {
                $json = substr($line, 6);

                if ($json === '[DONE]') {
                    continue;
                }

                $this->parseChunk($json);
            }
        }
    }

    /**
     * Parse JSON chunk and extract content.
     *
     * @param string $json The JSON string to parse.
     *
     * @return void
     */
    private function parseChunk(string $json): void
    {
        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            return;
        }

        // OpenAI format
        if (isset($decoded['choices'][0]['delta']['content'])) {
            $content = $decoded['choices'][0]['delta']['content'];
            if (is_string($content) && $content !== '') {
                $this->chunks[] = $content;
            }
        }

        // Anthropic format
        if (
            isset($decoded['type'])
            && $decoded['type'] === 'content_block_delta'
            && isset($decoded['delta']['text'])
        ) {
            $content = $decoded['delta']['text'];
            if (is_string($content) && $content !== '') {
                $this->chunks[] = $content;
            }
        }

        // Gemini format
        if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            $content = $decoded['candidates'][0]['content']['parts'][0]['text'];
            if (is_string($content) && $content !== '') {
                $this->chunks[] = $content;
            }
        }
    }

    /**
     * Check if there are chunks available.
     *
     * @return bool True if chunks are available.
     */
    public function hasChunks(): bool
    {
        return !empty($this->chunks);
    }

    /**
     * Get and remove the next chunk.
     *
     * @return string|null The next chunk or null if no chunks available.
     */
    public function getChunk(): ?string
    {
        return array_shift($this->chunks);
    }

    /**
     * Get all chunks and clear the buffer.
     *
     * @return array<string> All available chunks.
     */
    public function getAllChunks(): array
    {
        $chunks = $this->chunks;
        $this->chunks = [];
        return $chunks;
    }

    /**
     * Clear the buffer and chunks.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->buffer = '';
        $this->chunks = [];
    }
}
