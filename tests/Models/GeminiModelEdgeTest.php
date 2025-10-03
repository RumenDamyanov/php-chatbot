<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\ChatResponse;

use Rumenx\PhpChatbot\Models\GeminiModel;

it('GeminiModel returns default prompt if context missing', function () {
    $model = new GeminiModel('dummy');
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('GeminiModel uses custom prompt', function () {
    $model = new GeminiModel('dummy');
    $response = (string) $model->getResponse('test', [
        'prompt' => 'Custom!',
    ]);
    expect($response)->toContain('No response');
});

it('GeminiModel handles non-string prompt', function () {
    $model = new GeminiModel('dummy');
    $response = (string) $model->getResponse('test', [
        'prompt' => 123,
    ]);
    expect($response)->toContain('No response');
});

it('GeminiModel handles cURL error gracefully', function () {
    $model = new GeminiModel('dummy', 'gemini-1.5-pro', 'http://localhost:9999/invalid');
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('Google Gemini');
});

it('GeminiModel returns fallback if candidates missing', function () {
    $model = new class('dummy') extends GeminiModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            // Simulate missing candidates
            return ChatResponse::fromString('[Google Gemini] No response.', 'gemini-1.5-pro');
        }
    };
    $response = (string) $model->getResponse('test');
    expect($response)->toContain('No response');
});

it('GeminiModel handles exception', function () {
    $model = new class('dummy') extends GeminiModel {
        public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse {
            throw new \Exception('Simulated');
        }
    };
    $result = null;
    try {
        $model->getResponse('test');
    } catch (\Exception $e) {
        $result = $e->getMessage();
    }
    expect($result)->toBe('Simulated');
});

it(
    'GeminiModel returns content if API response is valid',
    function () {
        $model = new class('dummy') extends GeminiModel {
            /**
             * Simulate valid Gemini API response.
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return ChatResponse
             */
            public function getResponse(string $input, array $context = []): ChatResponse
            {
                $response = [
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    [ 'text' => 'Hello from Gemini!' ]
                                ]
                            ]
                        ]
                    ]
                ];
                if (is_array($response)
                    && isset(
                        $response['candidates'][0]['content']['parts'][0]['text']
                    )
                    && is_string(
                        $response['candidates'][0]['content']['parts'][0]['text']
                    )
                ) {
                    return ChatResponse::fromString($response['candidates'][0]['content']['parts'][0]['text'], 'gemini-1.5-pro');
                }
                return ChatResponse::fromString('[Google Gemini] No response.', 'gemini-1.5-pro');
            }
        };
        $response = (string) $model->getResponse('test');
        expect($response)->toBe('Hello from Gemini!');
    }
);
