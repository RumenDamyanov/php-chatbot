# Security Policy

## Supported Versions

We actively support the following versions of php-chatbot:

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

The php-chatbot project takes security bugs seriously. We appreciate your efforts to responsibly disclose your findings.

### How to Report a Security Vulnerability

**Please do NOT report security vulnerabilities through public GitHub issues.**

Instead, please report them via one of the following methods:

1. **Email**: Send details to `security@rumenx.com`
2. **GitHub Security Advisories**: Use the [GitHub Security Advisories](https://github.com/RumenDamyanov/php-chatbot/security/advisories) feature

### What to Include

When reporting a security vulnerability, please include:

- Type of issue (e.g. buffer overflow, SQL injection, cross-site scripting, etc.)
- Full paths of source file(s) related to the manifestation of the issue
- The location of the affected source code (tag/branch/commit or direct URL)
- Any special configuration required to reproduce the issue
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue, including how an attacker might exploit the issue

### Response Timeline

We will respond to security vulnerability reports within **48 hours**.

After the initial reply to your report, we will:

1. Confirm the problem and determine the affected versions
2. Audit code to find any potential similar problems
3. Prepare fixes for all supported versions
4. Release security patches as soon as possible

### Responsible Disclosure

We ask that you:

- Give us reasonable time to address the issue before making any information public
- Make a good faith effort to avoid privacy violations, destruction of data, and interruption or degradation of our service
- Only interact with accounts you own or with explicit permission of the account holder

### Security Best Practices

When using php-chatbot:

1. **API Keys**: Store API keys securely using environment variables, never commit them to version control
2. **Input Validation**: Use the built-in message filtering middleware to sanitize user inputs
3. **Rate Limiting**: Implement appropriate rate limiting in your application
4. **Updates**: Keep the package updated to the latest version
5. **Configuration**: Review and customize the security configuration based on your needs
6. **Monitoring**: Log and monitor chat interactions for suspicious activity

### Security Features

php-chatbot includes several built-in security features:

- **Message Filtering Middleware**: Filters profanity, aggressive content, and malicious links
- **Input Sanitization**: Validates and sanitizes user inputs
- **Configurable Content Policies**: Customizable rules for content filtering
- **Safe Defaults**: Secure configuration out of the box

For more information about configuring these features, see the main [README.md](README.md).

## Security Hall of Fame

We recognize and thank the following individuals for responsibly disclosing security vulnerabilities:

*(No reports yet)*

---

Thank you for helping keep php-chatbot and our users safe!
