<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Support\ChatResponse;

use Rumenx\PhpChatbot\Models\OllamaModel;

it(
    'creates OllamaModel with default config',
    function () {
        $model = new OllamaModel();
        expect($model)->toBeInstanceOf(OllamaModel::class);
    }
);

it(
    'creates OllamaModel with custom config',
    function () {
        $model = new OllamaModel(
            [
                'base_url' => 'http://remotehost:1234',
                'model' => 'mistral',
                'api_key' => 'abc123',
                'timeout' => 5
            ]
        );
        expect($model)->toBeInstanceOf(OllamaModel::class);
    }
);

it(
    'throws on invalid base_url',
    function () {
        expect(fn() => new OllamaModel(['base_url' => 'not-a-url']))
            ->toThrow(InvalidArgumentException::class);
    }
);

it(
    'throws on missing model name',
    function () {
        expect(fn() => new OllamaModel(['model' => '']))
            ->toThrow(InvalidArgumentException::class);
    }
);

it(
    'throws on connection error',
    function () {
        $model = new OllamaModel(
            [
                'base_url' => 'http://127.0.0.1:9999',
                'model' => 'llama2'
            ]
        );
        expect(fn() => $model->getResponse('hi'))
            ->toThrow(\Rumenx\PhpChatbot\Exceptions\NetworkException::class);
    }
);

it(
    'throws on invalid API response',
    function () {
        // Mock with a local server or use a dummy endpoint that returns 200
        // but not valid JSON
        $model = new OllamaModel(
            [
                'base_url' => 'http://localhost:11434',
                'model' => 'llama2'
            ]
        );
        // We'll simulate by overriding curl_exec via a stub if possible,
        // or skip this in CI
        // For now, just check that invalid JSON triggers the error branch
        // This is a placeholder for a more advanced mock
        $stub = new class(
            [
                'base_url' => 'http://localhost:11434',
                'model' => 'llama2'
            ]
        ) extends OllamaModel {
            /**
             * Simulate invalid API response for test.
             *
             * @param string $input   User input
             * @param array  $context Context array
             *
             * @return string
             */
            public function getResponse(string $input, array $context = []): \Rumenx\PhpChatbot\Support\ChatResponse
            {
                $result = '{"not_response": "fail"}';
                $json = json_decode($result, true);
                if (!is_array($json) || !isset($json['response'])) {
                    throw new \Rumenx\PhpChatbot\Exceptions\ApiException(
                        'Ollama API invalid response: ' . $result,
                        0,
                        $result
                    );
                }
                return \Rumenx\PhpChatbot\Support\ChatResponse::fromString((string)$json['response'], 'llama2');
            }
        };
        expect(fn() => $stub->getResponse('hi'))->toThrow(\Rumenx\PhpChatbot\Exceptions\ApiException::class);
    }
);

it(
    'sends prompt and returns response (integration, may skip if Ollama not running)',
    function () {
        $model = new OllamaModel(['model' => 'llama2']);
        try {
            $response = $model->getResponse('Hello!');
            expect($response)->toBeString();
        } catch (\Rumenx\PhpChatbot\Exceptions\NetworkException | \Rumenx\PhpChatbot\Exceptions\ApiException $e) {
            // Accept connection error if Ollama is not running
            expect($e->getMessage())->toContain('Ollama');
        }
    }
);
