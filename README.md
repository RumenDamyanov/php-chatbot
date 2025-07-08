# php-chatbot

![CI](https://github.com/RumenDamyanov/php-chatbot/actions/workflows/ci.yml/badge.svg)
![Analyze](https://github.com/RumenDamyanov/php-chatbot/actions/workflows/analyze.yml/badge.svg)
![Style](https://github.com/RumenDamyanov/php-chatbot/actions/workflows/style.yml/badge.svg)
[![codecov](https://codecov.io/gh/RumenDamyanov/php-chatbot/branch/master/graph/badge.svg)](https://codecov.io/gh/RumenDamyanov/php-chatbot)

**php-chatbot** is a modern, framework-agnostic PHP package for integrating an AI-powered chat popup into any web application. It features out-of-the-box support for Laravel and Symfony, a flexible model abstraction for using OpenAI, Anthropic, xAI, Google Gemini, Meta, and more, and is designed for easy customization and extension. Build your own UI or use the provided minimal frontend as a starting point. High test coverage, static analysis, and coding standards are included.

## Features

- Plug-and-play chat popup UI (minimal frontend dependencies)
- Laravel & Symfony support via adapters/service providers
- AI model abstraction via contracts (swap models easily)
- Customizable prompts, tone, language, and scope
- Emoji and multi-script support (Cyrillic, Greek, Armenian, Asian, etc.)
- Security safeguards against abuse
- High test coverage (Pest)
- Static analysis (phpstan) & coding standards (phpcs, Symfony style)

## Supported AI Providers & Models

| Provider   | Example Models / Notes                                 | API Key Required | Local/Remote |
|------------|--------------------------------------------------------|------------------|--------------|
| OpenAI     | gpt-4.1, gpt-4o, gpt-4o-mini, gpt-3.5-turbo, etc.      | Yes              | Remote       |
| Anthropic  | Claude 3 Sonnet, 3.7, 4, etc.                          | Yes              | Remote       |
| xAI        | Grok-1, Grok-1.5, etc.                                 | Yes              | Remote       |
| Google     | Gemini 1.5 Pro, Gemini 1.5 Flash, etc.                 | Yes              | Remote       |
| Meta       | Llama 3 (8B, 70B), etc.                                | Yes              | Remote       |
| Ollama     | llama2, mistral, phi3, and any local Ollama model      | No (local) / Opt | Local/Remote |
| Free model | Simple fallback, no API key required                   | No               | Local        |

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

## Example .env

```env
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=...
GEMINI_API_KEY=...
XAI_API_KEY=...
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
# or for coverage
./vendor/bin/pest --coverage --min=90
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

- Input validation and abuse prevention built-in.
- Rate limiting and content filtering available via config.

## Rate Limiting & Abuse Prevention

You can implement rate limiting and abuse prevention in your backend. For Laravel, use built-in middleware:

```php
// routes/web.php
Route::post('/php-chatbot/message', function (Request $request) {
    // ...existing code...
})->middleware('throttle:10,1'); // 10 requests per minute per IP
```

For plain PHP, you can use a simple session or IP-based limiter, or integrate a package like `malkusch/lock` or Redis.

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

## Best Practices

- Never edit files in `vendor/`
- Always copy views/styles to your own app before customizing
- Use environment variables for secrets
- Use the provided JS/TS components as a starting point, not as production code
- Keep your customizations outside the package directory for upgrade safety

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

## Questions & Support

For questions, issues, or feature requests, please use the [GitHub Issues](https://github.com/RumenDamyanov/php-chatbot/issues) page.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## License

MIT. See [LICENSE.md](LICENSE.md).
