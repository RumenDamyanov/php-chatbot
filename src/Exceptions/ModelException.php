<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Exceptions;

/**
 * Exception thrown when an AI model error occurs.
 *
 * This exception is thrown when:
 * - Model is not available or supported
 * - Model response is malformed or unexpected
 * - Model processing fails internally
 * - Model factory cannot create an instance
 *
 * Extends RuntimeException for backward compatibility with existing code
 * that catches RuntimeException.
 *
 * @package Rumenx\PhpChatbot\Exceptions
 */
class ModelException extends \RuntimeException
{
}
