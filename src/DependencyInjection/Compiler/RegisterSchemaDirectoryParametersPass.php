<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterSchemaDirectoryParametersPass implements CompilerPassInterface
{
    private const APP_SCHEMA_DIR_RELATIVE_PATH = '/config/graphql';
    private const EZPLATFORM_SCHEMA_DIR_RELATIVE_PATH = '/ezplatform';
    private const PACKAGE_DIR_RELATIVE_PATH = '/../vendor/ezsystems/ezplatform-graphql';
    private const PACKAGE_SCHEMA_DIR_RELATIVE_PATH = '/src/Resources/config/graphql';
    private const FIELS_DEFINITION_FILE_NAME = 'Field.types.yml';

    public function process(ContainerBuilder $container)
    {
        $rootDir = rtrim($container->getParameter('kernel.root_dir'), '/');
        $appSchemaDir = $rootDir . self::APP_SCHEMA_DIR_RELATIVE_PATH;
        $eZPlatformSchemaDir = $appSchemaDir . self::EZPLATFORM_SCHEMA_DIR_RELATIVE_PATH;
        $packageRootDir = $rootDir . self::PACKAGE_DIR_RELATIVE_PATH;
        $fieldsDefinitionFile = $packageRootDir . self::PACKAGE_SCHEMA_DIR_RELATIVE_PATH . DIRECTORY_SEPARATOR . self::FIELS_DEFINITION_FILE_NAME;

        $container->setParameter('ezplatform.graphql.schema.root_dir', $appSchemaDir);
        $container->setParameter('ezplatform.graphql.schema.ezplatform_dir', $eZPlatformSchemaDir);
        $container->setParameter('ezplatform.graphql.schema.fields_definition_file', $fieldsDefinitionFile);
        $container->setParameter('ezplatform.graphql.package.root_dir', $packageRootDir);
    }
}
