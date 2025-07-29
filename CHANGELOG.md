# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Chat message filtering middleware for content safety and moderation
- Configurable profanity, aggression, and link filtering
- System instruction injection for AI guidance
- Comprehensive security documentation (SECURITY.md)
- Enhanced contributing guidelines (CONTRIBUTING.md)
- Funding information (FUNDING.md)
- This changelog file (CHANGELOG.md)

### Changed
- Improved README.md with configuration best practices
- Enhanced documentation structure and organization

### Security
- Added input validation and sanitization features
- Implemented configurable content filtering policies

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
