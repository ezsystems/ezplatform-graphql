<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformGraphQL\Exception\LogicException;

abstract class DecoratingFieldDefinitionMapper implements FieldDefinitionMapper
{
    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper
     */
    protected $innerMapper;

    public function __construct(FieldDefinitionMapper $innerMapper)
    {
        $this->innerMapper = $innerMapper;
    }

    public function mapToFieldDefinitionType(FieldDefinition $fieldDefinition): ?string
    {
        return $this->innerMapper->mapToFieldDefinitionType($fieldDefinition);
    }

    public function mapToFieldValueType(FieldDefinition $fieldDefinition): ?string
    {
        return $this->innerMapper->mapToFieldValueType($fieldDefinition);
    }

    public function mapToFieldValueResolver(FieldDefinition $fieldDefinition): ?string
    {
        return $this->innerMapper->mapToFieldValueResolver($fieldDefinition);
    }

    public function mapToFieldValueInputType(ContentType $contentType, FieldDefinition $fieldDefinition): ?string
    {
        /** @deprecated this test will be removed in ezplatform-graphql 2.x */
        if ($this->innerMapper instanceof FieldDefinitionInputMapper) {
            return $this->innerMapper->mapToFieldValueInputType($contentType, $fieldDefinition);
        }

        throw new LogicException('The inner mapper is not a FieldDefinitionInputMapper. This method should not have been called in a 1.x version of ezplatform-graphql.');
    }

    public function mapToFieldValueArgsBuilder(FieldDefinition $fieldDefinition): ?string
    {
        /** @deprecated this test will be removed in ezplatform-graphql 2.x */
        if ($this->innerMapper instanceof FieldDefinitionArgsBuilderMapper) {
            return $this->innerMapper->mapToFieldValueArgsBuilder($fieldDefinition);
        }

        throw new LogicException('The inner mapper is not a FieldDefinitionArgsMapper. This method should not have been called in a 1.x version of ezplatform-graphql.');
    }

    abstract protected function getFieldTypeIdentifier(): string;

    protected function canMap(FieldDefinition $fieldDefinition)
    {
        return $fieldDefinition->fieldTypeIdentifier === $this->getFieldTypeIdentifier();
    }
}
