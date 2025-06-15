<?php
use Psr\Log\NullLogger;

if (!class_exists('DummyLogger')) {
    class DummyLogger extends NullLogger {
        public array $logs = [];
        public function error($message, array $context = []): void {
            $this->logs[] = $message;
        }
    }
}
