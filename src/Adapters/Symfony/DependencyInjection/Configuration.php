<?php

namespace Rumenx\PhpChatbot\Adapters\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Defines and validates configuration options for PhpChatbotBundle.
 *
 * @category DependencyInjection
 * @package  Rumenx\PhpChatbot
 * @author   Rumen X <contact@rumenx.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('php_chatbot');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('api_key')
            ->defaultNull()
            ->info('API key for the chatbot service')
            ->end()
                ->scalarNode('model')
            ->defaultValue('gpt-3.5-turbo')
            ->info('Default AI model')
            ->end()
            ->end();

        return $treeBuilder;
    }
}
