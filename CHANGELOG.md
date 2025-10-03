# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed - BREAKING CHANGE ⚠️
- **Updated all AI model defaults to latest stable versions** (Closes #9)
  - OpenAI: `gpt-3.5-turbo` → `gpt-4o-mini` (legacy to current)
  - Anthropic: `claude-3-sonnet-20240229` → `claude-3-5-sonnet-20241022` (deprecated to stable)
  - Gemini: `gemini-default` → `gemini-1.5-flash` (placeholder to stable)
  - xAI: `xai-default` → `grok-2-1212` (placeholder to stable)
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
    // Uses gpt-4o-mini by default
],

// Or specify explicitly
'openai' => [
    'api_key' => getenv('OPENAI_API_KEY'),
    'model' => 'gpt-4o-mini', // or any other supported model
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
