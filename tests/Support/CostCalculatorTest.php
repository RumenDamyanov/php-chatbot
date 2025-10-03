<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\CostCalculator;
use Rumenx\PhpChatbot\Support\TokenUsage;

describe('CostCalculator', function () {
    beforeEach(function () {
        $this->calculator = new CostCalculator();
    });

    it('calculates cost for GPT-4o', function () {
        $usage = new TokenUsage(1000000, 1000000, 2000000);
        $cost = $this->calculator->calculate($usage, 'gpt-4o');
        
        // 1M tokens * $2.50 + 1M tokens * $10.00 = $12.50
        expect($cost)->toBe(12.50);
    });

    it('calculates cost for GPT-4o-mini', function () {
        $usage = new TokenUsage(1000000, 1000000, 2000000);
        $cost = $this->calculator->calculate($usage, 'gpt-4o-mini');
        
        // 1M tokens * $0.15 + 1M tokens * $0.60 = $0.75
        expect($cost)->toBe(0.75);
    });

    it('calculates cost for Claude 3.5 Sonnet', function () {
        $usage = new TokenUsage(1000000, 1000000, 2000000);
        $cost = $this->calculator->calculate($usage, 'claude-3-5-sonnet-20241022');
        
        // 1M tokens * $3.00 + 1M tokens * $15.00 = $18.00
        expect($cost)->toBe(18.00);
    });

    it('calculates cost for Claude 3 Haiku', function () {
        $usage = new TokenUsage(1000000, 1000000, 2000000);
        $cost = $this->calculator->calculate($usage, 'claude-3-haiku');
        
        // 1M tokens * $0.25 + 1M tokens * $1.25 = $1.50
        expect($cost)->toBe(1.50);
    });

    it('calculates cost for Gemini 1.5 Pro', function () {
        $usage = new TokenUsage(1000000, 1000000, 2000000);
        $cost = $this->calculator->calculate($usage, 'gemini-1.5-pro');
        
        // 1M tokens * $1.25 + 1M tokens * $5.00 = $6.25
        expect($cost)->toBe(6.25);
    });

    it('calculates cost for Gemini 1.5 Flash', function () {
        $usage = new TokenUsage(1000000, 1000000, 2000000);
        $cost = $this->calculator->calculate($usage, 'gemini-1.5-flash');
        
        // 1M tokens * $0.075 + 1M tokens * $0.30 = $0.375
        expect($cost)->toBe(0.375);
    });

    it('calculates cost for DeepSeek Chat', function () {
        $usage = new TokenUsage(1000000, 1000000, 2000000);
        $cost = $this->calculator->calculate($usage, 'deepseek-chat');
        
        // 1M tokens * $0.14 + 1M tokens * $0.28 = $0.42
        expect($cost)->toBe(0.42);
    });

    it('returns zero cost for Ollama models', function () {
        $usage = new TokenUsage(1000000, 1000000, 2000000);
        $cost = $this->calculator->calculate($usage, 'ollama');
        
        expect($cost)->toBe(0.0);
    });

    it('returns zero cost for unknown models', function () {
        $usage = new TokenUsage(1000000, 1000000, 2000000);
        $cost = $this->calculator->calculate($usage, 'unknown-model');
        
        expect($cost)->toBe(0.0);
    });

    it('calculates cost for small token counts', function () {
        $usage = new TokenUsage(100, 50, 150);
        $cost = $this->calculator->calculate($usage, 'gpt-4o-mini');
        
        // 100 * $0.15/1M + 50 * $0.60/1M = 0.000045
        expect($cost)->toBeGreaterThan(0.0);
        expect($cost)->toBeLessThan(0.001);
    });

    it('gets pricing for known model', function () {
        $pricing = $this->calculator->getPricing('gpt-4o');
        
        expect($pricing)->toBeArray();
        expect($pricing['input'])->toBe(2.50);
        expect($pricing['output'])->toBe(10.00);
    });

    it('returns null for unknown model pricing', function () {
        $pricing = $this->calculator->getPricing('unknown-model-xyz');
        
        expect($pricing)->toBeNull();
    });

    it('matches model with version suffix', function () {
        $cost1 = $this->calculator->calculate(
            new TokenUsage(1000000, 1000000, 2000000),
            'gpt-4o-2024-11-20'
        );
        $cost2 = $this->calculator->calculate(
            new TokenUsage(1000000, 1000000, 2000000),
            'gpt-4o'
        );
        
        expect($cost1)->toBe($cost2);
    });

    it('checks if model has pricing', function () {
        expect($this->calculator->hasPricing('gpt-4o'))->toBeTrue();
        expect($this->calculator->hasPricing('claude-3-sonnet'))->toBeTrue();
        expect($this->calculator->hasPricing('unknown-model'))->toBeFalse();
    });

    it('gets list of supported models', function () {
        $models = $this->calculator->getSupportedModels();
        
        expect($models)->toBeArray();
        expect($models)->toContain('gpt-4o');
        expect($models)->toContain('gpt-4o-mini');
        expect($models)->toContain('claude-3-5-sonnet');
        expect($models)->toContain('gemini-1.5-pro');
        expect($models)->toContain('deepseek-chat');
    });

    it('calculates batch costs', function () {
        $usages = [
            new TokenUsage(100, 50, 150),
            new TokenUsage(200, 100, 300),
            new TokenUsage(300, 150, 450),
        ];
        
        $result = $this->calculator->calculateBatch($usages, 'gpt-4o-mini');
        
        expect($result)->toBeArray();
        expect($result['total_cost'])->toBeGreaterThan(0.0);
        expect($result['prompt_tokens'])->toBe(600);
        expect($result['completion_tokens'])->toBe(300);
        expect($result['total_tokens'])->toBe(900);
    });

    it('estimates cost for given token counts', function () {
        $cost = $this->calculator->estimate(1000, 500, 'gpt-4o-mini');
        
        // 1000 * $0.15/1M + 500 * $0.60/1M
        expect($cost)->toBeGreaterThan(0.0);
        expect($cost)->toBeLessThan(0.01);
    });

    it('formats cost correctly for small amounts', function () {
        $formatted = $this->calculator->formatCost(0.000123);
        
        expect($formatted)->toContain('$');
        expect($formatted)->toContain('0.000123');
    });

    it('formats cost correctly for medium amounts', function () {
        $formatted = $this->calculator->formatCost(0.5678);
        
        expect($formatted)->toContain('$');
        expect($formatted)->toContain('0.5678');
    });

    it('formats cost correctly for large amounts', function () {
        $formatted = $this->calculator->formatCost(12.56);
        
        expect($formatted)->toContain('$');
        expect($formatted)->toContain('12.56');
    });

    it('gets cheapest OpenAI model', function () {
        $cheapest = $this->calculator->getCheapestModel('openai');
        
        expect($cheapest)->toBeString();
        expect($cheapest)->toContain('gpt-');
    });

    it('gets cheapest Anthropic model', function () {
        $cheapest = $this->calculator->getCheapestModel('anthropic');
        
        expect($cheapest)->toBeString();
        expect($cheapest)->toContain('claude-');
    });

    it('gets cheapest Google model', function () {
        $cheapest = $this->calculator->getCheapestModel('google');
        
        expect($cheapest)->toBeString();
        expect($cheapest)->toContain('gemini-');
    });

    it('returns null for unknown provider', function () {
        $cheapest = $this->calculator->getCheapestModel('unknown-provider');
        
        expect($cheapest)->toBeNull();
    });

    it('recognizes local Ollama models', function () {
        $localModels = [
            'llama2',
            'mistral',
            'codellama',
            'phi',
        ];
        
        foreach ($localModels as $model) {
            $cost = $this->calculator->calculate(
                new TokenUsage(1000000, 1000000, 2000000),
                $model
            );
            expect($cost)->toBe(0.0);
        }
    });

    it('distinguishes cloud Llama from local', function () {
        // Cloud Llama models should have pricing
        $cloudCost = $this->calculator->calculate(
            new TokenUsage(1000000, 1000000, 2000000),
            'llama-3.3-70b'
        );
        
        expect($cloudCost)->toBeGreaterThan(0.0);
    });

    it('rounds cost to 6 decimal places', function () {
        $usage = new TokenUsage(1, 1, 2);
        $cost = $this->calculator->calculate($usage, 'gpt-4o-mini');
        
        // Result should be rounded to 6 decimals
        expect($cost)->toBeLessThanOrEqual(0.000001);
    });
});

