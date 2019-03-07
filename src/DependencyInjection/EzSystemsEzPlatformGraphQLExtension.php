<?php

namespace EzSystems\EzPlatformGraphQL\DependencyInjection;

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
class EzSystemsEzPlatformGraphQLExtension extends Extension implements PrependExtensionInterface
{
    const GRAPHQL_CONFIG_DIR = __DIR__ . '/../../../../../app/config/graphql';
    const SCHEMA_DIR = self::GRAPHQL_CONFIG_DIR . '/ezplatform';
    const DOMAIN_SCHEMA_FILE = self::SCHEMA_DIR . '/Domain.types.yml';
    const APP_QUERY_SCHEMA = self::GRAPHQL_CONFIG_DIR . '/Query.types.yml';
    const APP_MUTATION_SCHEMA = self::GRAPHQL_CONFIG_DIR . '/Mutation.types.yml';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('schema.yml');
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
        if (file_exists(self::APP_QUERY_SCHEMA)) {
            $schema['platform'] = ['query' => 'Query'];
        } else if (file_exists(self::DOMAIN_SCHEMA_FILE)) {
            $schema['platform'] = ['query' => 'Domain'];
        } else {
            $schema['platform'] = ['query' => 'Platform'];
        }

        if (file_exists(self::APP_MUTATION_SCHEMA)) {
            $schema['platform']['mutation'] = 'Mutation';
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
