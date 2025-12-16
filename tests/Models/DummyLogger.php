<?php

declare(strict_types=1);
use Psr\Log\LoggerInterface;

if (!class_exists('DummyLogger')) {
    class DummyLogger implements LoggerInterface
    {
        /**
         * Collected log messages.
         *
         * @var array
         */
        public $logs = [];

        /**
         * Log an emergency message.
         *
         * @param Stringable|string $message The log message.
         * @param array             $context Context array.
         *
         * @return void
         */
        public function emergency(
            Stringable|string $message,
            array $context = []
        ): void {
            $this->logs[] = '[emergency] ' . (string)$message;
        }

        /**
         * Log an alert message.
         *
         * @param Stringable|string $message The log message.
         * @param array             $context Context array.
         *
         * @return void
         */
        public function alert(
            Stringable|string $message,
            array $context = []
        ): void {
            $this->logs[] = '[alert] ' . (string)$message;
        }

        /**
         * Log a critical message.
         *
         * @param Stringable|string $message The log message.
         * @param array             $context Context array.
         *
         * @return void
         */
        public function critical(
            Stringable|string $message,
            array $context = []
        ): void {
            $this->logs[] = '[critical] ' . (string)$message;
        }

        /**
         * Log an error message.
         *
         * @param Stringable|string $message The log message.
         * @param array             $context Context array.
         *
         * @return void
         */
        public function error(
            Stringable|string $message,
            array $context = []
        ): void {
            $this->logs[] = '[error] ' . (string)$message;
        }

        /**
         * Log a warning message.
         *
         * @param Stringable|string $message The log message.
         * @param array             $context Context array.
         *
         * @return void
         */
        public function warning(
            Stringable|string $message,
            array $context = []
        ): void {
            $this->logs[] = '[warning] ' . (string)$message;
        }

        /**
         * Log a notice message.
         *
         * @param Stringable|string $message The log message.
         * @param array             $context Context array.
         *
         * @return void
         */
        public function notice(
            Stringable|string $message,
            array $context = []
        ): void {
            $this->logs[] = '[notice] ' . (string)$message;
        }

        /**
         * Log an info message.
         *
         * @param Stringable|string $message The log message.
         * @param array             $context Context array.
         *
         * @return void
         */
        public function info(
            Stringable|string $message,
            array $context = []
        ): void {
            $this->logs[] = '[info] ' . (string)$message;
        }

        /**
         * Log a debug message.
         *
         * @param Stringable|string $message The log message.
         * @param array             $context Context array.
         *
         * @return void
         */
        public function debug(
            Stringable|string $message,
            array $context = []
        ): void {
            $this->logs[] = '[debug] ' . (string)$message;
        }

        /**
         * Logs with an arbitrary level.
         *
         * @param mixed             $level    Log level.
         * @param Stringable|string $message The log message.
         * @param array             $context Context array.
         *
         * @return void
         */
        public function log(
            $level,
            Stringable|string $message,
            array $context = []
        ): void {
            $this->logs[] = '[' . $level . '] ' . (string)$message;
        }
    }
}
