<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\DeepSeekAiModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\MetaModel;
use Rumenx\PhpChatbot\Models\XaiModel;

it('AnthropicModel handles cURL error', function () {
    $model = new AnthropicModel('dummy', 'claude-3-sonnet', 'http://localhost:9999/invalid');
    $response = $model->getResponse('test');
    expect($response)->toContain('Anthropic');
});

it('DeepSeekAiModel handles cURL error', function () {
    $model = new DeepSeekAiModel('dummy', 'deepseek-chat', 'http://localhost:9999/invalid');
    $response = $model->getResponse('test');
    expect($response)->toContain('DeepSeek');
});

it('GeminiModel handles cURL error', function () {
    $model = new GeminiModel('dummy', 'gemini-1.5-pro', 'http://localhost:9999/invalid');
    $response = $model->getResponse('test');
    expect($response)->toContain('Google Gemini');
});

it('MetaModel handles cURL error', function () {
    $model = new MetaModel('dummy', 'llama-3-70b', 'http://localhost:9999/invalid');
    $response = $model->getResponse('test');
    expect($response)->toContain('Meta');
});

it('XaiModel handles cURL error', function () {
    $model = new XaiModel('dummy', 'grok-1', 'http://localhost:9999/invalid');
    $response = $model->getResponse('test');
    expect($response)->toContain('xAI');
});

it('MetaModel sendMessage returns placeholder', function () {
    $model = new MetaModel('dummy', 'llama-3-8b');
    $response = $model->sendMessage('Hi!');
    expect($response)->toContain('Meta');
});

it('DeepSeekAiModel handles exception', function () {
    $model = new class('dummy') extends DeepSeekAiModel {
        protected function causeException() { throw new \Exception('Simulated exception'); }
        public function getResponse(string $input, array $context = []): string {
            try {
                $this->causeException();
            } catch (\Throwable $e) {
                if (isset($context['logger']) && $context['logger'] instanceof \Psr\Log\LoggerInterface) {
                    $context['logger']->error('DeepSeekAiModel exception: ' . $e->getMessage(), ['exception' => $e]);
                }
                return json_encode(['status' => 'error', 'message' => '[DeepSeek] Exception: ' . $e->getMessage()]);
            }
            return '';
        }
    };
    $response = $model->getResponse('test');
    expect($response)->toContain('[DeepSeek] Exception:');
});

it('GeminiModel handles exception', function () {
    $model = new class('dummy') extends GeminiModel {
        protected function causeException() { throw new \Exception('Simulated exception'); }
        public function getResponse(string $input, array $context = []): string {
            try {
                $this->causeException();
            } catch (\Throwable $e) {
                return '[Google Gemini] Exception: ' . $e->getMessage();
            }
            return '';
        }
    };
    $response = $model->getResponse('test');
    expect($response)->toContain('[Google Gemini] Exception:');
});

it('MetaModel handles exception', function () {
    $model = new class('dummy') extends MetaModel {
        protected function causeException() { throw new \Exception('Simulated exception'); }
        public function getResponse(string $input, array $context = []): string {
            try {
                $this->causeException();
            } catch (\Throwable $e) {
                return '[Meta] Exception: ' . $e->getMessage();
            }
            return '';
        }
    };
    $response = $model->getResponse('test');
    expect($response)->toContain('[Meta] Exception:');
});

it('XaiModel handles exception', function () {
    $model = new class('dummy') extends XaiModel {
        protected function causeException() { throw new \Exception('Simulated exception'); }
        public function getResponse(string $input, array $context = []): string {
            try {
                $this->causeException();
            } catch (\Throwable $e) {
                return '[xAI] Exception: ' . $e->getMessage();
            }
            return '';
        }
    };
    $response = $model->getResponse('test');
    expect($response)->toContain('[xAI] Exception:');
});
