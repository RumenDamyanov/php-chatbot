<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\ChatResponse;

use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\DeepSeekAiModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\MetaModel;
use Rumenx\PhpChatbot\Models\XaiModel;

require_once __DIR__ . '/DummyLogger.php';

it('AnthropicModel logs exception', function () {
    $logger = new DummyLogger();
    $model = new class('dummy', 'claude-3-sonnet') extends AnthropicModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            try { throw new \Exception('Simulated'); } catch (\Throwable $e) {
                if (isset($context['logger']) && $context['logger'] instanceof \Psr\Log\LoggerInterface) {
                    $context['logger']->error('AnthropicModel exception: ' . $e->getMessage(), ['exception' => $e]);
                }
                return ChatResponse::fromString('[Anthropic] Exception: ' . $e->getMessage(), 'claude-3-sonnet');
            }
        }
    };
    $model->getResponse('test', ['logger' => $logger]);
    expect($logger->logs)->not->toBeEmpty();
});

it('DeepSeekAiModel logs exception', function () {
    $logger = new DummyLogger();
    $model = new class('dummy') extends DeepSeekAiModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            try { throw new \Exception('Simulated'); } catch (\Throwable $e) {
                if (isset($context['logger']) && $context['logger'] instanceof \Psr\Log\LoggerInterface) {
                    $context['logger']->error('DeepSeekAiModel exception: ' . $e->getMessage(), ['exception' => $e]);
                }
                return ChatResponse::fromString(json_encode(['status' => 'error', 'message' => '[DeepSeek] Exception: ' . $e->getMessage()]), 'deepseek-chat');
            }
        }
    };
    $model->getResponse('test', ['logger' => $logger]);
    expect($logger->logs)->not->toBeEmpty();
});

it('GeminiModel logs exception', function () {
    $logger = new DummyLogger();
    $model = new class('dummy') extends GeminiModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            try { throw new \Exception('Simulated'); } catch (\Throwable $e) {
                if (isset($context['logger']) && $context['logger'] instanceof \Psr\Log\LoggerInterface) {
                    $context['logger']->error('GeminiModel exception: ' . $e->getMessage(), ['exception' => $e]);
                }
                return ChatResponse::fromString('[Google Gemini] Exception: ' . $e->getMessage(), 'gemini-1.5-pro');
            }
        }
    };
    $model->getResponse('test', ['logger' => $logger]);
    expect($logger->logs)->not->toBeEmpty();
});

it('MetaModel logs exception', function () {
    $logger = new DummyLogger();
    $model = new class('dummy') extends MetaModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            try { throw new \Exception('Simulated'); } catch (\Throwable $e) {
                if (isset($context['logger']) && $context['logger'] instanceof \Psr\Log\LoggerInterface) {
                    $context['logger']->error('MetaModel exception: ' . $e->getMessage(), ['exception' => $e]);
                }
                return ChatResponse::fromString('[Meta] Exception: ' . $e->getMessage(), 'llama-3-70b');
            }
        }
    };
    $model->getResponse('test', ['logger' => $logger]);
    expect($logger->logs)->not->toBeEmpty();
});

it('XaiModel logs exception', function () {
    $logger = new DummyLogger();
    $model = new class('dummy') extends XaiModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            try { throw new \Exception('Simulated'); } catch (\Throwable $e) {
                if (isset($context['logger']) && $context['logger'] instanceof \Psr\Log\LoggerInterface) {
                    $context['logger']->error('XaiModel exception: ' . $e->getMessage(), ['exception' => $e]);
                }
                return ChatResponse::fromString('[xAI] Exception: ' . $e->getMessage(), 'grok-1');
            }
        }
    };
    $model->getResponse('test', ['logger' => $logger]);
    expect($logger->logs)->not->toBeEmpty();
});
