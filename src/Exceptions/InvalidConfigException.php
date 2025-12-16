<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Exceptions;

/**
 * Exception thrown when configuration is invalid.
 *
 * This exception is thrown when:
 * - Required configuration keys are missing (e.g., API keys)
 * - Configuration values have invalid types or formats
 * - Model names are not supported
 * - Endpoint URLs are malformed
 *
 * Extends InvalidArgumentException for backward compatibility with existing code
 * that catches InvalidArgumentException.
 *
 * @package Rumenx\PhpChatbot\Exceptions
 */
class InvalidConfigException extends \InvalidArgumentException
{
}

