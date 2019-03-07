<?php

namespace EzSystems\EzPlatformGraphQL\DependencyInjection;

use EzSystems\EzPlatformGraphQL\DependencyInjection\GraphQL\SchemaProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EzSystemsEzPlatformGraphQLExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var SchemaProvider
     */
    private $schemaProvider;

    public function __construct(SchemaProvider $schemaProvider)
    {
        $this->schemaProvider = $schemaProvider;
    }

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
        if (($queryType = $this->schemaProvider->getQuerySchema()) !== null) {
            $schema['platform']['query'] = $queryType;
        }

        if (($mutationType = $this->schemaProvider->getMutationSchema()) !== null) {
            $schema['platform']['mutation'] = $mutationType;
        }

        return [
            'definitions' => [
                'config_validation' => '%kernel.debug%',
                'schema' => $schema
            ]
        ];
    }
}
