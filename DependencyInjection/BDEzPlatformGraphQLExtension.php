<?php

namespace BD\EzPlatformGraphQLBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BDEzPlatformGraphQLExtension extends Extension implements PrependExtensionInterface
{
    const DOMAIN_SCHEMA_FILE = __DIR__. '/../../../../app/config/graphql/Domain.types.yml';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('domain_content.yml');
        $loader->load('resolvers.yml');
        $loader->load('services.yml');
    }

    /**
     * Allow an extension to prepend the extension configurations.
     */
    public function prepend(ContainerBuilder $container)
    {
        
        $container->prependExtensionConfig('overblog_graphql', $this->getGraphQLConfig());
    }

    private function getGraphQLConfig()
    {
        $schemaFilePath = self::DOMAIN_SCHEMA_FILE;

        if (!file_exists($schemaFilePath)) {
            $schema['platform'] = ['query' => 'Platform'];
        } else {
            $schema['platform'] = ['query' => 'Domain'];
        }

        // Deprecated, use the default schema with the '_repository field instead.
        // Will be removed in a further release"
        // @todo remove BC code
        $schema['repository'] = ['query' => 'Repository'];

        return [
            'definitions' => [
                'config_validation' => '%kernel.debug%',
                'schema' => $schema
            ]
        ];
    }
}
