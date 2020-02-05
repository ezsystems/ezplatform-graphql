<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * Maps a Field Definition to its GraphQL arguments.
 */
interface FieldDefinitionArgsBuilderMapper
{
    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     *
     * @return string|null the argsBuilder string, or null if there are none.
     */
    public function mapToFieldValueArgsBuilder(FieldDefinition $fieldDefinition): ?string;
}
