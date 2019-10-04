<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

class ConfigurableFieldDefinitionInputMapper implements FieldDefinitionInputMapper
{
    /**
     * @var array
     * Map of ez field type identifier to a GraphQL type
     */
    private $typesMap;

    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionInputMapper
     */
    private $innerMapper;

    public function __construct(FieldDefinitionInputMapper $innerMapper, $typesMap = [])
    {
        $this->typesMap = $typesMap;
        $this->innerMapper = $innerMapper;
    }

    public function mapToFieldValueInputType(ContentType $contentType, FieldDefinition $fieldDefinition): ?string
    {
        return $this->typesMap[$fieldDefinition->fieldTypeIdentifier]
            ?? $this->innerMapper->mapToFieldValueInputType($contentType, $fieldDefinition);
    }
}
