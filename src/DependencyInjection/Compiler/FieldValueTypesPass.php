<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Registers the FieldValue GraphQL types.
 *
 * Since they are only referenced by an interface's resolver, they're not added by default.
 */
class FieldValueTypesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->has('overblog_graphql.request_executor')
            || !$container->hasParameter('ezplatform.graphql.schema.fields_definition_file')
        ) {
            return;
        }

        $fieldsDefinitionFile = $container->getParameter('ezplatform.graphql.schema.fields_definition_file');

        if (null === $fieldsDefinitionFile || !file_exists($fieldsDefinitionFile)) {
            return;
        }

        $executorDefinition = $container->getDefinition('overblog_graphql.request_executor');
        foreach ($executorDefinition->getMethodCalls() as $methodCall) {
            if ($methodCall[0] === 'addSchema') {
                $schemaDefinition = $container->getDefinition($methodCall[1][1]);
                $types = $schemaDefinition->getArgument(4);
                $fieldValuesTypes = $this->getDefinedTypesFromFile($fieldsDefinitionFile);
                $schemaDefinition->setArgument(4, array_merge($types, $fieldValuesTypes));
            }
        }
    }

    /**
     * @param string $filePath
     *
     * @return string[]
     */
    private function getDefinedTypesFromFile(string $filePath): array
    {
        // @todo make more dynamic
        $types = Yaml::parseFile($filePath);

        return array_filter(
            array_keys($types),
            function ($typeName) {
                return preg_match('/[a-z]+FieldValue$/i', $typeName);
            }
        );
    }
}
