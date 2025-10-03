<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\TokenUsage;

describe('TokenUsage', function () {
    it('creates instance with token counts', function () {
        $usage = new TokenUsage(100, 50, 150);
        
        expect($usage->getPromptTokens())->toBe(100);
        expect($usage->getCompletionTokens())->toBe(50);
        expect($usage->getTotalTokens())->toBe(150);
    });

    it('converts to array', function () {
        $usage = new TokenUsage(100, 50, 150);
        $array = $usage->toArray();
        
        expect($array)->toBeArray();
        expect($array['prompt_tokens'])->toBe(100);
        expect($array['completion_tokens'])->toBe(50);
        expect($array['total_tokens'])->toBe(150);
    });

    it('creates from array', function () {
        $data = [
            'prompt_tokens' => 200,
            'completion_tokens' => 100,
            'total_tokens' => 300,
        ];
        
        $usage = TokenUsage::fromArray($data);
        
        expect($usage->getPromptTokens())->toBe(200);
        expect($usage->getCompletionTokens())->toBe(100);
        expect($usage->getTotalTokens())->toBe(300);
    });

    it('creates from OpenAI response format', function () {
        $openAiUsage = [
            'prompt_tokens' => 50,
            'completion_tokens' => 75,
            'total_tokens' => 125,
        ];
        
        $usage = TokenUsage::fromOpenAI($openAiUsage);
        
        expect($usage->getPromptTokens())->toBe(50);
        expect($usage->getCompletionTokens())->toBe(75);
        expect($usage->getTotalTokens())->toBe(125);
    });

    it('creates from Anthropic response format', function () {
        $anthropicUsage = [
            'input_tokens' => 60,
            'output_tokens' => 90,
        ];
        
        $usage = TokenUsage::fromAnthropic($anthropicUsage);
        
        expect($usage->getPromptTokens())->toBe(60);
        expect($usage->getCompletionTokens())->toBe(90);
        expect($usage->getTotalTokens())->toBe(150);
    });

    it('creates from Gemini response format', function () {
        $geminiUsage = [
            'promptTokenCount' => 40,
            'candidatesTokenCount' => 80,
            'totalTokenCount' => 120,
        ];
        
        $usage = TokenUsage::fromGemini($geminiUsage);
        
        expect($usage->getPromptTokens())->toBe(40);
        expect($usage->getCompletionTokens())->toBe(80);
        expect($usage->getTotalTokens())->toBe(120);
    });

    it('generates human-readable summary', function () {
        $usage = new TokenUsage(100, 50, 150);
        $summary = $usage->getSummary();
        
        expect($summary)->toBeString();
        expect($summary)->toContain('100');
        expect($summary)->toContain('50');
        expect($summary)->toContain('150');
        expect($summary)->toContain('Tokens:');
    });

    it('checks if exceeds threshold', function () {
        $usage = new TokenUsage(100, 50, 150);
        
        expect($usage->exceedsThreshold(140))->toBeTrue();
        expect($usage->exceedsThreshold(150))->toBeFalse();
        expect($usage->exceedsThreshold(160))->toBeFalse();
    });

    it('calculates usage percentage', function () {
        $usage = new TokenUsage(50, 50, 100);
        
        expect($usage->getUsagePercentage(200))->toBe(50.0);
        expect($usage->getUsagePercentage(100))->toBe(100.0);
        expect($usage->getUsagePercentage(1000))->toBe(10.0);
    });

    it('calculates remaining tokens', function () {
        $usage = new TokenUsage(60, 40, 100);
        
        expect($usage->getRemainingTokens(200))->toBe(100);
        expect($usage->getRemainingTokens(150))->toBe(50);
        expect($usage->getRemainingTokens(100))->toBe(0);
        expect($usage->getRemainingTokens(50))->toBe(0);
    });

    it('handles zero tokens', function () {
        $usage = new TokenUsage(0, 0, 0);
        
        expect($usage->getPromptTokens())->toBe(0);
        expect($usage->getCompletionTokens())->toBe(0);
        expect($usage->getTotalTokens())->toBe(0);
        expect($usage->exceedsThreshold(1))->toBeFalse();
    });

    it('handles missing total in Gemini format', function () {
        $geminiUsage = [
            'promptTokenCount' => 30,
            'candidatesTokenCount' => 70,
            // totalTokenCount is missing - will be 0
        ];
        
        $usage = TokenUsage::fromGemini($geminiUsage);
        
        expect($usage->getPromptTokens())->toBe(30);
        expect($usage->getCompletionTokens())->toBe(70);
        expect($usage->getTotalTokens())->toBe(0); // Uses provided value or 0
    });
});

