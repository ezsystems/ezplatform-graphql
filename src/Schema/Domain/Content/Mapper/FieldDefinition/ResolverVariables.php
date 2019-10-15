<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * Maps a Field Definition to its GraphQL components.
 */
class ResolverVariables implements FieldDefinitionMapper
{
    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper
     */
    private $innerMapper;

    public function __construct(FieldDefinitionMapper $innerMapper)
    {
        $this->innerMapper = $innerMapper;
    }

    public function mapToFieldDefinitionType(FieldDefinition $fieldDefinition): string
    {
        return $this->innerMapper->mapToFieldDefinitionType($fieldDefinition);
    }

    public function mapToFieldValueType(FieldDefinition $fieldDefinition): string
    {
        return $this->innerMapper->mapToFieldValueType($fieldDefinition);
    }

    public function mapToFieldValueResolver(FieldDefinition $fieldDefinition): string
    {
        $resolver = $this->innerMapper->mapToFieldValueResolver($fieldDefinition);

        return str_replace(
            [
                'content',
                'field',
            ],
            [
                'value',
                'resolver("DomainFieldValue", [value, "' . $fieldDefinition->identifier . '"])',
            ],
            $resolver
        );
    }

    public function mapToFieldValueInputType(ContentType $contentType, FieldDefinition $fieldDefinition): ?string
    {
        return $this->innerMapper->mapToFieldValueInputType($contentType, $fieldDefinition);
    }
}
