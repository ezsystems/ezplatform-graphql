## Support field types

Custom field types have their own values and field definition structure, that need to be defined for GraphQL.

Example with Text Line:
- the Field Value for those fields is a String
- the resolver is the default one
- the field definition is a `TextLineFieldDefinition`, that exposes the min length, max length and default value

More complex example with Relation List:
- the Field Value for those fields is either `DomainContent` or a specific type of content (`ArticleContent`, `ImageContent`),
  depending on the field definition settings
- the resolver is fully customized
- the field definition is a `RelationListFieldDefinition`

## Mapping a custom field type
There are two ways to map a custom Field Type: using configuration, and by writing your own `FieldDefinitionMapper`.
Which one you choose depends if the field definition settings and constraints impact how it is mapped to GraphQL.

### Mapping with configuration
You need to use a simple compiler pass to modify a container parameter, `ezplatform_graphql.schema.content.mapping.field_definition_type`.
It is a hash that maps a field type identifier (`ezstring`) to the following entries:
- `value_type`: the GraphQL type values of this field are represented as. It can either be a native type
  (`String`, `Int`...), or a custom type that you will define.
  If not specified, `String` will be used.
- `value_resolver`: how values of this field are resolved and passed to the defined value type.
  If not specified, it will receive the `Field` object for the field type: `field`.
- `definition_type`: the GraphQL type field definitions of this type are represented as.
  If not specified, it will use `FieldDefinition`

Example of a compiler pass:
```
<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LandingPageGraphQLConfigurationPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('ezplatform_graphql.schema.content.mapping.field_definition_type')) {
            return;
        }

        $mapping = $container->getParameter('ezplatform_graphql.schema.content.mapping.field_definition_type');
        $mapping['my_custom_fieldtype'] = [
            'value_type' => 'MyCustomFieldValue',
            'definition_type' => 'MyCustomFieldDefinition',
            'value_resolver' => 'field.someProperty'
        ];
    }
}
```

### Mapping with a custom `FieldDefinitionMapper`
If the mapping of your field type depends on the Field Definition, you need to write a custom `FieldDefinitionMapper`.

The `FieldDefinitionMapper` API uses service decorators. To register your own mapper, make it decorate the 
`EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper` service:

```yaml
    AppBundle\GraphQL\Schema\MyCustomFieldDefinitionMapper:
        decorates: EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper
        arguments:
            $innerMapper: '@AppBundle\GraphQL\Schema\MyCustomFieldDefinitionMapper'
```

The `$innerMapper` argument will pass the decorated mapper to the constructor.:
You can use the `DecoratingFieldDefinitionMapper` from the ezplatform-graphql package to minimize the code to write.
It requires that you implement the `getFieldTypeIdentifier()` method to tell which field type is covered by the mapper:

```
namespace AppBundle\GraphQL\Schema;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\DecoratingFieldDefinitionMapper
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;

class MyCustomFieldDefinitionMapper extends DecoratingFieldDefinitionMapper implements FieldDefinitionMapper
{
    protected function getFieldTypeIdentifier(): string
    {
        return 'my_custom_field_type';
    }
}
```

The `FieldDefinitionMapper` interface defines three methods:
- `mapToFieldValueType()`: returns the GraphQL type value of this field are mapped to.
- `mapToFieldValueResolver()`: returns the resolver, as an expression language string, values are resolved with.
- `mapToFieldDefinitionType()`: returns the GraphQL type field definitions of this type are mapped to.

If you don't need one of those methods, for instance if you don't need a custom Field Definition, don't implement it,
and it will be handled by other mappers (configuration or default).

Example with the `RelationFieldDefinitionMapper`:
```php
class RelationFieldDefinitionMapper extends DecoratingFieldDefinitionMapper implements FieldDefinitionMapper
{
    /**
     * The value type depends on the field definition allowed content types setting:
     * - if one and only one content type is allowed, the value will be of this type
     * - if there are no restrictions, or several types are allowed, the value will be a `DomainContent`
     *
     * The cardinality (single or collection) depends on the selection limit setting:
     * - if only one item is allowed, the value is unique: `ArticleContent`, `DomainContent`, ...
     * - if there are no limits, or a limit larger than 1, the value is a collection: `"[ArticleContent]"`, `"[DomainContent]"`.
     */
    public function mapToFieldValueType(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueType($fieldDefinition);
        }
        $settings = $fieldDefinition->getFieldSettings();

        if (count($settings['selectionContentTypes']) === 1) {
            $contentType = $this->contentTypeService->loadContentTypeByIdentifier($settings['selectionContentTypes'][0]);
            $type = $this->nameHelper->domainContentName($contentType);
        } else {
            $type = 'DomainContent';
        }

        if ($this->isMultiple($fieldDefinition)) {
            $type = "[$type]";
        }

        return $type;
    }

    /**
     * The resolver uses a boolean argument `isMultiple` that depends on the selection limit setting.
     */
    public function mapToFieldValueResolver(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueResolver($fieldDefinition);
        }

        $isMultiple = $this->isMultiple($fieldDefinition) ? 'true' : 'false';

        return sprintf('@=resolver("DomainRelationFieldValue", [field, %s])', $isMultiple);
    }

    private function isMultiple(FieldDefinition $fieldDefinition)
    {
        $constraints = $fieldDefinition->getValidatorConfiguration();

        return isset($constraints['RelationListValueValidator'])
            && $constraints['RelationListValueValidator']['selectionLimit'] !== 1;
    }
}
```

#### Field Definition Input Mappers
As of v1.0.4, an extra interface is available for mutation input type handling, `FieldDefinitionInputMapper`.
It is used if the input for this field depends on the field definition. For instance, `ezmatrix`
generates its own input types depending on the configured columns.  It defines an extra method, `mapToFieldValueInputType`, 
that returns a GraphQL type for a Field Definition.

Example:
```
class MyFieldDefinitionMapper extends DecoratingFieldDefinitionMapper implements FieldDefinitionMapper, FieldDefinitionInputMapper
{
    public function mapToFieldValueInputType(ContentType $contentType, FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueInputType($fieldDefinition);
        }

        return $this->nameMyFieldType($fieldDefinition);
    }
}
```

In 2.0, `FieldDefinitionInputMapper` and `FieldDefinitionMapper` will be merged, and the service tag will be deprecated.

## Resolver expressions
Two variables are available in the resolver's expression:

- `field` is the current field, as an extension of the API's Field object that proxies properties requests to the Field Value
- `content` is the resolved content item's `ContentInfo`.

`RelationFieldValueBuilder` or `SelectionFieldValueBuilder` can be used as examples.