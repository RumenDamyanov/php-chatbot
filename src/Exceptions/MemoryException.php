<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Exceptions;

/**
 * Exception thrown when a conversation memory error occurs.
 *
 * This exception is thrown when:
 * - Memory storage backend fails (file, Redis, database)
 * - Cannot read or write conversation history
 * - Storage permissions are insufficient
 * - Storage quota is exceeded
 *
 * @package Rumenx\PhpChatbot\Exceptions
 */
class MemoryException extends PhpChatbotException
{
}
