<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

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
     * Create an AI model instance based on the provided configuration.
     *
     * @param array<string, mixed> $config Configuration array for the model.
     *
     * @return AiModelInterface The created AI model instance.
     */
    public static function make(array $config): AiModelInterface
    {
        // Model class name from config
        $modelClass = null;
        if (isset($config['model']) && is_string($config['model'])) {
            // Model class name as string
            $modelClass = $config['model'];
        }
        if (!$modelClass || !class_exists($modelClass)) {
            throw new \InvalidArgumentException(
                'Invalid or missing model class in config.'
            );
        }
        switch ($modelClass) {
        case OpenAiModel::class:
            // OpenAI config array
            $openai = [];
            if (isset($config['openai']) && is_array($config['openai'])) {
                // OpenAI config as array
                $openai = $config['openai'];
            }
            // OpenAI API key
            $apiKey = '';
            if (isset($openai['api_key']) && is_string($openai['api_key'])) {
                // API key as string
                $apiKey = $openai['api_key'];
            }
            // OpenAI model name
            $model = 'gpt-3.5-turbo';
            if (isset($openai['model']) && is_string($openai['model'])) {
                // Model name as string
                $model = $openai['model'];
            }
            // OpenAI endpoint
            $endpoint = 'https://api.openai.com/v1/chat/completions';
            if (isset($openai['endpoint']) && is_string($openai['endpoint'])) {
                // Endpoint as string
                $endpoint = $openai['endpoint'];
            }
            return new OpenAiModel($apiKey, $model, $endpoint);
        case AnthropicModel::class:
            // Anthropic config array
            $anthropic = [];
            if (isset($config['anthropic']) && is_array($config['anthropic'])) {
                // Anthropic config as array
                $anthropic = $config['anthropic'];
            }
            // Anthropic API key
            $apiKey = '';
            if (isset($anthropic['api_key']) && is_string($anthropic['api_key'])) {
                // API key as string
                $apiKey = $anthropic['api_key'];
            }
            // Anthropic model name
            $model = 'claude-3-sonnet-20240229';
            if (isset($anthropic['model']) && is_string($anthropic['model'])) {
                // Model name as string
                $model = $anthropic['model'];
            }
            // Anthropic endpoint
            $endpoint = 'https://api.anthropic.com/v1/messages';
            if (isset($anthropic['endpoint']) && is_string($anthropic['endpoint'])) {
                // Endpoint as string
                $endpoint = $anthropic['endpoint'];
            }
            return new AnthropicModel($apiKey, $model, $endpoint);
        case XaiModel::class:
            // xAI config array
            $xai = [];
            if (isset($config['xai']) && is_array($config['xai'])) {
                // xAI config as array
                $xai = $config['xai'];
            }
            // xAI API key
            $apiKey = '';
            if (isset($xai['api_key']) && is_string($xai['api_key'])) {
                // API key as string
                $apiKey = $xai['api_key'];
            }
            // xAI model name
            $model = 'grok-1';
            if (isset($xai['model']) && is_string($xai['model'])) {
                // Model name as string
                $model = $xai['model'];
            }
            // xAI endpoint
            $endpoint = 'https://api.x.ai/v1/chat/completions';
            if (isset($xai['endpoint']) && is_string($xai['endpoint'])) {
                // Endpoint as string
                $endpoint = $xai['endpoint'];
            }
            return new XaiModel($apiKey, $model, $endpoint);
        case GeminiModel::class:
            // Gemini config array
            $gemini = [];
            if (isset($config['gemini']) && is_array($config['gemini'])) {
                // Gemini config as array
                $gemini = $config['gemini'];
            }
            // Gemini API key
            $apiKey = '';
            if (isset($gemini['api_key']) && is_string($gemini['api_key'])) {
                // API key as string
                $apiKey = $gemini['api_key'];
            }
            // Gemini model name
            $model = 'gemini-1.5-pro';
            if (isset($gemini['model']) && is_string($gemini['model'])) {
                // Model name as string
                $model = $gemini['model'];
            }
            // Gemini endpoint
            $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models';
            if (isset($gemini['endpoint']) && is_string($gemini['endpoint'])) {
                // Endpoint as string
                $endpoint = $gemini['endpoint'];
            }
            return new GeminiModel($apiKey, $model, $endpoint);
        case MetaModel::class:
            // Meta config array
            $meta = [];
            if (isset($config['meta']) && is_array($config['meta'])) {
                // Meta config as array
                $meta = $config['meta'];
            }
            // Meta API key
            $apiKey = '';
            if (isset($meta['api_key']) && is_string($meta['api_key'])) {
                // API key as string
                $apiKey = $meta['api_key'];
            }
            // Meta model name
            $model = 'llama-3-70b';
            if (isset($meta['model']) && is_string($meta['model'])) {
                // Model name as string
                $model = $meta['model'];
            }
            // Meta endpoint
            $endpoint = 'https://api.meta.ai/v1/chat/completions';
            if (isset($meta['endpoint']) && is_string($meta['endpoint'])) {
                // Endpoint as string
                $endpoint = $meta['endpoint'];
            }
            return new MetaModel($apiKey, $model, $endpoint);
        case DefaultAiModel::class:
            return new DefaultAiModel();
        default:
            // Allow custom user models
            $instance = new $modelClass();
            if (!$instance instanceof AiModelInterface) {
                throw new \RuntimeException(
                    'Custom model must implement AiModelInterface'
                );
            }
            return $instance;
        }
    }
}
