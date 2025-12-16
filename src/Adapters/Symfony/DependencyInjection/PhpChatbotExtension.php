<?php

declare(strict_types=1);

namespace Rumenx\PhpChatbot\Adapters\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Loads and manages php-chatbot bundle configuration for Symfony.
 *
 * @category DependencyInjection
 * @package  Rumenx\PhpChatbot
 * @author   Rumen X <contact@rumenx.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/RumenDamyanov/php-chatbot
 */
class PhpChatbotExtension extends Extension
{
    /**
     * Loads and manages php-chatbot bundle configuration for Symfony.
     *
     * {@inheritdoc}
     *
     * @param array<int, array<string, mixed>> $configs   Configuration arrays.
     * @param ContainerBuilder                 $container The Symfony container.
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Set config parameters for DI
        foreach ($config as $key => $value) {
            $container->setParameter('php_chatbot.' . $key, $value);
        }

        // Load services.yaml from Resources/config
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');
    }
}
