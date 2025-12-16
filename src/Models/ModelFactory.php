<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;
use Rumenx\PhpChatbot\Exceptions\InvalidConfigException;
use Rumenx\PhpChatbot\Exceptions\ModelException;

/**
 * ModelFactory for the php-chatbot package.
 *
 * This class provides a factory for creating AI model instances based on config.
 *
 * @category Models
 * @package  Rumenx\PhpChatbot
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  MIT License (https://opensource.org/licenses/MIT)
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class ModelFactory
{
    /**
     * Registry of known model classes and their configuration keys.
     *
     * @var array<string, array{key: string, default_model: string, default_endpoint: string}>
     */
    private const MODEL_REGISTRY = [
        OpenAiModel::class => [
            'key' => 'openai',
            'default_model' => 'gpt-4o-mini',
            'default_endpoint' => 'https://api.openai.com/v1/chat/completions',
        ],
        AnthropicModel::class => [
            'key' => 'anthropic',
            'default_model' => 'claude-3-5-sonnet-20241022',
            'default_endpoint' => 'https://api.anthropic.com/v1/messages',
        ],
        XaiModel::class => [
            'key' => 'xai',
            'default_model' => 'grok-2-1212',
            'default_endpoint' => 'https://api.x.ai/v1/chat/completions',
        ],
        GeminiModel::class => [
            'key' => 'gemini',
            'default_model' => 'gemini-1.5-flash',
            'default_endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
        ],
        MetaModel::class => [
            'key' => 'meta',
            'default_model' => 'llama-3.3-70b-versatile',
            'default_endpoint' => 'https://api.meta.ai/v1/chat/completions',
        ],
        DeepSeekAiModel::class => [
            'key' => 'deepseek',
            'default_model' => 'deepseek-chat',
            'default_endpoint' => 'https://api.deepseek.com/v1/chat/completions',
        ],
    ];

    /**
     * Create an AI model instance based on the provided configuration.
     *
     * @param array<string, mixed> $config Configuration array for the model.
     *
     * @return AiModelInterface The created AI model instance.
     * @throws InvalidConfigException If configuration is invalid.
     * @throws ModelException If model cannot be created.
     */
    public static function make(array $config): AiModelInterface
    {
        // Validate and extract model class
        $modelClass = self::validateModelClass($config);
        // Handle DefaultAiModel (no configuration needed)
        if ($modelClass === DefaultAiModel::class) {
            return new DefaultAiModel();
        }

        // Handle registered models
        if (isset(self::MODEL_REGISTRY[$modelClass])) {
            return self::createRegisteredModel($modelClass, $config);
        }

        // Handle custom user models
        return self::createCustomModel($modelClass);
    }

    /**
     * Validate and extract the model class from configuration.
     *
     * @param array<string, mixed> $config Configuration array.
     *
     * @return string The validated model class name.
     * @throws InvalidConfigException If model class is invalid or missing.
     */
    private static function validateModelClass(array $config): string
    {
        if (!isset($config['model'])) {
            throw new InvalidConfigException(
                'Missing required "model" key in configuration.'
            );
        }

        if (!is_string($config['model'])) {
            throw new InvalidConfigException(
                'Model class must be a string, ' . gettype($config['model']) . ' given.'
            );
        }

        $modelClass = $config['model'];

        if (!class_exists($modelClass)) {
            throw new InvalidConfigException(
                "Model class '{$modelClass}' does not exist."
            );
        }

        return $modelClass;
    }

    /**
     * Create a registered model instance.
     *
     * @param string               $modelClass Model class name.
     * @param array<string, mixed> $config     Full configuration array.
     *
     * @return AiModelInterface The created model instance.
     * @throws InvalidConfigException If configuration is invalid.
     */
    private static function createRegisteredModel(string $modelClass, array $config): AiModelInterface
    {
        $registry = self::MODEL_REGISTRY[$modelClass];
        $configKey = $registry['key'];
        
        // Extract model-specific configuration
        $modelConfig = $config[$configKey] ?? [];
        
        if (!is_array($modelConfig)) {
            throw new InvalidConfigException(
                "Configuration for '{$configKey}' must be an array, " . gettype($modelConfig) . ' given.'
            );
        }

        // Extract and validate parameters
        $apiKey = self::extractString($modelConfig, 'api_key', '');
        $model = self::extractString($modelConfig, 'model', $registry['default_model']);
        $endpoint = self::extractString($modelConfig, 'endpoint', $registry['default_endpoint']);

        // Note: API keys and endpoints are validated at runtime by the models themselves.
        // This allows flexibility for development/testing scenarios.

        // Create the model instance
        return new $modelClass($apiKey, $model, $endpoint);
    }

    /**
     * Create a custom user model instance.
     *
     * @param string $modelClass Model class name.
     *
     * @return AiModelInterface The created model instance.
     * @throws ModelException If custom model cannot be created.
     * @throws \Error If PHP error occurs (abstract class, private constructor, etc.).
     */
    private static function createCustomModel(string $modelClass): AiModelInterface
    {
        try {
            $instance = new $modelClass();
        } catch (\Error $e) {
            // Re-throw PHP errors (abstract class, private constructor, etc.)
            // These represent programming errors, not runtime errors
            throw $e;
        } catch (\Exception $e) {
            // Wrap exceptions (runtime errors) in ModelException
            throw new ModelException(
                "Failed to instantiate custom model '{$modelClass}': " . $e->getMessage(),
                0,
                $e
            );
        }

        if (!$instance instanceof AiModelInterface) {
            throw new ModelException(
                "Custom model '{$modelClass}' must implement AiModelInterface."
            );
        }

        return $instance;
    }

    /**
     * Extract a string value from an array with a default fallback.
     *
     * @param array<string, mixed> $array   The array to extract from.
     * @param string               $key     The key to extract.
     * @param string               $default Default value if key is missing or not a string.
     *
     * @return string The extracted string value.
     */
    private static function extractString(array $array, string $key, string $default): string
    {
        if (!isset($array[$key])) {
            return $default;
        }

        if (!is_string($array[$key])) {
            return $default;
        }

        return $array[$key];
    }
}
