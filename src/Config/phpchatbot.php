<?php

declare(strict_types=1);

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
    
    // Error handling
    'throw_exceptions' => false, // Set to true to throw exceptions instead of returning error messages
    
    // Rate limiting
    'rate_limit_key' => null,      // Unique identifier (e.g., user ID, IP). Defaults to sessionId or 'default'
    'rate_limit_max' => 10,        // Maximum requests allowed
    'rate_limit_window' => 60,     // Time window in seconds
    
    // Response caching
    'cache_enabled' => true,       // Enable/disable caching per request
    'cache_ttl' => 3600,           // Cache time-to-live in seconds (1 hour default)
    'cache_key_components' => [],  // Additional data to include in cache key generation
    // Model-specific config
    'openai' => [
        'api_key' => getenv('OPENAI_API_KEY') ?: '',
        'model' => 'gpt-4o-mini', // Options: gpt-4o, gpt-4o-mini, gpt-4-turbo, gpt-3.5-turbo (legacy), etc.
        'endpoint' => 'https://api.openai.com/v1/chat/completions',
    ],
    'anthropic' => [
        'api_key' => getenv('ANTHROPIC_API_KEY') ?: '',
        // Options: claude-3-5-sonnet-20241022, claude-3-5-haiku-20241022, claude-3-opus-20240229
        'model' => 'claude-3-5-sonnet-20241022',
        'endpoint' => 'https://api.anthropic.com/v1/messages',
    ],
    'xai' => [
        'api_key' => getenv('XAI_API_KEY') ?: '',
        'model' => 'grok-2-1212', // Options: grok-2-1212, grok-2-vision-1212, grok-beta, etc.
        'endpoint' => 'https://api.x.ai/v1/chat/completions',
    ],
    'gemini' => [
        'api_key' => getenv('GEMINI_API_KEY') ?: '',
        'model' => 'gemini-1.5-flash', // Options: gemini-1.5-flash, gemini-1.5-pro, gemini-2.0-flash-exp, etc.
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
    ],
    'meta' => [
        'api_key' => getenv('META_API_KEY') ?: '',
        'model' => 'llama-3.3-70b-versatile', // Options: llama-3.3-70b-versatile, llama-3.1-405b, llama-3.1-70b, etc.
        'endpoint' => 'https://api.meta.ai/v1/chat/completions',
    ],
    'message_filtering' => [
        'instructions' => [
            'Avoid sharing external links.',
            'Refrain from quoting controversial sources.',
            'Use appropriate language.',
            'Reject harmful or dangerous requests.',
            'De-escalate potential conflicts and calm aggressive or rude users.',
        ],
        'profanities' => ['badword1', 'badword2'],
        'aggression_patterns' => ['hate', 'kill', 'stupid', 'idiot'],
        'link_pattern' => '/https?:\/\/[\w\.-]+/i',
    ],

    // Conversation Memory Settings
    'memory' => [
        // Enable conversation history storage
        'enabled' => true,

        // Maximum number of messages to keep per session (0 = unlimited)
        'max_history' => 20,

        // Storage backend: 'file', 'redis', 'database'
        'storage' => 'file',

        // File storage configuration
        'file_storage' => [
            'path' => function_exists('storage_path')
                ? storage_path('chatbot/conversations')  // Laravel
                : sys_get_temp_dir() . '/chatbot_conversations',  // Non-Laravel
        ],

        // Redis storage configuration (requires Redis extension or predis/predis)
        'redis_storage' => [
            'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
            'port' => getenv('REDIS_PORT') ?: 6379,
            'password' => getenv('REDIS_PASSWORD') ?: null,
            'database' => getenv('REDIS_DB') ?: 0,
            'key_prefix' => 'chatbot:memory:',
            'ttl' => 86400, // Time-to-live in seconds (0 = no expiration)
        ],

        // Database storage configuration (requires PDO)
        'database_storage' => [
            'driver' => getenv('DB_CONNECTION') ?: 'mysql',
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => getenv('DB_PORT') ?: 3306,
            'database' => getenv('DB_DATABASE') ?: 'chatbot',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'table_name' => 'chatbot_conversations',
        ],
    ],
];
