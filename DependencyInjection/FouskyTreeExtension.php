<?php

namespace Fousky\TreeBundle\DependencyInjection;

use Fousky\TreeBundle\EntityListener\NestedRepositoryInjector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class FouskyTreeExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->configureListeners($config, $container);
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\BadMethodCallException
     */
    protected function configureListeners(array $config, ContainerBuilder $container)
    {
        if (array_key_exists('nested_repository_injector', $config) &&
            $config['nested_repository_injector'] === true
        ) {
            $container->setDefinition(
                'fousky_tree.entity_listener.nested_repository_injector',
                (new Definition(NestedRepositoryInjector::class))
                    ->addTag('doctrine.event_subscriber')
            );
        }
    }
}
