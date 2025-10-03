<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Support\ChatResponse;
use Rumenx\PhpChatbot\Support\ResponseMetadata;
use Rumenx\PhpChatbot\Support\TokenUsage;
use Rumenx\PhpChatbot\Contracts\AiModelInterface;

class MockModelWithTokens implements AiModelInterface {
    public function getResponse(string $input, array $context = []): ChatResponse {
        $tokenUsage = new TokenUsage(100, 50, 150);
        $metadata = new ResponseMetadata(
            'mock-model',
            $tokenUsage,
            'stop',
            'response-123',
            time()
        );
        return new ChatResponse('Mock response: ' . $input, $metadata);
    }
    
    public function getModel(): string {
        return 'mock-model';
    }
}

class MockModelWithoutTokens implements AiModelInterface {
    public function getResponse(string $input, array $context = []): ChatResponse {
        $metadata = new ResponseMetadata('mock-no-tokens');
        return new ChatResponse('Response: ' . $input, $metadata);
    }
    
    public function getModel(): string {
        return 'mock-no-tokens';
    }
}

describe('PhpChatbot Token Tracking', function () {
    it('stores last response after ask', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        $response = $chatbot->ask('Hello');
        
        expect($response)->toBe('Mock response: Hello');
        expect($chatbot->getLastResponse())->toBeInstanceOf(ChatResponse::class);
        expect($chatbot->getLastResponse()->getContent())->toBe('Mock response: Hello');
    });

    it('returns last token usage', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        $chatbot->ask('Test message');
        $tokenUsage = $chatbot->getLastTokenUsage();
        
        expect($tokenUsage)->toBeInstanceOf(TokenUsage::class);
        expect($tokenUsage->getPromptTokens())->toBe(100);
        expect($tokenUsage->getCompletionTokens())->toBe(50);
        expect($tokenUsage->getTotalTokens())->toBe(150);
    });

    it('returns null token usage when not available', function () {
        $model = new MockModelWithoutTokens();
        $chatbot = new PhpChatbot($model);
        
        $chatbot->ask('Test');
        
        expect($chatbot->getLastTokenUsage())->toBeNull();
    });

    it('calculates last cost', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        $chatbot->ask('Calculate cost');
        $cost = $chatbot->getLastCost();
        
        expect($cost)->toBeFloat();
        expect($cost)->toBeGreaterThanOrEqual(0.0);
    });

    it('returns null cost when no token usage', function () {
        $model = new MockModelWithoutTokens();
        $chatbot = new PhpChatbot($model);
        
        $chatbot->ask('Test');
        
        expect($chatbot->getLastCost())->toBeNull();
    });

    it('returns null cost when no response yet', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        expect($chatbot->getLastCost())->toBeNull();
    });

    it('generates response summary', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        $chatbot->ask('Summary test');
        $summary = $chatbot->getLastResponseSummary();
        
        expect($summary)->toBeString();
        expect($summary)->toContain('mock-model');
        expect($summary)->toContain('Tokens:');
        expect($summary)->toContain('Cost:');
        expect($summary)->toContain('$');
    });

    it('returns null summary when no response', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        expect($chatbot->getLastResponseSummary())->toBeNull();
    });

    it('gets cost calculator instance', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        $calculator = $chatbot->getCostCalculator();
        
        expect($calculator)->toBeInstanceOf(\Rumenx\PhpChatbot\Support\CostCalculator::class);
    });

    it('estimates cost before making request', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        $estimated = $chatbot->estimateCost(1000, 500);
        
        expect($estimated)->toBeFloat();
        expect($estimated)->toBeGreaterThanOrEqual(0.0);
    });

    it('estimates cost with specific model', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        $estimated = $chatbot->estimateCost(1000, 500, 'gpt-4o-mini');
        
        expect($estimated)->toBeFloat();
        expect($estimated)->toBeGreaterThanOrEqual(0.0);
    });

    it('updates last response on multiple asks', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        $chatbot->ask('First');
        $first = $chatbot->getLastResponse();
        
        $chatbot->ask('Second');
        $second = $chatbot->getLastResponse();
        
        expect($first)->not->toBe($second);
        expect($second->getContent())->toContain('Second');
    });

    it('maintains backward compatibility with string return', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        $response = $chatbot->ask('Test');
        
        // Should return string directly
        expect($response)->toBeString();
        expect($response)->toBe('Mock response: Test');
        
        // But internal response is ChatResponse
        expect($chatbot->getLastResponse())->toBeInstanceOf(ChatResponse::class);
    });

    it('handles model without getModel method gracefully', function () {
        $model = new class implements AiModelInterface {
            public function getResponse(string $input, array $context = []): ChatResponse {
                return ChatResponse::fromString('Response', 'test');
            }
        };
        
        $chatbot = new PhpChatbot($model);
        
        // Should not throw when estimating cost
        $cost = $chatbot->estimateCost(100, 50);
        expect($cost)->toBeFloat();
    });

    it('returns null last response before any ask', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        expect($chatbot->getLastResponse())->toBeNull();
        expect($chatbot->getLastTokenUsage())->toBeNull();
    });

    it('tracks token usage across multiple requests', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        $usages = [];
        for ($i = 0; $i < 3; $i++) {
            $chatbot->ask("Message $i");
            $usages[] = $chatbot->getLastTokenUsage();
        }
        
        expect(count($usages))->toBe(3);
        foreach ($usages as $usage) {
            expect($usage)->toBeInstanceOf(TokenUsage::class);
            expect($usage->getTotalTokens())->toBe(150);
        }
    });

    it('cost summary includes model information', function () {
        $model = new MockModelWithTokens();
        $chatbot = new PhpChatbot($model);
        
        $chatbot->ask('Test');
        $summary = $chatbot->getLastResponseSummary();
        
        expect($summary)->toContain('mock-model');
    });

    it('handles zero-cost models in summary', function () {
        $model = new class implements AiModelInterface {
            public function getResponse(string $input, array $context = []): ChatResponse {
                $tokenUsage = new TokenUsage(100, 50, 150);
                $metadata = new ResponseMetadata('ollama', $tokenUsage, 'stop');
                return new ChatResponse('Response', $metadata);
            }
            
            public function getModel(): string {
                return 'ollama';
            }
        };
        
        $chatbot = new PhpChatbot($model);
        $chatbot->ask('Test');
        $summary = $chatbot->getLastResponseSummary();
        
        expect($summary)->toContain('$0.000000');
    });
});

