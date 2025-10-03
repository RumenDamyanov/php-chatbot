<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\ChatResponse;
use Rumenx\PhpChatbot\Support\ResponseMetadata;
use Rumenx\PhpChatbot\Support\TokenUsage;

describe('ChatResponse', function () {
    it('creates instance with content and metadata', function () {
        $metadata = new ResponseMetadata('gpt-4o');
        $response = new ChatResponse('Hello, world!', $metadata);
        
        expect($response->getContent())->toBe('Hello, world!');
        expect($response->getMetadata())->toBe($metadata);
    });

    it('converts to string', function () {
        $metadata = new ResponseMetadata('gpt-4o');
        $response = new ChatResponse('Hello, world!', $metadata);
        
        expect((string) $response)->toBe('Hello, world!');
        expect($response->__toString())->toBe('Hello, world!');
    });

    it('gets token usage from metadata', function () {
        $tokenUsage = new TokenUsage(100, 50, 150);
        $metadata = new ResponseMetadata('gpt-4o', $tokenUsage);
        $response = new ChatResponse('Hello', $metadata);
        
        expect($response->getTokenUsage())->toBe($tokenUsage);
        expect($response->hasTokenUsage())->toBeTrue();
    });

    it('handles missing token usage', function () {
        $metadata = new ResponseMetadata('gpt-4o');
        $response = new ChatResponse('Hello', $metadata);
        
        expect($response->getTokenUsage())->toBeNull();
        expect($response->hasTokenUsage())->toBeFalse();
    });

    it('gets model from metadata', function () {
        $metadata = new ResponseMetadata('gpt-4o-mini');
        $response = new ChatResponse('Hello', $metadata);
        
        expect($response->getModel())->toBe('gpt-4o-mini');
    });

    it('gets finish reason from metadata', function () {
        $metadata = new ResponseMetadata('gpt-4o', null, 'stop');
        $response = new ChatResponse('Hello', $metadata);
        
        expect($response->getFinishReason())->toBe('stop');
    });

    it('checks if was truncated', function () {
        $truncated = new ChatResponse(
            'Hello',
            new ResponseMetadata('gpt-4o', null, 'length')
        );
        $notTruncated = new ChatResponse(
            'Hello',
            new ResponseMetadata('gpt-4o', null, 'stop')
        );
        
        expect($truncated->wasTruncated())->toBeTrue();
        expect($notTruncated->wasTruncated())->toBeFalse();
    });

    it('checks if was filtered', function () {
        $filtered = new ChatResponse(
            'Hello',
            new ResponseMetadata('gpt-4o', null, 'content_filter')
        );
        $notFiltered = new ChatResponse(
            'Hello',
            new ResponseMetadata('gpt-4o', null, 'stop')
        );
        
        expect($filtered->wasFiltered())->toBeTrue();
        expect($notFiltered->wasFiltered())->toBeFalse();
    });

    it('checks if completed normally', function () {
        $normal = new ChatResponse(
            'Hello',
            new ResponseMetadata('gpt-4o', null, 'stop')
        );
        $abnormal = new ChatResponse(
            'Hello',
            new ResponseMetadata('gpt-4o', null, 'length')
        );
        
        expect($normal->wasCompletedNormally())->toBeTrue();
        expect($abnormal->wasCompletedNormally())->toBeFalse();
    });

    it('converts to array', function () {
        $tokenUsage = new TokenUsage(100, 50, 150);
        $metadata = new ResponseMetadata('gpt-4o', $tokenUsage, 'stop');
        $response = new ChatResponse('Hello, world!', $metadata);
        
        $array = $response->toArray();
        
        expect($array)->toBeArray();
        expect($array['content'])->toBe('Hello, world!');
        expect($array['metadata'])->toBeArray();
        expect($array['metadata']['model'])->toBe('gpt-4o');
    });

    it('generates summary for short content', function () {
        $metadata = new ResponseMetadata('gpt-4o');
        $response = new ChatResponse('Short text', $metadata);
        
        $summary = $response->getSummary();
        
        expect($summary)->toBeString();
        expect($summary)->toContain('gpt-4o');
        expect($summary)->toContain('Short text');
    });

    it('truncates long content in summary', function () {
        $longContent = str_repeat('a', 200);
        $metadata = new ResponseMetadata('gpt-4o', null, 'stop');
        $response = new ChatResponse($longContent, $metadata);
        $summary = $response->getSummary();
        
        expect($summary)->toBeString();
        expect($summary)->toContain('...');
        // The preview itself is truncated at 50 chars
        expect($summary)->toContain('Model:');
        expect($summary)->toContain('Length: 200 chars');
    });

    it('creates from simple string', function () {
        $response = ChatResponse::fromString('Hello, world!', 'test-model');
        
        expect($response->getContent())->toBe('Hello, world!');
        expect($response->getModel())->toBe('test-model');
        expect($response->hasTokenUsage())->toBeFalse();
    });

    it('creates from OpenAI response', function () {
        $openAiResponse = [
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion',
            'created' => 1234567890,
            'model' => 'gpt-4o-mini',
            'choices' => [
                [
                    'finish_reason' => 'stop',
                    'message' => ['content' => 'Hello!']
                ]
            ],
            'usage' => [
                'prompt_tokens' => 10,
                'completion_tokens' => 5,
                'total_tokens' => 15
            ],
            'system_fingerprint' => 'fp_123'
        ];
        
        $response = ChatResponse::fromOpenAI('Hello!', $openAiResponse);
        
        expect($response->getContent())->toBe('Hello!');
        expect($response->getModel())->toBe('gpt-4o-mini');
        expect($response->getFinishReason())->toBe('stop');
        expect($response->hasTokenUsage())->toBeTrue();
        expect($response->getTokenUsage()->getPromptTokens())->toBe(10);
        expect($response->getTokenUsage()->getCompletionTokens())->toBe(5);
    });

    it('creates from Anthropic response', function () {
        $anthropicResponse = [
            'id' => 'msg_123',
            'type' => 'message',
            'role' => 'assistant',
            'model' => 'claude-3-5-sonnet-20241022',
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 20,
                'output_tokens' => 30
            ]
        ];
        
        $response = ChatResponse::fromAnthropic('Hello from Claude!', $anthropicResponse);
        
        expect($response->getContent())->toBe('Hello from Claude!');
        expect($response->getModel())->toBe('claude-3-5-sonnet-20241022');
        expect($response->getFinishReason())->toBe('end_turn');
        expect($response->hasTokenUsage())->toBeTrue();
        expect($response->getTokenUsage()->getPromptTokens())->toBe(20);
        expect($response->getTokenUsage()->getCompletionTokens())->toBe(30);
    });

    it('creates from Gemini response', function () {
        $geminiResponse = [
            'candidates' => [
                [
                    'finishReason' => 'STOP',
                    'safetyRatings' => []
                ]
            ],
            'usageMetadata' => [
                'promptTokenCount' => 15,
                'candidatesTokenCount' => 25,
                'totalTokenCount' => 40
            ]
        ];
        
        $response = ChatResponse::fromGemini('Hello from Gemini!', $geminiResponse, 'gemini-1.5-pro');
        
        expect($response->getContent())->toBe('Hello from Gemini!');
        expect($response->getModel())->toBe('gemini-1.5-pro');
        expect($response->getFinishReason())->toBe('stop');
        expect($response->hasTokenUsage())->toBeTrue();
        expect($response->getTokenUsage()->getPromptTokens())->toBe(15);
        expect($response->getTokenUsage()->getCompletionTokens())->toBe(25);
    });

    it('handles OpenAI response without usage', function () {
        $openAiResponse = [
            'model' => 'gpt-4o',
            'choices' => [['finish_reason' => 'stop']]
        ];
        
        $response = ChatResponse::fromOpenAI('Hello!', $openAiResponse);
        
        expect($response->getContent())->toBe('Hello!');
        expect($response->hasTokenUsage())->toBeFalse();
    });

    it('handles Anthropic response without usage', function () {
        $anthropicResponse = [
            'model' => 'claude-3-sonnet',
            'stop_reason' => 'end_turn'
        ];
        
        $response = ChatResponse::fromAnthropic('Hello!', $anthropicResponse);
        
        expect($response->getContent())->toBe('Hello!');
        expect($response->hasTokenUsage())->toBeFalse();
    });

    it('handles Gemini response without usage', function () {
        $geminiResponse = [
            'candidates' => [['finishReason' => 'STOP']]
        ];
        
        $response = ChatResponse::fromGemini('Hello!', $geminiResponse, 'gemini-1.5-flash');
        
        expect($response->getContent())->toBe('Hello!');
        expect($response->hasTokenUsage())->toBeFalse();
    });
});

