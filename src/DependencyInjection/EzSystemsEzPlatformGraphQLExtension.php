<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\DependencyInjection;

use EzSystems\EzPlatformGraphQL\DependencyInjection\GraphQL\YamlSchemaProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EzSystemsEzPlatformGraphQLExtension extends Extension implements PrependExtensionInterface
{
    private const SCHEMA_DIR_PATH = '/config/graphql/types';
    private const EZPLATFORM_SCHEMA_DIR_PATH = '/ezplatform';
    private const PACKAGE_DIR_PATH = '/vendor/ezsystems/ezplatform-graphql';
    private const PACKAGE_SCHEMA_DIR_PATH = '/src/Resources/config/graphql';
    private const FIELDS_DEFINITION_FILE_NAME = 'Field.types.yaml';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services/data_loaders.yaml');
        $loader->load('services/mutations.yaml');
        $loader->load('services/resolvers.yaml');
        $loader->load('services/schema.yaml');
        $loader->load('services/services.yaml');
        $loader->load('default_settings.yaml');
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $this->setContainerParameters($container);

        $configDir = $container->getParameter('ezplatform.graphql.schema.root_dir');

        $graphQLConfig = $this->getGraphQLConfig($configDir);
        $graphQLConfig['definitions']['mappings']['types'][] = [
            'type' => 'yaml',
            'dir' => $container->getParameter('ezplatform.graphql.package.root_dir') . self::PACKAGE_SCHEMA_DIR_PATH,
        ];
        $graphQLConfig['definitions']['mappings']['types'][] = [
            'type' => 'yaml',
            'dir' => $container->getParameter('kernel.project_dir') . self::SCHEMA_DIR_PATH,
        ];
        $container->prependExtensionConfig('overblog_graphql', $graphQLConfig);
    }

    /**
     * Uses YamlConfigProvider to determinate what schema should be used.
     *
     * @param string $configDir
     *
     * @return array
     */
    private function getGraphQLConfig(string $configDir): array
    {
        $schemaConfiguration = (new YamlSchemaProvider($configDir))->getSchemaConfiguration();

        return [
            'definitions' => [
                'config_validation' => '%kernel.debug%',
                'schema' => $schemaConfiguration,
            ],
        ];
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    private function setContainerParameters(ContainerBuilder $container): void
    {
        $rootDir = rtrim($container->getParameter('kernel.project_dir'), '/');

        $appSchemaDir = $rootDir . self::SCHEMA_DIR_PATH;
        $eZPlatformSchemaDir = $appSchemaDir . self::EZPLATFORM_SCHEMA_DIR_PATH;
        $packageRootDir = $rootDir . self::PACKAGE_DIR_PATH;
        $fieldsDefinitionFile = $packageRootDir . self::PACKAGE_SCHEMA_DIR_PATH . DIRECTORY_SEPARATOR . self::FIELDS_DEFINITION_FILE_NAME;

        $container->setParameter('ezplatform.graphql.schema.root_dir', $appSchemaDir);
        $container->setParameter('ezplatform.graphql.schema.ezplatform_dir', $eZPlatformSchemaDir);
        $container->setParameter('ezplatform.graphql.schema.fields_definition_file', $fieldsDefinitionFile);
        $container->setParameter('ezplatform.graphql.package.root_dir', $packageRootDir);
    }
}
