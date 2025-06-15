<?php

namespace Rumenx\PhpChatbot;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

/**
 * Class PhpChatbot
 *
 * The main chatbot orchestrator. Handles user input, context merging and delegates response generation to the AI model.
 *
 * @package Rumenx\PhpChatbot
 */
class PhpChatbot
{
    /**
     * The AI model instance used for generating responses.
     *
     * @var AiModelInterface
     */
    protected AiModelInterface $model;

    /**
     * Configuration array for chatbot behavior, model, prompts, etc.
     *
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * PhpChatbot constructor.
     *
     * @param AiModelInterface $model  The AI model implementation.
     * @param array<string, mixed> $config  Optional configuration for the chatbot.
     */
    public function __construct(AiModelInterface $model, array $config = [])
    {
        $this->model = $model;
        /** @var array<string, mixed> $config */
        $this->config = $config;
    }

    /**
     * Generate a chatbot reply for the given input and context.
     *
     * @param string $input  The user input message.
     * @param array<string, mixed> $context  Optional runtime context (merged with config).
     * @return string  The chatbot's reply.
     */
    public function ask(string $input, array $context = []): string
    {
        /** @var array<string, mixed> $context */
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
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
}
