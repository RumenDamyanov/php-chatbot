<?php

namespace Rumenx\PhpChatbot\Support;

/**
 * Class CostCalculator
 *
 * Calculates API costs based on token usage and model pricing.
 * Pricing data is based on provider documentation as of 2024.
 *
 * @category AI
 * @package  Rumenx\PhpChatbot\Support
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class CostCalculator
{
    /**
     * Pricing data for all supported models.
     * Format: [model_pattern => [input_price_per_1M, output_price_per_1M]]
     * Prices are in USD per 1 million tokens.
     *
     * @var array<string, array{input: float, output: float}>
     */
    protected const PRICING = [
        // OpenAI GPT-4 models
        'gpt-4-turbo' => ['input' => 10.00, 'output' => 30.00],
        'gpt-4-turbo-preview' => ['input' => 10.00, 'output' => 30.00],
        'gpt-4-0125-preview' => ['input' => 10.00, 'output' => 30.00],
        'gpt-4-1106-preview' => ['input' => 10.00, 'output' => 30.00],
        'gpt-4' => ['input' => 30.00, 'output' => 60.00],
        'gpt-4-0613' => ['input' => 30.00, 'output' => 60.00],
        'gpt-4-32k' => ['input' => 60.00, 'output' => 120.00],
        'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
        'gpt-4o-2024-11-20' => ['input' => 2.50, 'output' => 10.00],
        'gpt-4o-2024-08-06' => ['input' => 2.50, 'output' => 10.00],
        'gpt-4o-2024-05-13' => ['input' => 5.00, 'output' => 15.00],
        'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
        'gpt-4o-mini-2024-07-18' => ['input' => 0.15, 'output' => 0.60],

        // OpenAI GPT-3.5 models
        'gpt-3.5-turbo' => ['input' => 0.50, 'output' => 1.50],
        'gpt-3.5-turbo-0125' => ['input' => 0.50, 'output' => 1.50],
        'gpt-3.5-turbo-1106' => ['input' => 1.00, 'output' => 2.00],
        'gpt-3.5-turbo-16k' => ['input' => 3.00, 'output' => 4.00],

        // OpenAI o1 models
        'o1' => ['input' => 15.00, 'output' => 60.00],
        'o1-2024-12-17' => ['input' => 15.00, 'output' => 60.00],
        'o1-preview' => ['input' => 15.00, 'output' => 60.00],
        'o1-mini' => ['input' => 3.00, 'output' => 12.00],

        // Anthropic Claude models
        'claude-3-5-sonnet' => ['input' => 3.00, 'output' => 15.00],
        'claude-3-5-sonnet-20241022' => ['input' => 3.00, 'output' => 15.00],
        'claude-3-5-sonnet-20240620' => ['input' => 3.00, 'output' => 15.00],
        'claude-3-5-haiku' => ['input' => 1.00, 'output' => 5.00],
        'claude-3-5-haiku-20241022' => ['input' => 1.00, 'output' => 5.00],
        'claude-3-opus' => ['input' => 15.00, 'output' => 75.00],
        'claude-3-opus-20240229' => ['input' => 15.00, 'output' => 75.00],
        'claude-3-sonnet' => ['input' => 3.00, 'output' => 15.00],
        'claude-3-sonnet-20240229' => ['input' => 3.00, 'output' => 15.00],
        'claude-3-haiku' => ['input' => 0.25, 'output' => 1.25],
        'claude-3-haiku-20240307' => ['input' => 0.25, 'output' => 1.25],

        // Google Gemini models
        'gemini-2.0-flash-exp' => ['input' => 0.00, 'output' => 0.00],
        'gemini-1.5-pro' => ['input' => 1.25, 'output' => 5.00],
        'gemini-1.5-flash' => ['input' => 0.075, 'output' => 0.30],
        'gemini-1.5-flash-8b' => ['input' => 0.0375, 'output' => 0.15],
        'gemini-pro' => ['input' => 0.50, 'output' => 1.50],

        // xAI Grok models
        'grok-beta' => ['input' => 5.00, 'output' => 15.00],
        'grok-2' => ['input' => 2.00, 'output' => 10.00],
        'grok-2-1212' => ['input' => 2.00, 'output' => 10.00],

        // Meta Llama models (via Groq/Together)
        'llama-3.3-70b' => ['input' => 0.35, 'output' => 0.40],
        'llama-3.1-405b' => ['input' => 0.50, 'output' => 0.50],
        'llama-3.1-70b' => ['input' => 0.35, 'output' => 0.40],
        'llama-3.1-8b' => ['input' => 0.05, 'output' => 0.08],

        // DeepSeek models
        'deepseek-chat' => ['input' => 0.14, 'output' => 0.28],
        'deepseek-reasoner' => ['input' => 0.55, 'output' => 2.19],

        // Ollama (local models - free)
        'ollama' => ['input' => 0.00, 'output' => 0.00],
    ];

    /**
     * Calculate the cost for a given token usage.
     *
     * @param TokenUsage $tokenUsage Token usage information.
     * @param string     $model      Model identifier.
     *
     * @return float Cost in USD.
     */
    public function calculate(TokenUsage $tokenUsage, string $model): float
    {
        $pricing = $this->getPricing($model);

        if ($pricing === null) {
            return 0.0;
        }

        $inputCost = ($tokenUsage->getPromptTokens() / 1_000_000)
            * $pricing['input'];
        $outputCost = ($tokenUsage->getCompletionTokens() / 1_000_000)
            * $pricing['output'];

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Get pricing for a specific model.
     *
     * @param string $model Model identifier.
     *
     * @return array{input: float, output: float}|null
     */
    public function getPricing(string $model): ?array
    {
        // Exact match
        if (isset(self::PRICING[$model])) {
            return self::PRICING[$model];
        }

        // Try partial match for models with timestamps/versions
        foreach (self::PRICING as $pattern => $pricing) {
            if (str_starts_with($model, $pattern)) {
                return $pricing;
            }
        }

        // Try matching base model name (e.g., "gpt-4" from "gpt-4-0314")
        $modelParts = explode('-', $model);
        for ($i = count($modelParts); $i > 1; $i--) {
            $baseModel = implode('-', array_slice($modelParts, 0, $i));
            if (isset(self::PRICING[$baseModel])) {
                return self::PRICING[$baseModel];
            }
        }

        // Check if it's an Ollama model
        if ($this->isOllamaModel($model)) {
            return self::PRICING['ollama'];
        }

        return null;
    }

    /**
     * Check if a model has pricing information.
     *
     * @param string $model Model identifier.
     *
     * @return bool
     */
    public function hasPricing(string $model): bool
    {
        return $this->getPricing($model) !== null;
    }

    /**
     * Get all supported models.
     *
     * @return array<string>
     */
    public function getSupportedModels(): array
    {
        return array_keys(self::PRICING);
    }

    /**
     * Calculate cost summary for multiple requests.
     *
     * @param array<TokenUsage> $usages Array of token usage objects.
     * @param string            $model  Model identifier.
     *
     * @return array{total_cost: float, prompt_tokens: int,
     *               completion_tokens: int, total_tokens: int}
     */
    public function calculateBatch(array $usages, string $model): array
    {
        $totalCost = 0.0;
        $promptTokens = 0;
        $completionTokens = 0;
        $totalTokens = 0;

        foreach ($usages as $usage) {
            $totalCost += $this->calculate($usage, $model);
            $promptTokens += $usage->getPromptTokens();
            $completionTokens += $usage->getCompletionTokens();
            $totalTokens += $usage->getTotalTokens();
        }

        return [
            'total_cost' => round($totalCost, 6),
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
        ];
    }

    /**
     * Estimate cost for a given number of tokens.
     *
     * @param int    $promptTokens     Number of input tokens.
     * @param int    $completionTokens Number of output tokens.
     * @param string $model            Model identifier.
     *
     * @return float Estimated cost in USD.
     */
    public function estimate(
        int $promptTokens,
        int $completionTokens,
        string $model
    ): float {
        $pricing = $this->getPricing($model);

        if ($pricing === null) {
            return 0.0;
        }

        $inputCost = ($promptTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($completionTokens / 1_000_000) * $pricing['output'];

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Format cost as a human-readable string.
     *
     * @param float $cost Cost in USD.
     *
     * @return string Formatted cost string.
     */
    public function formatCost(float $cost): string
    {
        if ($cost < 0.01) {
            return sprintf('$%.6f', $cost);
        }

        if ($cost < 1.0) {
            return sprintf('$%.4f', $cost);
        }

        return sprintf('$%.2f', $cost);
    }

    /**
     * Check if a model is an Ollama (local) model.
     *
     * @param string $model Model identifier.
     *
     * @return bool
     */
    protected function isOllamaModel(string $model): bool
    {
        // Ollama models typically don't have specific pricing patterns
        // They are local models running on user's hardware
        $ollamaPatterns = [
            'llama',
            'mistral',
            'mixtral',
            'qwen',
            'codellama',
            'phi',
            'gemma',
        ];

        $modelLower = strtolower($model);
        foreach ($ollamaPatterns as $pattern) {
            if (str_contains($modelLower, $pattern)) {
                // Check if it's not a cloud provider's model
                if (
                    !str_contains($modelLower, 'together')
                    && !str_contains($modelLower, 'groq')
                    && !str_contains($modelLower, 'meta-')
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the most cost-effective model for a given provider.
     *
     * @param string $provider Provider name (openai, anthropic, google, etc).
     *
     * @return string|null Model identifier or null if provider not found.
     */
    public function getCheapestModel(string $provider): ?string
    {
        $providerModels = $this->getModelsByProvider($provider);

        if (empty($providerModels)) {
            return null;
        }

        $cheapest = null;
        $lowestCost = PHP_FLOAT_MAX;

        foreach ($providerModels as $model) {
            $pricing = self::PRICING[$model];
            $avgCost = ($pricing['input'] + $pricing['output']) / 2;

            if ($avgCost < $lowestCost) {
                $lowestCost = $avgCost;
                $cheapest = $model;
            }
        }

        return $cheapest;
    }

    /**
     * Get all models for a specific provider.
     *
     * @param string $provider Provider name.
     *
     * @return array<string>
     */
    protected function getModelsByProvider(string $provider): array
    {
        $patterns = [
            'openai' => ['gpt-', 'o1-'],
            'anthropic' => ['claude-'],
            'google' => ['gemini-'],
            'xai' => ['grok-'],
            'meta' => ['llama-'],
            'deepseek' => ['deepseek-'],
            'ollama' => ['ollama'],
        ];

        $providerPattern = $patterns[strtolower($provider)] ?? [];
        $models = [];

        foreach (self::PRICING as $model => $pricing) {
            foreach ($providerPattern as $pattern) {
                if (str_starts_with($model, $pattern)) {
                    $models[] = $model;
                    break;
                }
            }
        }

        return $models;
    }
}
