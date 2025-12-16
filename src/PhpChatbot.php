<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;
use Rumenx\PhpChatbot\Support\ConversationMemory;
use Rumenx\PhpChatbot\Support\ChatResponse;
use Rumenx\PhpChatbot\Support\TokenUsage;
use Rumenx\PhpChatbot\Support\CostCalculator;
use Rumenx\PhpChatbot\Exceptions\PhpChatbotException;
use Rumenx\PhpChatbot\Exceptions\NetworkException;
use Rumenx\PhpChatbot\Exceptions\ApiException;

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
     * Conversation memory manager.
     *
     * @var ConversationMemory|null
     */
    protected $memory;

    /**
     * The last ChatResponse received from the model.
     *
     * @var ChatResponse|null
     */
    protected $lastResponse;

    /**
     * Cost calculator for tracking API costs.
     *
     * @var CostCalculator
     */
    protected $costCalculator;

    /**
     * Constructor for PhpChatbot.
     *
     * @param AiModelInterface     $model   The AI model implementation.
     * @param array<string, mixed> $config  Optional configuration for the chatbot.
     * @param ConversationMemory|null $memory Optional conversation memory manager.
     */
    public function __construct(
        AiModelInterface $model,
        array $config = [],
        ?ConversationMemory $memory = null
    ) {
        $this->model = $model;
        $this->config = $config;
        $this->memory = $memory;
        $this->lastResponse = null;
        $this->costCalculator = new CostCalculator();
    }

    /**
     * Get a response from the chatbot.
     *
     * @param string               $input   The user input message.
     * @param array<string, mixed> $context Optional runtime context (merged with
     *                                      config).
     *
     * @return string The chatbot's reply (automatically converted from ChatResponse).
     */
    public function ask(
        string $input,
        array $context = []
    ): string {
        $context = array_merge($this->config, $context);

        // Extract session ID if provided
        $sessionId = $context['sessionId'] ?? null;

        // Add conversation history to context if memory is enabled
        if ($this->memory !== null && $this->memory->isEnabled() && $sessionId !== null) {
            $history = $this->memory->getFormattedHistory($sessionId);
            if (!empty($history)) {
                $context['messages'] = $history;
            }

            // Add current user message to history
            $this->memory->addMessage($sessionId, 'user', $input);
        }

        try {
            $response = $this->model->getResponse($input, $context);

            // Store the ChatResponse for token tracking
            $this->lastResponse = $response;

            // Get the content as string
            $responseContent = (string) $response;

            // Store assistant's response in memory
            if ($this->memory !== null && $this->memory->isEnabled() && $sessionId !== null) {
                $this->memory->addMessage($sessionId, 'assistant', $responseContent);
            }

            return $responseContent;
        } catch (PhpChatbotException $e) {
            // Check if we should propagate exceptions or return error messages
            $throwExceptions = $context['throw_exceptions'] ?? false;
            
            if ($throwExceptions) {
                throw $e;
            }
            
            // Graceful degradation: return error message
            $errorMessage = $e->getMessage();
            $this->lastResponse = ChatResponse::fromString($errorMessage, 'error');
            return $errorMessage;
        }
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

        // Extract session ID if provided
        $sessionId = $context['sessionId'] ?? null;

        // Add conversation history to context if memory is enabled
        if ($this->memory !== null && $this->memory->isEnabled() && $sessionId !== null) {
            $history = $this->memory->getFormattedHistory($sessionId);
            if (!empty($history)) {
                $context['messages'] = $history;
            }

            // Add current user message to history
            $this->memory->addMessage($sessionId, 'user', $input);
        }

        // Collect full response while streaming
        $fullResponse = '';

        foreach ($this->model->getStreamingResponse($input, $context) as $chunk) {
            $fullResponse .= $chunk;
            yield $chunk;
        }

        // Store assistant's complete response in memory
        if ($this->memory !== null && $this->memory->isEnabled() && $sessionId !== null) {
            $this->memory->addMessage($sessionId, 'assistant', $fullResponse);
        }
    }

    /**
     * Get conversation history for a session.
     *
     * @param string $sessionId The session identifier.
     *
     * @return array<int, array<string, mixed>> Array of messages.
     */
    public function getConversationHistory(string $sessionId): array
    {
        if ($this->memory === null) {
            return [];
        }

        return $this->memory->getHistory($sessionId);
    }

    /**
     * Clear conversation history for a session.
     *
     * @param string $sessionId The session identifier.
     *
     * @return bool True on success, false on failure.
     */
    public function clearConversationHistory(string $sessionId): bool
    {
        if ($this->memory === null) {
            return false;
        }

        return $this->memory->clearHistory($sessionId);
    }

    /**
     * Get the conversation memory manager.
     *
     * @return ConversationMemory|null
     */
    public function getMemory(): ?ConversationMemory
    {
        return $this->memory;
    }

    /**
     * Set the conversation memory manager.
     *
     * @param ConversationMemory|null $memory
     *
     * @return void
     */
    public function setMemory(?ConversationMemory $memory): void
    {
        $this->memory = $memory;
    }

    /**
     * Get the last ChatResponse received from the model.
     *
     * This provides access to the complete response including metadata,
     * token usage, and other information.
     *
     * @return ChatResponse|null
     */
    public function getLastResponse(): ?ChatResponse
    {
        return $this->lastResponse;
    }

    /**
     * Get token usage information from the last response.
     *
     * @return TokenUsage|null
     */
    public function getLastTokenUsage(): ?TokenUsage
    {
        return $this->lastResponse?->getTokenUsage();
    }

    /**
     * Calculate the cost of the last API request.
     *
     * Returns null if no token usage information is available.
     *
     * @return float|null Cost in USD, or null if unavailable.
     */
    public function getLastCost(): ?float
    {
        $tokenUsage = $this->getLastTokenUsage();

        if ($tokenUsage === null || $this->lastResponse === null) {
            return null;
        }

        $model = $this->lastResponse->getModel();

        return $this->costCalculator->calculate($tokenUsage, $model);
    }

    /**
     * Get a formatted summary of the last response including token usage and cost.
     *
     * @return string|null Human-readable summary, or null if no response available.
     */
    public function getLastResponseSummary(): ?string
    {
        if ($this->lastResponse === null) {
            return null;
        }

        $summary = $this->lastResponse->getSummary();
        $cost = $this->getLastCost();

        if ($cost !== null) {
            $formattedCost = $this->costCalculator->formatCost($cost);
            $summary .= " | Cost: {$formattedCost}";
        }

        return $summary;
    }

    /**
     * Get the cost calculator instance.
     *
     * @return CostCalculator
     */
    public function getCostCalculator(): CostCalculator
    {
        return $this->costCalculator;
    }

    /**
     * Estimate the cost for a given number of tokens.
     *
     * This is useful for budgeting before making an API call.
     *
     * @param int    $promptTokens     Estimated input tokens.
     * @param int    $completionTokens Estimated output tokens.
     * @param string|null $model       Model name (uses current model if not provided).
     *
     * @return float Estimated cost in USD.
     */
    public function estimateCost(
        int $promptTokens,
        int $completionTokens,
        ?string $model = null
    ): float {
        if ($model === null) {
            if (method_exists($this->model, 'getModel')) {
                $model = $this->model->getModel();
            } else {
                $model = 'unknown';
            }
        }

        return $this->costCalculator->estimate(
            $promptTokens,
            $completionTokens,
            $model
        );
    }
}
