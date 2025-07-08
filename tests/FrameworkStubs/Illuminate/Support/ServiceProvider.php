<?php

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
