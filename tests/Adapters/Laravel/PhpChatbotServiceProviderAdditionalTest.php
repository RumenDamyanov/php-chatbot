<?php

use Rumenx\PhpChatbot\Models\OllamaModel;

describe(
    'OllamaModel Additional Coverage Tests',
    function () {
        it(
            'OllamaModel validates base_url format',
            function () {
                $config = [
                    'base_url' => 'invalid-url',
                    'model' => 'llama2'
                ];

                expect(fn() => new OllamaModel($config))
                    ->toThrow(\InvalidArgumentException::class);
            }
        );

        it(
            'OllamaModel requires model name',
            function () {
                $config = [
                    'base_url' => 'http://localhost:11434',
                    'model' => ''
                ];

                expect(fn() => new OllamaModel($config))
                    ->toThrow(\InvalidArgumentException::class);
            }
        );

        it(
            'OllamaModel creates instance with valid config',
            function () {
                $config = [
                    'base_url' => 'http://localhost:11434',
                    'model' => 'llama2',
                    'timeout' => 30
                ];

                $model = new OllamaModel($config);

                expect($model)->toBeInstanceOf(OllamaModel::class);
            }
        );
    }
);
