# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-10-26

### Security

- Reject Gemini model names that contain path separators before they are placed in the generateContent URL.
- Document that PHP2Shell ([CVE-2026-109618](https://www.cve.org/CVERecord?id=CVE-2026-109618), [CVE-2026-109619](https://www.cve.org/CVERecord?id=CVE-2026-109619)) is in the PHP engine: this package cannot patch it. Run PHP **8.3.36+**, **8.4.27+**, or **8.5.12+** ([PHP Foundation advisory](https://thephp.foundation/blog/2026/10/21/todays-php-security-release-and-how-were-strengthening-php-security/), [CISA alert](https://www.cisa.gov/news-events/alerts/2026/10/21/critical-vulnerabilities-php-require-immediate-patching)).

### Changed

- Require PHP **^8.3**. PHP 8.2 reaches upstream end of life on 31 December 2026; stay on 1.0.x if you must remain on 8.2 (and use **8.2.35+** until then).
- CI tests PHP 8.3, 8.4, and 8.5. Coverage is collected on 8.4 only.
- Default chat models: OpenAI `gpt-5.4-mini`, Anthropic `claude-sonnet-5`, Google `gemini-3.8-flash`, xAI `grok-4.6`. Also priced and documented: `gpt-5.4`, `claude-opus-5`, `claude-haiku-4-5`, `grok-4.7`. Older model ids still work when set explicitly.
- DeepSeek stays on `deepseek-chat`. Meta stays on `llama-3.3-70b-versatile`.

### Added
- **Token & Cost Tracking** - Comprehensive monitoring and cost optimization features
  - New `TokenUsage` class to track prompt/completion/total tokens
  - New `ResponseMetadata` class with detailed response information (model, finish reason, timestamp, etc.)
  - New `ChatResponse` class wrapping AI responses with metadata
  - New `CostCalculator` class with pricing for all major AI providers
  - Token tracking methods in `PhpChatbot`: `getLastTokenUsage()`, `getLastCost()`, `getLastResponse()`
  - Cost estimation before requests: `estimateCost()`
  - Provider comparison and optimization tools
  - Support for budget management and cost monitoring
  - Comprehensive token tracking documentation

### Changed - BREAKING CHANGE ⚠️
- **All AI model classes now return `ChatResponse` instead of `string`**
  - Affected classes: `OpenAiModel`, `AnthropicModel`, `GeminiModel`, `XaiModel`, `MetaModel`, `DeepSeekAiModel`, `OllamaModel`
  - **Backward Compatible**: `ChatResponse` implements `Stringable` interface for automatic string conversion
  - Existing code treating responses as strings continues to work without modification
  - New code can access rich metadata through `ChatResponse` object
  - Example: `$response = $chatbot->ask('Hello'); echo $response;` // Still works!
- **Updated all AI model defaults to latest stable versions** (Closes #9)
  - OpenAI: `gpt-3.5-turbo` → `gpt-5.4-mini`
  - Anthropic: `claude-3-sonnet-20240229` → `claude-sonnet-5`
  - Gemini: `gemini-default` → `gemini-3.8-flash`
  - xAI: `xai-default` → `grok-4.6`
  - Meta: `meta-default` → `llama-3.3-70b-versatile` (placeholder to stable)
  - DeepSeek: `deepseek-chat` (no change - already current)
- **Migration Note**: Users can still specify old model names explicitly in configuration
- Updated `ModelFactory` with all new default values
- Updated configuration file (`src/Config/phpchatbot.php`) with new models and options

### Added
- Chat message filtering middleware for content safety and moderation
- Configurable profanity, aggression, and link filtering
- System instruction injection for AI guidance
- Comprehensive security documentation (SECURITY.md)
- Enhanced contributing guidelines (CONTRIBUTING.md)
- Funding information (FUNDING.md)
- This changelog file (CHANGELOG.md)
- Comprehensive implementation planning documentation in `.ai/` directory
- Model migration documentation and guides

### Changed
- Improved README.md with configuration best practices
- Enhanced documentation structure and organization
- Updated PHPDoc comments with new default model information

### Security
- Added input validation and sanitization features
- Implemented configurable content filtering policies

### Migration Guide
To migrate to the new model defaults:

**If you don't specify a model** (using defaults):
- No action required - you'll automatically use the new stable models
- Expect improved performance and capabilities

**If you explicitly specify a model**:
- Your code continues to work without changes
- Consider updating to latest model versions for better performance
- Check provider documentation for deprecated models

**Example configuration**:
```php
// New default (recommended)
'openai' => [
    'api_key' => getenv('OPENAI_API_KEY'),
    // Uses gpt-5.4-mini by default
],

// Or specify explicitly
'openai' => [
    'api_key' => getenv('OPENAI_API_KEY'),
    'model' => 'gpt-5.4-mini', // or any other supported model
],
```

## [1.0.0] - 2025-01-XX

### Added
- Initial release of php-chatbot package
- Framework-agnostic PHP chat implementation
- Laravel adapter with service provider
- Symfony adapter with bundle support
- Support for multiple AI providers:
  - OpenAI (GPT-4, GPT-3.5, etc.)
  - Anthropic (Claude models)
  - Google Gemini
  - Meta Llama models
  - xAI Grok models
  - Ollama (local models)
  - Default fallback model
- Model factory for easy provider switching
- Configurable chat prompts and behavior
- Frontend components (HTML/CSS/JS)
- Framework-specific components (Vue, React, Angular)
- TypeScript examples
- SCSS support for styling
- Comprehensive test suite with Pest
- Static analysis with PHPStan (level 6)
- PSR-12 coding standards
- High test coverage (90%+)
- Documentation and examples
- CI/CD workflows (GitHub Actions)
- Code quality tools integration

### Features
- Plug-and-play chat popup UI
- AI model abstraction layer
- Customizable prompts and configuration
- Multi-language and emoji support
- Security safeguards and rate limiting
- Framework adapters for easy integration
- Extensible architecture
- Comprehensive documentation

### Requirements
- PHP 8.3 or higher
- Composer for dependency management
- Optional: Laravel 10+ or Symfony 6+

---

## Release Notes Template

For future releases, use this template:

```markdown
## [X.Y.Z] - YYYY-MM-DD

### Added
- New features

### Changed
- Changes in existing functionality

### Deprecated
- Soon-to-be removed features

### Removed
- Now removed features

### Fixed
- Bug fixes

### Security
- Security improvements
```

## Contributing to the Changelog

When contributing to the project:

1. Add your changes to the `[Unreleased]` section
2. Use the appropriate category (Added, Changed, Fixed, etc.)
3. Write clear, concise descriptions
4. Include issue/PR references when applicable
5. Maintainers will move entries to versioned sections during releases

## Legend

- **Added**: New features
- **Changed**: Changes in existing functionality
- **Deprecated**: Soon-to-be removed features
- **Removed**: Now removed features
- **Fixed**: Bug fixes
- **Security**: Security improvements
