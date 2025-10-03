<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Models\ModelFactory;
use Rumenx\PhpChatbot\Models\OpenAiModel;
use Rumenx\PhpChatbot\Models\DefaultAiModel;
use Rumenx\PhpChatbot\Models\AnthropicModel;
use Rumenx\PhpChatbot\Models\XaiModel;
use Rumenx\PhpChatbot\Models\GeminiModel;
use Rumenx\PhpChatbot\Models\MetaModel;
use Rumenx\PhpChatbot\Support\ChatResponse;

it('ModelFactory creates OpenAiModel', function () {
    $config = [
        'model' => OpenAiModel::class,
        'openai' => [ 'api_key' => 'dummy', 'model' => 'gpt-3.5-turbo' ]
    ];
    $model = ModelFactory::make($config);
    expect($model)->toBeInstanceOf(OpenAiModel::class);
});

it('ModelFactory creates DefaultAiModel', function () {
    $config = [ 'model' => DefaultAiModel::class ];
    $model = ModelFactory::make($config);
    expect($model)->toBeInstanceOf(DefaultAiModel::class);
});

it('ModelFactory creates AnthropicModel', function () {
    $config = [ 'model' => AnthropicModel::class, 'anthropic' => [ 'api_key' => 'dummy' ] ];
    $model = ModelFactory::make($config);
    expect($model)->toBeInstanceOf(AnthropicModel::class);
});

it('ModelFactory creates XaiModel', function () {
    $config = [ 'model' => XaiModel::class, 'xai' => [ 'api_key' => 'dummy' ] ];
    $model = ModelFactory::make($config);
    expect($model)->toBeInstanceOf(XaiModel::class);
});

it('ModelFactory creates GeminiModel', function () {
    $config = [ 'model' => GeminiModel::class, 'gemini' => [ 'api_key' => 'dummy' ] ];
    $model = ModelFactory::make($config);
    expect($model)->toBeInstanceOf(GeminiModel::class);
});

it('ModelFactory creates MetaModel', function () {
    $config = [ 'model' => MetaModel::class, 'meta' => [ 'api_key' => 'dummy' ] ];
    $model = ModelFactory::make($config);
    expect($model)->toBeInstanceOf(MetaModel::class);
});

it('ModelFactory throws on invalid class', function () {
    $config = [ 'model' => 'NotAClass' ];
    expect(fn() => ModelFactory::make($config))->toThrow(InvalidArgumentException::class);
});

it('ModelFactory throws on missing model key', function () {
    $config = [];
    expect(fn() => ModelFactory::make($config))->toThrow(InvalidArgumentException::class);
});

it('ModelFactory does not throw on missing config for known model', function () {
    $config = ['model' => 'Rumenx\\PhpChatbot\\Models\\AnthropicModel'];
    expect(fn() => ModelFactory::make($config))->not->toThrow(InvalidArgumentException::class);
});

it('ModelFactory throws on unknown model', function () {
    $config = ['model' => 'UnknownModelClass'];
    expect(fn() => ModelFactory::make($config))->toThrow(InvalidArgumentException::class);
});

it(
    'ModelFactory throws on custom model not implementing interface',
    function () {
        /**
         * Dummy class for test (does not implement AiModelInterface).
         *
         * @category Test
         * @package  Rumenx\PhpChatbot
         * @author   Rumen Damyanov <contact@rumenx.com>
         * @license  MIT License (https://opensource.org/licenses/MIT)
         * @link     https://github.com/RumenDamyanov/php-chatbot
         */
        class NotAChatbot
        {
        }
        expect(fn() => ModelFactory::make(['model' => NotAChatbot::class]))
            ->toThrow(RuntimeException::class);
    }
);

it(
    'ModelFactory throws on custom model with required constructor argument',
    function () {
        /**
         * Dummy class for test (implements AiModelInterface, but requires constructor arg).
         *
         * @category Test
         * @package  Rumenx\PhpChatbot
         * @author   Rumen Damyanov <contact@rumenx.com>
         * @license  MIT License (https://opensource.org/licenses/MIT)
         * @link     https://github.com/RumenDamyanov/php-chatbot
         */
        class NeedsArg implements \Rumenx\PhpChatbot\Contracts\AiModelInterface
        {
            /**
             * NeedsArg constructor.
             *
             * @param mixed $foo Required arg
             */
            public function __construct($foo)
            {
            }
            /**
             * Get a response (dummy).
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return ChatResponse
             */
            public function getResponse(string $input, array $context = []): ChatResponse
            {
                return ChatResponse::fromString('x', 'needs-arg');
            }
        }
        expect(fn() => ModelFactory::make(['model' => NeedsArg::class]))
            ->toThrow(Error::class);
    }
);

it(
    'ModelFactory creates custom model implementing interface with optional constructor arg',
    function () {
        /**
         * Dummy class for test (implements AiModelInterface, optional constructor arg).
         *
         * @category Test
         * @package  Rumenx\PhpChatbot
         * @author   Rumen Damyanov <contact@rumenx.com>
         * @license  MIT License (https://opensource.org/licenses/MIT)
         * @link     https://github.com/RumenDamyanov/php-chatbot
         */
        class OptionalArgModel implements \Rumenx\PhpChatbot\Contracts\AiModelInterface
        {
            /**
             * OptionalArgModel constructor.
             *
             * @param mixed|null $foo Optional arg
             */
            public function __construct($foo = null)
            {
            }
            /**
             * Get a response (dummy).
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return ChatResponse
             */
            public function getResponse(string $input, array $context = []): ChatResponse
            {
                return ChatResponse::fromString('ok', 'optional-arg');
            }
        }
        $config = ['model' => OptionalArgModel::class];
        $model = ModelFactory::make($config);
        expect($model)->toBeInstanceOf(OptionalArgModel::class);
    }
);

it(
    'ModelFactory throws if custom model constructor throws',
    function () {
        /**
         * Dummy class for test (implements AiModelInterface, constructor throws).
         *
         * @category Test
         * @package  Rumenx\PhpChatbot
         * @author   Rumen Damyanov <contact@rumenx.com>
         * @license  MIT License (https://opensource.org/licenses/MIT)
         * @link     https://github.com/RumenDamyanov/php-chatbot
         */
        class ThrowsInCtor implements \Rumenx\PhpChatbot\Contracts\AiModelInterface
        {
            /**
             * ThrowsInCtor constructor.
             */
            public function __construct()
            {
                throw new RuntimeException('fail');
            }
            /**
             * Get a response (dummy).
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return ChatResponse
             */
            public function getResponse(string $input, array $context = []): ChatResponse
            {
                return ChatResponse::fromString('fail', 'throws-in-ctor');
            }
        }
        expect(fn() => ModelFactory::make(['model' => ThrowsInCtor::class]))
            ->toThrow(RuntimeException::class);
    }
);

it(
    'ModelFactory throws on abstract model class',
    function () {
        /**
         * Abstract class for test (implements AiModelInterface).
         *
         * @category Test
         * @package  Rumenx\PhpChatbot
         * @author   Rumen Damyanov <contact@rumenx.com>
         * @license  MIT License (https://opensource.org/licenses/MIT)
         * @link     https://github.com/RumenDamyanov/php-chatbot
         */
        abstract class AbstractModel
            implements \Rumenx\PhpChatbot\Contracts\AiModelInterface
        {
            /**
             * Get a response (dummy).
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return ChatResponse
             */
            public function getResponse(string $input, array $context = []) : ChatResponse
            {
                return ChatResponse::fromString('abstract', 'abstract-model');
            }
        }
        expect(fn() => ModelFactory::make(['model' => AbstractModel::class]))
            ->toThrow(Error::class);
    }
);

it(
    'ModelFactory throws on interface as model',
    function () {
        /**
         * Interface for test (extends AiModelInterface).
         *
         * @category Test
         * @package  Rumenx\PhpChatbot
         * @author   Rumen Damyanov <contact@rumenx.com>
         * @license  MIT License (https://opensource.org/licenses/MIT)
         * @link     https://github.com/RumenDamyanov/php-chatbot
         */
        interface InterfaceModel
            extends \Rumenx\PhpChatbot\Contracts\AiModelInterface
        {
        }
        expect(fn() => ModelFactory::make(['model' => InterfaceModel::class]))
            ->toThrow(InvalidArgumentException::class);
    }
);

it(
    'ModelFactory throws on model with private constructor',
    function () {
        /**
         * Dummy class for test (implements AiModelInterface, private constructor).
         *
         * @category Test
         * @package  Rumenx\PhpChatbot
         * @author   Rumen Damyanov <contact@rumenx.com>
         * @license  MIT License (https://opensource.org/licenses/MIT)
         * @link     https://github.com/RumenDamyanov/php-chatbot
         */
        class PrivateCtorModel
            implements \Rumenx\PhpChatbot\Contracts\AiModelInterface
        {
            /**
             * Private constructor.
             */
            private function __construct()
            {
            }
            /**
             * Get a response (dummy).
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return ChatResponse
             */
            public function getResponse(string $input, array $context = []) : ChatResponse
            {
                return ChatResponse::fromString('private', 'private-ctor');
            }
        }
        expect(fn() => ModelFactory::make(['model' => PrivateCtorModel::class]))
            ->toThrow(Error::class);
    }
);

it(
    'ModelFactory throws on final model with private constructor',
    function () {
        /**
         * Dummy class for test (implements AiModelInterface, final, private constructor).
         *
         * @category Test
         * @package  Rumenx\PhpChatbot
         * @author   Rumen Damyanov <contact@rumenx.com>
         * @license  MIT License (https://opensource.org/licenses/MIT)
         * @link     https://github.com/RumenDamyanov/php-chatbot
         */
        final class FinalPrivateCtorModel
            implements \Rumenx\PhpChatbot\Contracts\AiModelInterface
        {
            /**
             * Private constructor.
             */
            private function __construct()
            {
            }
            /**
             * Get a response (dummy).
             *
             * @param string $input   Input string
             * @param array  $context Context array
             *
             * @return ChatResponse
             */
            public function getResponse(string $input, array $context = []) : ChatResponse
            {
                return ChatResponse::fromString('final', 'final-private-ctor');
            }
        }
        expect(fn() => ModelFactory::make(['model' => FinalPrivateCtorModel::class]))
            ->toThrow(Error::class);
    }
);
