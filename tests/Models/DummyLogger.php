<?php
use Psr\Log\NullLogger;

if (!class_exists('DummyLogger')) {
    class DummyLogger extends NullLogger {
        /**
         * Collected log messages.
         *
         * @var array
         */
        public $logs = [];

        /**
         * Log an error message.
         *
         * @param Stringable|string $message The log message.
         * @param array $context Context array.
         *
         * @return void
         */
        public function error(Stringable|string $message, array $context = []): void
        {
            $this->logs[] = (string)$message;
        }
    }
}
