<?php

declare(strict_types=1);

namespace Illuminate\Support;

if (!class_exists('Illuminate\\Support\\ServiceProvider')) {
    abstract class ServiceProvider
    {
        public $app;
        public function __construct($app = null) { $this->app = $app; }
        public function register() {}
        public function boot() {}
        public function publishes() {}
        public function callAfterResolving() {}
    }
}
