<?php

namespace Rumenx\PhpChatbot\Adapters\Symfony;

// Dummy base class for IDE/static analysis if Symfony is not installed
if (!class_exists('Symfony\\Component\\HttpKernel\\Bundle\\Bundle')) {
    abstract class Bundle
    {
    }
}
