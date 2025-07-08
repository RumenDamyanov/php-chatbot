<?php
/**
 * Laravel helper stubs for package tests.
 *
 * PHP version 8.0+
 *
 * @category Test
 * @package  Rumenx\PhpChatbot\Tests\FrameworkStubs
 * @author   Rumen Damyanov <contact@rumenx.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */

if (!function_exists('config')) {
    /**
     * Dummy config() helper for tests.
     *
     * @param string|null $key Config key
     *
     * @return array|string|null
     */
    function config($key = null)
    {
        return [
            'model' => \Rumenx\PhpChatbot\Models\DefaultAiModel::class
        ];
    }
}
if (!function_exists('config_path')) {
    /**
     * Dummy config_path() helper for tests.
     *
     * @param string|null $file File name
     *
     * @return string
     */
    function config_path($file = null)
    {
        return '/tmp/' . ($file ?: '');
    }
}
if (!function_exists('resource_path')) {
    /**
     * Dummy resource_path() helper for tests.
     *
     * @param string|null $file File name
     *
     * @return string
     */
    function resource_path($file = null)
    {
        return '/tmp/' . ($file ?: '');
    }
}
if (!function_exists('public_path')) {
    /**
     * Dummy public_path() helper for tests.
     *
     * @param string|null $file File name
     *
     * @return string
     */
    function public_path($file = null)
    {
        return '/tmp/' . ($file ?: '');
    }
}
