# Contributing to php-chatbot

Thank you for your interest in contributing to php-chatbot! We welcome contributions from the community and are pleased to have you join us.

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code:

- **Be respectful**: Treat everyone with respect and kindness
- **Be inclusive**: Welcome newcomers and encourage diverse perspectives
- **Be collaborative**: Work together constructively and professionally
- **Be patient**: Help others learn and grow

## How to Contribute

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include:

- **Clear title**: Summarize the problem in the title
- **Detailed description**: Explain what you expected vs. what actually happened
- **Steps to reproduce**: List the steps to reproduce the behavior
- **Environment**: Include PHP version, framework (Laravel/Symfony), and package version
- **Code samples**: Include relevant code snippets or configuration

### Suggesting Features

Feature requests are welcome! Please:

- Check existing issues for similar requests
- Explain the use case and why it would be beneficial
- Provide examples of how the feature would work
- Consider how it fits with the project's goals

### Development Setup

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/your-username/php-chatbot.git
   cd php-chatbot
   ```
3. **Install dependencies**:
   ```bash
   composer install
   ```
4. **Create a feature branch**:
   ```bash
   git checkout -b feature/your-feature-name
   ```

### Running Tests

Before submitting changes, ensure all tests pass:

```bash
# Run the test suite
composer test

# Run tests with coverage
composer test

# Run static analysis
composer analyze

# Check coding standards
composer style

# Fix coding standards automatically
composer style-fix
```

### Code Standards

We follow strict code quality standards:

- **PSR-12**: PHP coding style standard
- **PHPStan Level 6**: Static analysis for type safety
- **High Test Coverage**: All new code should include tests
- **Documentation**: Public methods and classes must be documented

### Adding New AI Providers/Models

To add a new AI provider/model:

1. Implement `AiModelInterface` in a new class in `src/Models/`.
2. Add config options for the new provider in `src/Config/phpchatbot.php`.
3. Update `ModelFactory` to support the new model.
4. Add tests for your new model in `tests/Models/`.
5. Document usage in the README.

### Writing Tests

We use [Pest](https://pestphp.com/) for testing. When adding new features:

1. **Write tests first** (Test-Driven Development)
2. **Cover edge cases** and error conditions
3. **Use descriptive test names** that explain what is being tested
4. **Mock external dependencies** (API calls, file system, etc.)

Example test structure:

```php
it('filters profanity from user messages', function () {
    $middleware = new ChatMessageFilterMiddleware(
        [], // instructions
        ['badword'], // profanities
        [], // aggression patterns
        '' // link pattern
    );
    
    $result = $middleware->handle('This contains badword content', []);
    
    expect($result['message'])->not->toContain('badword');
});
```

### Submitting Changes

1. **Commit your changes** with clear, descriptive messages:
   ```bash
   git commit -m "Add feature: configurable rate limiting"
   ```

2. **Push to your fork**:
   ```bash
   git push origin feature/your-feature-name
   ```

3. **Create a Pull Request** on GitHub with:
   - Clear title and description
   - Reference to any related issues
   - Screenshots/examples if applicable
   - Confirmation that tests pass

### Pull Request Guidelines

- **One feature per PR**: Keep changes focused and atomic
- **Update documentation**: Include relevant documentation updates
- **Backward compatibility**: Avoid breaking changes when possible
- **Performance**: Consider the performance impact of changes
- **Security**: Be mindful of security implications

### Commit Message Format

Use clear, descriptive commit messages:

```
type(scope): description

Examples:
feat(models): add support for Gemini Pro model
fix(middleware): handle empty message filtering rules
docs(readme): update configuration examples
test(models): add edge case tests for rate limiting
```

Types: `feat`, `fix`, `docs`, `test`, `refactor`, `style`, `chore`

### Documentation

- Update relevant documentation for any changes
- Include code examples for new features
- Update the CHANGELOG.md for notable changes
- Ensure README.md stays current

## Getting Help

- **Issues**: For bugs and feature requests
- **Discussions**: For questions and general discussion
- **Email**: contact@rumenx.com for private inquiries

## Recognition

Contributors are recognized in:

- CHANGELOG.md for significant contributions
- GitHub contributors page
- Release notes for major features

## Development Philosophy

php-chatbot aims to be:

- **Framework-agnostic**: Works with any PHP framework
- **Secure by default**: Built-in security features
- **Easy to extend**: Clean architecture and interfaces
- **Well-tested**: High test coverage and quality
- **Performance-focused**: Efficient and scalable

Thank you for contributing to php-chatbot! 🚀
