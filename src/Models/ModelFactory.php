<?php

namespace Rumenx\PhpChatbot\Models;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;

class ModelFactory
{
    /**
     * @param array<string, mixed> $config
     */
    public static function make(array $config): AiModelInterface
    {
        $modelClass = $config['model'] ?? null;
        if (!$modelClass || !class_exists($modelClass)) {
            throw new \InvalidArgumentException('Invalid or missing model class in config.');
        }
        // Pass API keys and model options from config if needed
        switch ($modelClass) {
            case OpenAiModel::class:
                return new OpenAiModel(
                    $config['openai']['api_key'] ?? '',
                    $config['openai']['model'] ?? 'gpt-3.5-turbo',
                    $config['openai']['endpoint'] ?? 'https://api.openai.com/v1/chat/completions'
                );
            case AnthropicModel::class:
                return new AnthropicModel(
                    $config['anthropic']['api_key'] ?? '',
                    $config['anthropic']['model'] ?? 'claude-3-sonnet-20240229',
                    $config['anthropic']['endpoint'] ?? 'https://api.anthropic.com/v1/messages'
                );
            case XaiModel::class:
                return new XaiModel(
                    $config['xai']['api_key'] ?? '',
                    $config['xai']['model'] ?? 'grok-1',
                    $config['xai']['endpoint'] ?? 'https://api.x.ai/v1/chat/completions'
                );
            case GeminiModel::class:
                return new GeminiModel(
                    $config['gemini']['api_key'] ?? '',
                    $config['gemini']['model'] ?? 'gemini-1.5-pro',
                    $config['gemini']['endpoint'] ?? 'https://generativelanguage.googleapis.com/v1beta/models'
                );
            case MetaModel::class:
                return new MetaModel(
                    $config['meta']['api_key'] ?? '',
                    $config['meta']['model'] ?? 'llama-3-70b',
                    $config['meta']['endpoint'] ?? 'https://api.meta.ai/v1/chat/completions'
                );
            case DefaultAiModel::class:
                return new DefaultAiModel();
            default:
                // Allow custom user models
                return new $modelClass();
        }
    }
}
