<?php

return [
    // Default to free model
    'model' => Rumenx\PhpChatbot\Models\DefaultAiModel::class,
    'prompt' => 'You are a helpful, friendly chatbot.',
    'language' => 'en',
    'tone' => 'neutral',
    'rate_limit' => 10, // requests per minute
    'allowed_scripts' => ['Latin', 'Cyrillic', 'Greek', 'Armenian', 'Han', 'Kana', 'Hangul'],
    'emojis' => true,
    'deescalate' => true,
    'funny' => false,
    // Model-specific config
    'openai' => [
        'api_key' => getenv('OPENAI_API_KEY') ?: '',
        'model' => 'gpt-4o', // Options: gpt-4.1, gpt-4o, gpt-4o-mini, gpt-3.5-turbo, etc.
        'endpoint' => 'https://api.openai.com/v1/chat/completions',
    ],
    'anthropic' => [
        'api_key' => getenv('ANTHROPIC_API_KEY') ?: '',
        'model' => 'claude-3-sonnet-20240229', // Options: claude-3-sonnet-20240229, claude-3-7, claude-4, etc.
        'endpoint' => 'https://api.anthropic.com/v1/messages',
    ],
    'xai' => [
        'api_key' => getenv('XAI_API_KEY') ?: '',
        'model' => 'grok-1', // Options: grok-1, grok-1.5, etc.
        'endpoint' => 'https://api.x.ai/v1/chat/completions',
    ],
    'gemini' => [
        'api_key' => getenv('GEMINI_API_KEY') ?: '',
        'model' => 'gemini-1.5-pro', // Options: gemini-1.5-pro, gemini-1.5-flash, etc.
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
    ],
    'meta' => [
        'api_key' => getenv('META_API_KEY') ?: '',
        'model' => 'llama-3-70b', // Options: llama-3-8b, llama-3-70b, etc.
        'endpoint' => 'https://api.meta.ai/v1/chat/completions',
    ],
];
