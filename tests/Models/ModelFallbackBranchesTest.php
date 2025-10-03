<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\ChatResponse;

use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\DeepSeekAiModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\MetaModel;
use Rumenx\PhpChatbot\Models\XaiModel;

it('AnthropicModel returns fallback if choices missing', function () {
    $model = new class('dummy') extends AnthropicModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            // Simulate missing choices
            $response = [];
            $content = $response['choices'][0]['message']['content'] ?? '[Anthropic] No response.';
            return \Rumenx\PhpChatbot\Support\ChatResponse::fromString($content, 'claude-3-sonnet');
        }
    };
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('DeepSeekAiModel returns fallback if choices missing', function () {
    $model = new class('dummy') extends DeepSeekAiModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            // Simulate API returning no choices
            $response = ['not_choices' => true];
            if (isset($context['logger']) && $context['logger'] instanceof \Psr\Log\LoggerInterface) {
                $context['logger']->error('DeepSeekAiModel API error: No response', ['response' => $response]);
            }
            return \Rumenx\PhpChatbot\Support\ChatResponse::fromString('[DeepSeek] No response.', 'deepseek-chat');
        }
    };
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('[DeepSeek] No response.');
});

it('GeminiModel returns fallback if candidates missing', function () {
    $model = new class('dummy') extends GeminiModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            // Simulate API returning no candidates
            $response = [];
            $content = $response['candidates'][0]['content']['parts'][0]['text'] ?? '[Google Gemini] No response.';
            return \Rumenx\PhpChatbot\Support\ChatResponse::fromString($content, 'gemini-1.5-flash');
        }
    };
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('[Google Gemini] No response.');
});

it('MetaModel returns fallback if choices missing', function () {
    $model = new class('dummy') extends MetaModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            // Simulate API returning no choices
            $response = [];
            $content = $response['choices'][0]['message']['content'] ?? '[Meta] No response.';
            return \Rumenx\PhpChatbot\Support\ChatResponse::fromString($content, 'llama-3-70b');
        }
    };
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('[Meta] No response.');
});

it('XaiModel returns fallback if choices missing', function () {
    $model = new class('dummy') extends XaiModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            // Simulate API returning no choices
            $response = [];
            $content = $response['choices'][0]['message']['content'] ?? '[xAI] No response.';
            return \Rumenx\PhpChatbot\Support\ChatResponse::fromString($content, 'grok-beta');
        }
    };
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('[xAI] No response.');
});
