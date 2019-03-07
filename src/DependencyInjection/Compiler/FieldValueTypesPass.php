<?php
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use EzSystems\EzPlatformGraphQL\DependencyInjection\EzSystemsEzPlatformGraphQLExtension;
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
    private $fieldsYamlDefinitionFile;

    public function __construct($fieldsYamlDefinitionFile)
    {
        $this->fieldsYamlDefinitionFile = $fieldsYamlDefinitionFile;
    }

    public function process(ContainerBuilder $container)
    {
        if (!$container->has('overblog_graphql.request_executor')) {
            return;
        }

        if (!file_exists($this->fieldsYamlDefinitionFile)) {
            return;
        }

        $executorDefinition = $container->getDefinition('overblog_graphql.request_executor');
        foreach ($executorDefinition->getMethodCalls() as $methodCall) {
            if ($methodCall[0] === 'addSchema') {
                $schemaDefinition = $container->getDefinition($methodCall[1][1]);
                $types = $schemaDefinition->getArgument(4);
                $fieldValuesTypes = $this->getDefinedTypes();
                $schemaDefinition->setArgument(4, array_merge($types, $fieldValuesTypes));

            }
        }
    }

    /**
     * @return string[]
     */
    private function getDefinedTypes()
    {
        // @todo make more dynamic
        $types = Yaml::parseFile($this->fieldsYamlDefinitionFile);

        return array_filter(
            array_keys($types),
            function ($typeName) {
                return preg_match('/[a-z]+FieldValue$/i', $typeName);
            }
        );
    }
}