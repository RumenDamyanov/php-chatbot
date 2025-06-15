<?php

namespace Rumenx\PhpChatbot\Adapters\Laravel;

use Rumenx\PhpChatbot\Contracts\AiModelInterface;
use Rumenx\PhpChatbot\PhpChatbot;

class PhpChatbotServiceProvider // extends \Illuminate\Support\ServiceProvider (for real Laravel usage)
{
    public function register(): void
    {
        // In real Laravel, this would use $this->app->singleton
        // Here, left as a stub for static analysis and package structure
    }

    public function boot(): void
    {
        // In real Laravel, this would publish config, views, assets, etc.
    }
}
