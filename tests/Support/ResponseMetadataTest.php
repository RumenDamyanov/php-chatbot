<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\TokenUsage;
use Rumenx\PhpChatbot\Support\ResponseMetadata;

describe('ResponseMetadata', function () {
    it('creates instance with required parameters', function () {
        $metadata = new ResponseMetadata('gpt-4o-mini');
        
        expect($metadata->getModel())->toBe('gpt-4o-mini');
        expect($metadata->getTokenUsage())->toBeNull();
        expect($metadata->getFinishReason())->toBeNull();
    });

    it('creates instance with all parameters', function () {
        $tokenUsage = new TokenUsage(100, 50, 150);
        $metadata = new ResponseMetadata(
            model: 'gpt-4o',
            tokenUsage: $tokenUsage,
            finishReason: 'stop',
            id: 'chatcmpl-123',
            created: 1234567890,
            extra: ['system_fingerprint' => 'fp_123']
        );
        
        expect($metadata->getModel())->toBe('gpt-4o');
        expect($metadata->getTokenUsage())->toBe($tokenUsage);
        expect($metadata->getFinishReason())->toBe('stop');
        expect($metadata->getId())->toBe('chatcmpl-123');
        expect($metadata->getCreated())->toBe(1234567890);
        expect($metadata->getExtra())->toBe(['system_fingerprint' => 'fp_123']);
    });

    it('checks if has token usage', function () {
        $metadataWithUsage = new ResponseMetadata(
            'gpt-4o',
            new TokenUsage(100, 50, 150)
        );
        $metadataWithoutUsage = new ResponseMetadata('gpt-4o');
        
        expect($metadataWithUsage->hasTokenUsage())->toBeTrue();
        expect($metadataWithoutUsage->hasTokenUsage())->toBeFalse();
    });

    it('gets extra metadata value', function () {
        $metadata = new ResponseMetadata(
            'gpt-4o',
            null,
            null,
            null,
            null,
            ['key1' => 'value1', 'key2' => 'value2']
        );
        
        expect($metadata->getExtraValue('key1'))->toBe('value1');
        expect($metadata->getExtraValue('key2'))->toBe('value2');
        expect($metadata->getExtraValue('key3'))->toBeNull();
        expect($metadata->getExtraValue('key3', 'default'))->toBe('default');
    });

    it('detects truncated responses', function () {
        $truncated1 = new ResponseMetadata('gpt-4o', null, 'length');
        $truncated2 = new ResponseMetadata('gpt-4o', null, 'max_tokens');
        $truncated3 = new ResponseMetadata('gpt-4o', null, 'truncated');
        $notTruncated = new ResponseMetadata('gpt-4o', null, 'stop');
        
        expect($truncated1->wasTruncated())->toBeTrue();
        expect($truncated2->wasTruncated())->toBeTrue();
        expect($truncated3->wasTruncated())->toBeTrue();
        expect($notTruncated->wasTruncated())->toBeFalse();
    });

    it('detects filtered responses', function () {
        $filtered1 = new ResponseMetadata('gpt-4o', null, 'content_filter');
        $filtered2 = new ResponseMetadata('gpt-4o', null, 'safety');
        $filtered3 = new ResponseMetadata('gpt-4o', null, 'policy_violation');
        $notFiltered = new ResponseMetadata('gpt-4o', null, 'stop');
        
        expect($filtered1->wasFiltered())->toBeTrue();
        expect($filtered2->wasFiltered())->toBeTrue();
        expect($filtered3->wasFiltered())->toBeTrue();
        expect($notFiltered->wasFiltered())->toBeFalse();
    });

    it('detects normal completion', function () {
        $normal = new ResponseMetadata('gpt-4o', null, 'stop');
        $abnormal = new ResponseMetadata('gpt-4o', null, 'length');
        
        expect($normal->wasCompletedNormally())->toBeTrue();
        expect($abnormal->wasCompletedNormally())->toBeFalse();
    });

    it('converts to array', function () {
        $tokenUsage = new TokenUsage(100, 50, 150);
        $metadata = new ResponseMetadata(
            model: 'gpt-4o',
            tokenUsage: $tokenUsage,
            finishReason: 'stop',
            id: 'chatcmpl-123',
            created: 1234567890,
            extra: ['test' => 'value']
        );
        
        $array = $metadata->toArray();
        
        expect($array)->toBeArray();
        expect($array['model'])->toBe('gpt-4o');
        expect($array['token_usage'])->toBeArray();
        expect($array['finish_reason'])->toBe('stop');
        expect($array['id'])->toBe('chatcmpl-123');
        expect($array['created'])->toBe(1234567890);
        expect($array['extra'])->toBe(['test' => 'value']);
    });

    it('generates summary with token usage', function () {
        $tokenUsage = new TokenUsage(100, 50, 150);
        $metadata = new ResponseMetadata('gpt-4o', $tokenUsage, 'stop', 'msg_123', time());
        $summary = $metadata->getSummary();
        
        expect($summary)->toBeString();
        expect($summary)->toContain('gpt-4o');
        expect($summary)->toContain('stop');
        expect($summary)->toContain('Tokens:');
    });

    it('generates summary without token usage', function () {
        $metadata = new ResponseMetadata('gpt-4o', null, 'stop');
        
        $summary = $metadata->getSummary();
        
        expect($summary)->toBeString();
        expect($summary)->toContain('gpt-4o');
        expect($summary)->toContain('stop');
    });

    it('handles null finish reason in summary', function () {
        $metadata = new ResponseMetadata('gpt-4o');
        
        $summary = $metadata->getSummary();
        
        expect($summary)->toBeString();
        expect($summary)->toContain('gpt-4o');
    });
});

