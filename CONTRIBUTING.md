# Contributing to php-chatbot

Thank you for considering contributing to php-chatbot! We welcome all kinds of contributions: bug reports, feature requests, code, documentation, and more.

## How to Contribute

- Fork the repository and create your branch from `main`.
- Write clear, concise commit messages.
- Add tests for new features and bug fixes.
- Ensure your code passes all tests and static analysis.
- Follow the Symfony coding standards (use `composer style`).
- Submit a pull request with a clear description of your changes.

## Adding New AI Providers/Models

To add a new AI provider/model:

1. Implement `AiModelInterface` in a new class in `src/Models/`.
2. Add config options for the new provider in `src/Config/phpchatbot.php`.
3. Update `ModelFactory` to support the new model.
4. Add tests for your new model in `tests/Models/`.
5. Document usage in the README.


## Code of Conduct

Please be respectful and considerate in all interactions.

## Reporting Issues

If you find a bug or have a feature request, please open an issue on GitHub.
