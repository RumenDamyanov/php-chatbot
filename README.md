# php-chatbot

[![CI](https://github.com/RumenDamyanov/php-chatbot/actions/workflows/ci.yml/badge.svg)](https://github.com/RumenDamyanov/php-chatbot/actions/workflows/ci.yml)
[![Analyze](https://github.com/RumenDamyanov/php-chatbot/actions/workflows/analyze.yml/badge.svg)](https://github.com/RumenDamyanov/php-chatbot/actions/workflows/analyze.yml)
[![Style](https://github.com/RumenDamyanov/php-chatbot/actions/workflows/style.yml/badge.svg)](https://github.com/RumenDamyanov/php-chatbot/actions/workflows/style.yml)
[![CodeQL](https://github.com/RumenDamyanov/php-chatbot/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/RumenDamyanov/php-chatbot/actions/workflows/github-code-scanning/codeql)
[![Dependabot](https://github.com/RumenDamyanov/php-chatbot/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/RumenDamyanov/php-chatbot/actions/workflows/dependabot/dependabot-updates)
[![codecov](https://codecov.io/gh/RumenDamyanov/php-chatbot/branch/master/graph/badge.svg)](https://codecov.io/gh/RumenDamyanov/php-chatbot)


> 📖 **Documentation**: [Contributing](CONTRIBUTING.md) · [Security](SECURITY.md) · [Changelog](CHANGELOG.md) · [Funding](FUNDING.md)

**php-chatbot** is a modern, framework-agnostic PHP package for integrating an AI-powered chat popup into any web application. It features out-of-the-box support for Laravel and Symfony, a flexible model abstraction for using OpenAI, Anthropic, xAI, Google Gemini, Meta, and more, and is designed for easy customization and extension. Build your own UI or use the provided minimal frontend as a starting point. High test coverage, static analysis, and coding standards are included.

## 📦 Part of the Chatbot Family

This is the PHP implementation of our multi-language chatbot library:

- 🐘 **[php-chatbot](https://github.com/RumenDamyanov/php-chatbot)** - PHP implementation (this package)
- 📘 **[npm-chatbot](https://github.com/RumenDamyanov/npm-chatbot)** - TypeScript/JavaScript implementation
- 🔷 **[go-chatbot](https://github.com/RumenDamyanov/go-chatbot)** - Go implementation

All implementations share the same API design and features, making it easy to switch between languages or maintain consistency across polyglot projects.

## 🔗 Recommended Projects

If you find **php-chatbot** useful, you might also be interested in these related projects:

- 🔍 **[php-seo](https://github.com/RumenDamyanov/php-seo)** - Comprehensive SEO toolkit for PHP applications
- 🗺️ **[php-sitemap](https://github.com/RumenDamyanov/php-sitemap)** - Dynamic XML sitemap generator for PHP
- 🌍 **[php-geolocation](https://github.com/RumenDamyanov/php-geolocation)** - IP geolocation and geographic data tools

## Features

- Plug-and-play chat popup UI (minimal frontend dependencies)
- **Token & Cost Tracking** - Monitor API usage and optimize costs with detailed token analytics
- **Conversation Memory** - Persistent context across messages with file/Redis/database storage
- **Streaming Responses** - Real-time token-by-token output for better UX
- **Rate Limiting** - Built-in request throttling with memory and Redis backends
- **Response Caching** - Intelligent caching to reduce API costs and improve performance
- **Health Monitoring** - Comprehensive health checks for models, storage, and cache
- **Exception Hierarchy** - Robust error handling with specific exception types
- Laravel & Symfony support via adapters/service providers
- AI model abstraction via contracts (swap models easily)
- Customizable prompts, tone, language, and scope
- Emoji and multi-script support (Cyrillic, Greek, Armenian, Asian, etc.)
- Security safeguards against abuse
- High test coverage (80%+, 540+ tests with Pest)
- Static analysis (PHPStan Level MAX) & coding standards (PSR-12, Symfony style)

## 📚 Documentation & Wiki

Comprehensive documentation and guides are available in our [GitHub Wiki](https://github.com/RumenDamyanov/php-chatbot/wiki):

### 🚀 Getting Started
- **[Installation Guide](https://github.com/RumenDamyanov/php-chatbot/wiki/Installation-Guide)** - Step-by-step installation for all environments
- **[Quick Start Guide](https://github.com/RumenDamyanov/php-chatbot/wiki/Quick-Start-Guide)** - Get up and running in minutes
- **[Configuration](https://github.com/RumenDamyanov/php-chatbot/wiki/Configuration)** - Complete configuration reference

### 🔧 Implementation Guides
- **[Framework Integration](https://github.com/RumenDamyanov/php-chatbot/wiki/Framework-Integration)** - Laravel, Symfony, and plain PHP setup
- **[Frontend Integration](https://github.com/RumenDamyanov/php-chatbot/wiki/Frontend-Integration)** - React, Vue, Angular components and examples
- **[AI Models](https://github.com/RumenDamyanov/php-chatbot/wiki/AI-Models)** - Provider comparison and configuration
- **[Token & Cost Tracking](https://github.com/RumenDamyanov/php-chatbot/wiki/Token-Tracking)** - Monitor API usage, track costs, and optimize spending
- **[Conversation Memory](https://github.com/RumenDamyanov/php-chatbot/wiki/Conversation-Memory)** - Persistent context with file/Redis/database storage
- **[Rate Limiting](https://github.com/RumenDamyanov/php-chatbot/wiki/Rate-Limiting)** - Built-in request throttling and abuse prevention
- **[Response Caching](https://github.com/RumenDamyanov/php-chatbot/wiki/Response-Caching)** - Smart caching to reduce API costs
- **[Health Monitoring](https://github.com/RumenDamyanov/php-chatbot/wiki/Health-Monitoring)** - System health checks and monitoring

### 📖 Examples & Best Practices
- **[Examples](https://github.com/RumenDamyanov/php-chatbot/wiki/Examples)** - Real-world implementations and use cases
- **[Best Practices](https://github.com/RumenDamyanov/php-chatbot/wiki/Best-Practices)** - Production deployment and security guidelines
- **[Security & Filtering](https://github.com/RumenDamyanov/php-chatbot/wiki/Security-and-Filtering)** - Content filtering and abuse prevention
- **[Streaming Responses](https://github.com/RumenDamyanov/php-chatbot/wiki/Streaming-Responses)** - How to use streaming responses

### 🛠️ Development & Support
- **[API Reference](https://github.com/RumenDamyanov/php-chatbot/wiki/API-Reference)** - Complete API documentation
- **[Troubleshooting](https://github.com/RumenDamyanov/php-chatbot/wiki/Troubleshooting)** - Common issues and solutions
- **[Contributing](https://github.com/RumenDamyanov/php-chatbot/wiki/Contributing)** - How to contribute to the project
- **[FAQ](https://github.com/RumenDamyanov/php-chatbot/wiki/FAQ)** - Frequently asked questions

> 💡 **Tip**: The wiki contains production-ready examples, troubleshooting guides, and comprehensive API documentation that goes beyond this README.

## Supported AI Providers & Models

| Provider   | Example Models / Notes                                 | API Key Required | Local/Remote | Streaming |
|------------|--------------------------------------------------------|------------------|--------------|-----------|
| OpenAI     | gpt-4.1, gpt-4o, gpt-4o-mini, gpt-3.5-turbo, etc.      | Yes              | Remote       | ✅        |
| Anthropic  | Claude 3 Sonnet, 3.7, 4, etc.                          | Yes              | Remote       | ✅        |
| xAI        | Grok-1, Grok-1.5, etc.                                 | Yes              | Remote       | ✅        |
| Google     | Gemini 1.5 Pro, Gemini 1.5 Flash, etc.                 | Yes              | Remote       | ✅        |
| Meta       | Llama 3 (8B, 70B), etc.                                | Yes              | Remote       | ✅        |
| DeepSeek   | DeepSeek Chat, DeepSeek Coder, etc.                    | Yes              | Remote       | ✅        |
| Ollama     | llama2, mistral, phi3, and any local Ollama model      | No (local) / Opt | Local/Remote | ✅        |
| Free model | Simple fallback, no API key required                   | No               | Local        | ❌        |

## Installation

```bash
composer require rumenx/php-chatbot
```

### Laravel

- Package auto-discovers the service provider.
- Publish config: `php artisan vendor:publish --provider="Rumenx\\PhpChatbot\\Adapters\\Laravel\\PhpChatbotServiceProvider"`

### Symfony

- Register the bundle in your `bundles.php`.
- Publish config: `php bin/console chatbot:publish-config`

## Usage

1. Add the chat popup to your layout (see `/resources/views` for examples).
2. Configure your preferred AI model and prompts in the config file (`src/Config/phpchatbot.php`).
3. Optionally, customize the frontend (CSS/JS) in `/resources`.

### API Keys & Credentials

**Never hardcode API keys or secrets in your codebase.**

- Use environment variables (e.g. `.env` file) or your infrastructure's secret management.
- The config file will check for environment variables (e.g. `OPENAI_API_KEY`, `ANTHROPIC_API_KEY`, etc.) first.
- See `.env.example` for reference.

## Quick Start

```php
use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Models\ModelFactory;

$config = require 'src/Config/phpchatbot.php';
$model = ModelFactory::make($config);
$chatbot = new PhpChatbot($model, $config);

$reply = $chatbot->ask('Hello!');
echo $reply;
```

### Token & Cost Tracking

Monitor API usage and optimize costs with built-in token tracking:

```php
use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Models\OpenAiModel;

$model = new OpenAiModel('your-api-key', 'gpt-4o');
$chatbot = new PhpChatbot($model);

// Make a request
$response = $chatbot->ask('Explain quantum computing');
echo $response;

// Track token usage
$usage = $chatbot->getLastTokenUsage();
echo "Tokens used: {$usage->totalTokens}\n";

// Calculate cost
$cost = $chatbot->getLastCost();
echo "Cost: $" . number_format($cost, 4) . "\n";

// Estimate cost before making a request
$estimatedCost = $chatbot->estimateCost('This is my prompt');
echo "Estimated: $" . number_format($estimatedCost, 6) . "\n";
```

See the **[Token & Cost Tracking Guide](https://github.com/RumenDamyanov/php-chatbot/wiki/Token-Tracking)** for budget management, cost optimization tips, and provider comparison.

## Streaming Responses

**php-chatbot** now supports streaming responses for real-time chat experiences! Streaming allows you to receive and display responses as they are generated, creating a more interactive user experience.

### Supported Models

The following AI providers support streaming:

- ✅ OpenAI (all models)
- ✅ Anthropic Claude (all models)
- ✅ Google Gemini (all models)
- ✅ xAI Grok (all models)
- ✅ Meta LLaMA (all models)
- ✅ DeepSeek (all models)
- ✅ Ollama (all models)

### Basic Streaming Example

```php
use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Models\OpenAiModel;

$model = new OpenAiModel('your-api-key');
$chatbot = new PhpChatbot($model);

// Get streaming response
foreach ($chatbot->askStream('Hello!') as $chunk) {
    echo $chunk;
    flush(); // Send to browser immediately
}
```

### Backend Streaming API Example

For Server-Sent Events (SSE) streaming to frontend:

```php
// Set headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no'); // Disable nginx buffering

$model = ModelFactory::make($config);
$chatbot = new PhpChatbot($model, $config);

// Stream chunks to client
foreach ($chatbot->askStream($message, $context) as $chunk) {
    echo "data: " . json_encode(['chunk' => $chunk]) . "\n\n";
    flush();
}

echo "data: [DONE]\n\n";
flush();
```

### Frontend Integration (JavaScript)

```javascript
const eventSource = new EventSource('/api/chatbot/stream?message=' + encodeURIComponent(message));

eventSource.onmessage = function(event) {
    if (event.data === '[DONE]') {
        eventSource.close();
        return;
    }
    
    const data = JSON.parse(event.data);
    // Append chunk to chat UI
    chatMessageElement.textContent += data.chunk;
};

eventSource.onerror = function() {
    eventSource.close();
};
```

### Checking Streaming Support

```php
use Rumenx\PhpChatbot\Contracts\StreamableModelInterface;

if ($model instanceof StreamableModelInterface && $model->supportsStreaming()) {
    // Use streaming
    foreach ($chatbot->askStream($message) as $chunk) {
        // Process chunk...
    }
} else {
    // Fallback to regular response
    $reply = $chatbot->ask($message);
}
```

> 💡 **Note**: Not all models support streaming. The `DefaultAiModel` (fallback) does not support streaming. Always check if your model implements `StreamableModelInterface` before using `askStream()`.

## Rate Limiting

**php-chatbot** includes built-in rate limiting to prevent API abuse and control costs. Configure rate limits per user, IP, or session.

### Supported Backends

- ✅ **Memory** - In-memory rate limiting (default, single-server)
- ✅ **Redis** - Distributed rate limiting (multi-server, production-ready)

### Basic Usage

```php
use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Models\OpenAiModel;

$config = require 'src/Config/phpchatbot.php';
$config['rate_limiting'] = [
    'enabled' => true,
    'driver' => 'memory', // or 'redis'
    'limits' => [
        'requests' => 10,    // 10 requests
        'window' => 60,      // per 60 seconds
    ],
];

$model = new OpenAiModel('your-api-key');
$chatbot = new PhpChatbot($model, $config);

try {
    $reply = $chatbot->ask('Hello!');
} catch (\Rumenx\PhpChatbot\RateLimiting\RateLimitException $e) {
    // Rate limit exceeded
    echo "Too many requests. Try again in {$e->getRetryAfter()} seconds.";
}
```

### Redis Configuration

```php
$config['rate_limiting'] = [
    'enabled' => true,
    'driver' => 'redis',
    'limits' => [
        'requests' => 100,
        'window' => 3600,  // 100 requests per hour
    ],
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 0,
    ],
];
```

> 💡 **Tip**: Use Redis for production deployments with multiple servers. Memory backend is perfect for single-server setups.

## Response Caching

Reduce API costs and improve response times with intelligent response caching.

### Supported Backends

- ✅ **Memory** - In-memory cache (fast, single request)
- ✅ **File** - File-based cache (persistent, single-server)
- ✅ **Redis** - Distributed cache (multi-server, recommended for production)

### Basic Usage

```php
$config['cache'] = [
    'enabled' => true,
    'driver' => 'file',  // 'memory', 'file', or 'redis'
    'ttl' => 3600,       // Cache for 1 hour
    'path' => '/tmp/chatbot-cache',  // for file driver
];

$chatbot = new PhpChatbot($model, $config);

// First call: hits API
$reply1 = $chatbot->ask('What is PHP?');

// Second call: returns cached response (no API call)
$reply2 = $chatbot->ask('What is PHP?');
```

### Cache Key Generation

Cache keys are automatically generated based on:
- User message (normalized)
- Model name and configuration
- System prompt
- Conversation context

### Redis Cache Configuration

```php
$config['cache'] = [
    'enabled' => true,
    'driver' => 'redis',
    'ttl' => 7200,  // 2 hours
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 1,
    ],
];
```

> 💡 **Tip**: Enable caching for FAQ-style chatbots or when users ask similar questions frequently.

## Health Monitoring

Monitor the health of your chatbot infrastructure with built-in health checks.

### Supported Health Checks

- ✅ **Model Health** - AI provider availability and response time
- ✅ **Storage Health** - Conversation memory storage (file/Redis/database)
- ✅ **Cache Health** - Caching system availability

### Basic Usage

```php
use Rumenx\PhpChatbot\Health\HealthMonitor;
use Rumenx\PhpChatbot\Health\ModelHealthChecker;
use Rumenx\PhpChatbot\Health\StorageHealthChecker;
use Rumenx\PhpChatbot\Health\CacheHealthChecker;

$model = new OpenAiModel('your-api-key');
$storage = new FileStorage('/tmp/chatbot-memory');
$cache = new MemoryCache();

$monitor = new HealthMonitor();
$monitor->registerChecker('model', new ModelHealthChecker($model));
$monitor->registerChecker('storage', new StorageHealthChecker($storage));
$monitor->registerChecker('cache', new CacheHealthChecker($cache));

// Check overall health
$results = $monitor->checkAll();
$overallHealth = $monitor->getOverallHealth($results);

echo "System Health: {$overallHealth->value}\n";

foreach ($results as $name => $result) {
    echo "{$name}: {$result->status->value} ";
    echo "({$result->metrics['response_time']}ms)\n";
    
    if ($result->message) {
        echo "  → {$result->message}\n";
    }
}
```

### Health Status Levels

- 🟢 **HEALTHY** - All systems operational
- 🟡 **DEGRADED** - System working but with issues
- 🔴 **UNHEALTHY** - System not functional

### Integration with Monitoring Tools

```php
// HTTP endpoint for health checks
Route::get('/health/chatbot', function () {
    $results = $healthMonitor->checkAll();
    $status = $healthMonitor->getOverallHealth($results);
    
    return response()->json([
        'status' => $status->value,
        'checks' => array_map(fn($r) => [
            'status' => $r->status->value,
            'message' => $r->message,
            'metrics' => $r->metrics,
        ], $results),
    ], $status === HealthStatus::HEALTHY ? 200 : 503);
});
```

> 💡 **Tip**: Integrate with tools like Datadog, New Relic, or Prometheus for comprehensive monitoring.

## Exception Handling

**php-chatbot** provides a comprehensive exception hierarchy for precise error handling and debugging.

### Exception Types

```php
use Rumenx\PhpChatbot\Exceptions\PhpChatbotException;      // Base exception
use Rumenx\PhpChatbot\Exceptions\ApiException;            // API-related errors
use Rumenx\PhpChatbot\Exceptions\NetworkException;        // Network/connectivity errors
use Rumenx\PhpChatbot\Exceptions\ModelException;          // Model-specific errors
use Rumenx\PhpChatbot\Exceptions\InvalidConfigException;  // Configuration errors
use Rumenx\PhpChatbot\Exceptions\MemoryException;         // Memory storage errors
use Rumenx\PhpChatbot\RateLimiting\RateLimitException;    // Rate limit errors
```

### Exception Handling Example

```php
use Rumenx\PhpChatbot\Exceptions\ApiException;
use Rumenx\PhpChatbot\Exceptions\NetworkException;
use Rumenx\PhpChatbot\Exceptions\RateLimitException;

try {
    $reply = $chatbot->ask($message);
    echo $reply;
} catch (RateLimitException $e) {
    // User exceeded rate limit
    http_response_code(429);
    echo json_encode([
        'error' => 'Too many requests',
        'retry_after' => $e->getRetryAfter(),
    ]);
} catch (ApiException $e) {
    // API error (invalid key, quota exceeded, etc.)
    http_response_code(502);
    echo json_encode([
        'error' => 'AI service error',
        'details' => $e->getMessage(),
        'status_code' => $e->getStatusCode(),
    ]);
} catch (NetworkException $e) {
    // Network/connectivity error
    http_response_code(503);
    echo json_encode([
        'error' => 'Service temporarily unavailable',
        'details' => $e->getMessage(),
    ]);
} catch (\Exception $e) {
    // Generic error
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
```

### Disable Exceptions (Return Error Strings)

For backward compatibility, you can disable exception throwing:

```php
$config['throw_exceptions'] = false;

$chatbot = new PhpChatbot($model, $config);
$reply = $chatbot->ask($message);

// Errors will be returned as strings instead of throwing exceptions
if (str_starts_with($reply, 'Error:')) {
    // Handle error
}
```

> 💡 **Tip**: Keep `throw_exceptions` enabled (default) for production. Use specific catch blocks for granular error handling.

## Example .env

```env
# AI Provider API Keys
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=...
GEMINI_API_KEY=...
XAI_API_KEY=...
META_API_KEY=...
DEEPSEEK_API_KEY=...

# Rate Limiting (optional)
RATE_LIMIT_ENABLED=true
RATE_LIMIT_DRIVER=memory  # or 'redis'
RATE_LIMIT_REQUESTS=10
RATE_LIMIT_WINDOW=60

# Caching (optional)
CACHE_ENABLED=true
CACHE_DRIVER=file  # 'memory', 'file', or 'redis'
CACHE_TTL=3600
CACHE_PATH=/tmp/chatbot-cache

# Redis Configuration (if using Redis for rate limiting or caching)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DATABASE=0

# Exception Handling (optional, default: true)
THROW_EXCEPTIONS=true
```

## API Endpoint Contract

- **Endpoint:** `/php-chatbot/message`
- **Request:**

```json
{ "message": "Hello" }
```

- **Response:**

```json
{ "reply": "Hi! How can I help you?" }
```

## Symfony Backend Example

```php
// src/Controller/ChatbotController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Models\ModelFactory;

class ChatbotController extends AbstractController
{
    public function message(Request $request): JsonResponse
    {
        $config = require $this->getParameter('kernel.project_dir').'/src/Config/phpchatbot.php';
        $model = ModelFactory::make($config);
        $chatbot = new PhpChatbot($model, $config);
        $input = $request->toArray()['message'] ?? '';
        $reply = $chatbot->ask($input);
        return $this->json(['reply' => $reply]);
    }
}
```

## Testing

```bash
composer test
```

## Running Tests with Coverage

```sh
composer test
# or for coverage with minimum threshold
./vendor/bin/pest --coverage --min=80
# current coverage: 80.6% ✅
```

## Static Analysis

```bash
composer analyze
```

## Coding Standards

```bash
composer style
```

## Security

- Input validation and abuse prevention built-in
- Built-in rate limiting with memory and Redis backends
- Content filtering middleware available via config
- Comprehensive exception handling for security monitoring
- Configurable request throttling per user/IP/session

## Rate Limiting & Abuse Prevention

**php-chatbot** includes built-in rate limiting (see [Rate Limiting](#rate-limiting) section above). For additional framework-level protection, you can combine it with Laravel/Symfony middleware:

### Laravel Example

```php
// routes/web.php
Route::post('/php-chatbot/message', function (Request $request) {
    // ...existing code...
})->middleware('throttle:60,1'); // Framework-level: 60 requests per minute per IP
```

### Combined Protection (Recommended)

```php
// Framework middleware: Protects against DDoS (IP-based)
Route::middleware('throttle:60,1')->group(function () {
    // Built-in rate limiter: Protects against API abuse (user/session-based)
    $config['rate_limiting'] = [
        'enabled' => true,
        'limits' => ['requests' => 10, 'window' => 60], // 10 AI calls per minute
    ];
});
```

> 💡 **Best Practice**: Use framework middleware for broad DDoS protection and built-in rate limiting for fine-grained API cost control.

## JavaScript Framework Components

You can use the provided chat popup as a plain HTML/JS snippet, or integrate a modern component for Vue, React, or Angular:

- **Vue**: `resources/js/components/PhpChatbotVue.vue`
- **React**: `resources/js/components/PhpChatbotReact.jsx`
- **Angular**: `resources/js/components/PhpChatbotAngular.html` (with TypeScript logic)

### JS Component Usage

1. **Copy the component** into your app's source tree.
2. **Import and register** it in your app (see your framework's docs).
3. **Customize the backend endpoint** (`/php-chatbot/message`) as needed.
4. **Build your assets** (e.g. with Vite, Webpack, or your framework's CLI).

#### Example (Vue)

```js
// main.js
import PhpChatbotVue from './components/PhpChatbotVue.vue';
app.component('PhpChatbotVue', PhpChatbotVue);
```

#### Example (React)

```js
import PhpChatbotReact from './components/PhpChatbotReact.jsx';
<PhpChatbotReact />
```

#### Example (Angular)

- Copy the HTML, TypeScript, and CSS into your Angular component files.
- Register and use `<php-chatbot-angular></php-chatbot-angular>` in your template.

### JS Component Customization

- You can style or extend the components as needed.
- You may add your own framework component in a similar way.
- The backend endpoint is framework-agnostic; you can point it to any PHP route/controller.

## TypeScript Example (React)

A modern TypeScript React chatbot component is provided as an example in `resources/js/components/PhpChatbotTs.tsx`.

**How to use:**

1. Copy `PhpChatbotTs.tsx` into your React app's components folder.
2. Import and use it in your app:

   ```tsx
   import PhpChatbotTs from './components/PhpChatbotTs';
   // ...
   <PhpChatbotTs />
   ```

3. Make sure your backend endpoint `/php-chatbot/message` is set up as described above.
4. Style as needed (the component uses inline styles for demo purposes, but you can use your own CSS/SCSS).

> This component is a minimal, framework-agnostic starting point. You are encouraged to extend or restyle it to fit your app.

## Backend Integration Example

To handle chat requests, add a route/controller in your backend (Laravel, Symfony, or plain PHP) that receives POST requests at `/php-chatbot/message` and returns a JSON response. Example for Laravel:

```php
// routes/web.php
use Illuminate\Http\Request;
use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Models\ModelFactory;
use Illuminate\Support\Facades\Log;

Route::post('/php-chatbot/message', function (Request $request) {
    $config = config('phpchatbot');
    $model = ModelFactory::make($config);
    $chatbot = new PhpChatbot($model, $config);
    $context = [
        'prompt' => $config['prompt'],
        'logger' => Log::getFacadeRoot(), // Optional PSR-3 logger
    ];
    $reply = $chatbot->ask($request->input('message'), $context);
    return response()->json(['reply' => $reply]);
});
```

For plain PHP, use:

```php
// public/php-chatbot-message.php
require '../vendor/autoload.php';
use Rumenx\PhpChatbot\PhpChatbot;
use Rumenx\PhpChatbot\Models\ModelFactory;
$config = require '../src/Config/phpchatbot.php';
$model = ModelFactory::make($config);
$chatbot = new PhpChatbot($model, $config);
$input = json_decode(file_get_contents('php://input'), true)['message'] ?? '';
$reply = $chatbot->ask($input);
header('Content-Type: application/json');
echo json_encode(['reply' => $reply]);
```

## Frontend Styles (SCSS)

The chat popup styles are written in modern SCSS for maintainability. You can find the source in `resources/css/chatbot.scss`.

To compile SCSS to CSS:

1. **Install Sass** (if you haven't already):

   ```sh
   npm install --save-dev sass
   ```

2. **Add this script** to your `package.json`:

   ```json
   "scripts": {
     "scss": "sass resources/css/chatbot.scss resources/css/chatbot.css --no-source-map"
   }
   ```

3. **Compile SCSS**:

   ```sh
   npm run scss
   ```

Or use the Sass CLI directly:

```sh
sass resources/css/chatbot.scss resources/css/chatbot.css --no-source-map
```

To watch for changes automatically:

```sh
sass --watch resources/css/chatbot.scss:resources/css/chatbot.css --no-source-map
```

> Only commit the compiled `chatbot.css` for production/deployment. For more options, see [Sass documentation](https://sass-lang.com/documentation/cli/dart-sass).

## Customizing Frontend Styles & Views (Best Practice)

The provided CSS/SCSS and view files (`resources/css/`, `resources/views/`) are **optional and basic**. They are meant as a starting point for your own implementation. **You are encouraged to build your own UI and styles on top of or instead of these defaults.**

**Do not edit files directly in the `vendor/` folder or inside the package source.**

### How to Safely Override Views and Styles

- **Copy the provided view files** (e.g. `resources/views/popup.blade.php` or `popup.php`) into your own application's views directory. Update your app to use your custom view.
- **Copy and modify the SCSS/CSS** (`resources/css/chatbot.scss` or `chatbot.css`) into your own asset pipeline. Import, extend, or replace as needed.
- **For Laravel/Symfony:**
  - Publish the package views/config (see the Installation section above) and edit the published files in your app, not in `vendor/`.
  - Example (Laravel):

    ```sh
    php artisan vendor:publish --provider="Rumenx\PhpChatbot\Adapters\Laravel\PhpChatbotServiceProvider" --tag=views
    ```

  - Example (Symfony):

    ```sh
    php bin/console chatbot:publish-views
    ```

- **For plain PHP:** Copy the view and style files to your public or template directory and reference them in your HTML.

> **Important:** If you edit files directly in `vendor/rumenx/php-chatbot/`, your changes will be lost on the next `composer update`. Always override or extend in your own app.

### Principle

- **Treat the package's frontend as a reference implementation.**
- **Override or extend in your own codebase for all customizations.**
- **Never edit vendor files directly.**

## Configuration Best Practices

**Never edit files in the `vendor/` directory.**

- For Laravel: Publish the config file to your app's `config/` directory using:
  ```sh
  php artisan vendor:publish --provider="Rumenx\PhpChatbot\Adapters\Laravel\PhpChatbotServiceProvider"
  ```
  Then edit `config/phpchatbot.php` in your app. This file will not be overwritten by package updates.

- For Symfony: Use the provided command to publish config to your app's config directory, then edit as needed.

- For plain PHP or other frameworks: **Use an environment variable to point to your own config file.**

  1. Copy `src/Config/phpchatbot.php` to a safe location in your project (e.g., `config/phpchatbot.php`).
  2. Set the environment variable in your `.env` or server config:
     ```env
     PHPCHATBOT_CONFIG_PATH=/path/to/your/config/phpchatbot.php
     ```
  3. In your bootstrap or controller, load config like this:
     ```php
     $configPath = getenv('PHPCHATBOT_CONFIG_PATH') ?: __DIR__ . '/../vendor/rumenx/php-chatbot/src/Config/phpchatbot.php';
     $config = require $configPath;
     ```

- **Always use environment variables for secrets and API keys.**
- **Never edit or commit changes to files in `vendor/`.**
- **Document your custom config location for your team.**

**Example usage in a plain PHP project:**

```php
$configPath = getenv('PHPCHATBOT_CONFIG_PATH') ?: __DIR__ . '/../vendor/rumenx/php-chatbot/src/Config/phpchatbot.php';
$config = require $configPath;
$model = ModelFactory::make($config);
$chatbot = new PhpChatbot($model, $config);
```

> This approach ensures your configuration is safe from being overwritten during package updates and is easy to manage in any deployment environment.

## Best Practices

- Never edit files in `vendor/`
- Always copy views/styles to your own app before customizing
- Use environment variables for secrets
- Use the provided JS/TS components as a starting point, not as production code
- Keep your customizations outside the package directory for upgrade safety

## Quality & Standards

**php-chatbot** is built to production-grade standards:

### ✅ Code Quality
- **Strict Types**: `declare(strict_types=1)` in all PHP files for maximum type safety
- **PSR-12**: Follows PSR-12 coding standards (Symfony style)
- **PHPStan Level MAX**: Static analysis at the highest level (9) with zero errors
- **PHPCS**: Automated code style checking and enforcement

### ✅ Testing
- **540+ Tests**: Comprehensive test suite using Pest PHP
- **80.6% Coverage**: Production-grade code coverage
- **CI/CD**: Automated testing on GitHub Actions (multiple PHP versions)
- **Edge Cases**: Extensive testing of error conditions and edge cases

### ✅ Architecture
- **SOLID Principles**: Clean, maintainable, and extensible code
- **Interface-Driven**: Model abstraction via contracts
- **Factory Pattern**: Easy model instantiation and configuration
- **Dependency Injection**: Testable and flexible design

### ✅ Production-Ready
- **Error Handling**: Comprehensive exception hierarchy
- **Rate Limiting**: Built-in request throttling
- **Response Caching**: Intelligent caching to reduce costs
- **Health Monitoring**: System health checks and observability
- **Logging**: PSR-3 logger support for debugging and monitoring

> 💡 **Philosophy**: Simple to start, powerful in production. No compromises on code quality.

## What's Included

| Feature                | Location/Example                                 | Optional/Required |
|------------------------|--------------------------------------------------|-------------------|
| PHP Core Classes       | `src/`                                           | Required          |
| Laravel Adapter        | `src/Adapters/Laravel/`                          | Optional          |
| Symfony Adapter        | `src/Adapters/Symfony/`                          | Optional          |
| Config File            | `src/Config/phpchatbot.php`                      | Required          |
| Blade/PHP Views        | `resources/views/`                               | Optional          |
| CSS/SCSS               | `resources/css/chatbot.scss`/`chatbot.css`       | Optional          |
| JS/TS Components       | `resources/js/components/`                       | Optional          |
| Example .env           | `.env.example`                                   | Optional          |
| Tests                  | `tests/`                                         | Optional          |

## Chat Message Filtering Middleware

The package includes a configurable chat message filtering middleware to help ensure safe, appropriate, and guideline-aligned AI responses. This middleware:

- Filters and optionally rephrases user-submitted messages before they reach the AI model.
- Appends hidden system instructions (not visible in chat history) to the AI context, enforcing safety and communication guidelines.
- All filtering rules (profanities, aggression patterns, link regex) and system instructions are fully configurable in `src/Config/phpchatbot.php`.

**Example configuration:**

```php
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
```

**Example usage in your controller or chat service:**

```php
use Rumenx\PhpChatbot\Middleware\ChatMessageFilterMiddleware;

$config = require 'src/Config/phpchatbot.php';
$filterCfg = $config['message_filtering'] ?? [];
$middleware = new ChatMessageFilterMiddleware(
    $filterCfg['instructions'] ?? [],
    $filterCfg['profanities'] ?? [],
    $filterCfg['aggression_patterns'] ?? [],
    $filterCfg['link_pattern'] ?? ''
);

// Before sending to the AI model:
$filtered = $middleware->handle($userMessage, $context);
$reply = $chatbot->ask($filtered['message'], $filtered['context']);
```

**Purpose:**
- Promotes safe, respectful, and effective communication.
- Prevents misuse, abuse, and unsafe outputs.
- All rules are transparent and configurable—no hidden censorship or manipulation.

## Questions & Support

For questions, issues, or feature requests, please use the [GitHub Issues](https://github.com/RumenDamyanov/php-chatbot/issues) page.

For security vulnerabilities, please see our [Security Policy](SECURITY.md).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on how to contribute.

## Code of Conduct

This project adheres to a Code of Conduct to ensure a welcoming and inclusive environment for all contributors. See [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) for details.

## Support the Project

If you find this project helpful, consider supporting its development. See [FUNDING.md](FUNDING.md) for ways to contribute.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed history of changes and releases.

## License

MIT. See [LICENSE.md](LICENSE.md).
