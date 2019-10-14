<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL;

use EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EzSystemsEzPlatformGraphQLBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new Compiler\FieldDefinitionInputMappersPass());
        $container->addCompilerPass(new Compiler\FieldInputHandlersPass());
        $container->addCompilerPass(new Compiler\InputTypesMappingPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
        $container->addCompilerPass(new Compiler\RichTextInputConvertersPass());
        $container->addCompilerPass(new Compiler\SchemaWorkersPass());
        $container->addCompilerPass(new Compiler\SchemaDomainIteratorsPass());
    }
}
