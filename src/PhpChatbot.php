<?php

namespace Rumenx\PhpChatbot;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

/**
 * Class PhpChatbot
 *
 * The main chatbot orchestrator. Handles user input, context merging and delegates
 * response generation to the AI model.
 *
 * This class is final to encourage extension via composition, not inheritance.
 * For framework integration guidance, see README.md.
 *
 * @category AI
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/RumenDamyanov/php-chatbot
 * @final
 */
final class PhpChatbot
{
    /**
     * The AI model implementation.
     *
     * @var AiModelInterface
     */
    protected $model;

    /**
     * Default configuration for the chatbot.
     *
     * @var array<string, mixed>
     */
    protected $config;

    /**
     * Constructor for PhpChatbot.
     *
     * @param AiModelInterface     $model   The AI model implementation.
     * @param array<string, mixed> $config  Optional configuration for the chatbot.
     */
    public function __construct(
        AiModelInterface $model,
        array $config = []
    ) {
        $this->model = $model;
        $this->config = $config;
    }

    /**
     * Get a response from the chatbot.
     *
     * @param string               $input   The user input message.
     * @param array<string, mixed> $context Optional runtime context (merged with
     *                                      config).
     *
     * @return string The chatbot's reply.
     */
    public function ask(
        string $input,
        array $context = []
    ): string {
        $context = array_merge($this->config, $context);
        return $this->model->getResponse($input, $context);
    }

    /**
     * Get the current AI model instance.
     *
     * @return AiModelInterface
     */
    public function getModel(): AiModelInterface
    {
        return $this->model;
    }

    /**
     * Set a new AI model instance.
     *
     * @param AiModelInterface $model
     *
     * @return void
     */
    public function setModel(AiModelInterface $model): void
    {
        $this->model = $model;
    }

    /**
     * Get the chatbot configuration array.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set the chatbot configuration array.
     *
     * @param array<string, mixed> $config
     *
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * Get a streaming response from the chatbot.
     *
     * This method returns a Generator that yields response chunks as they
     * become available from the AI provider. This enables real-time streaming
     * of responses without waiting for the complete response.
     *
     * @param string               $input   The user input message.
     * @param array<string, mixed> $context Optional runtime context (merged
     *                                      with config).
     *
     * @return \Generator<int, string> Generator yielding response chunks.
     * @throws \RuntimeException If the current model doesn't support streaming.
     */
    public function askStream(
        string $input,
        array $context = []
    ): \Generator {
        if (!$this->model instanceof \Rumenx\PhpChatbot\Contracts\StreamableModelInterface) {
            throw new \RuntimeException(
                'Current AI model does not implement StreamableModelInterface. ' .
                'Streaming is not supported for this provider.'
            );
        }

        if (!$this->model->supportsStreaming()) {
            throw new \RuntimeException(
                'Streaming is not available for the current model configuration.'
            );
        }

        $context = array_merge($this->config, $context);

        foreach ($this->model->getStreamingResponse($input, $context) as $chunk) {
            yield $chunk;
        }
    }
}
