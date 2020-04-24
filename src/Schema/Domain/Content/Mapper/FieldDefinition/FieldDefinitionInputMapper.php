<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * Maps a Field Definition to its GraphQL components for input (mutations).
 */
interface FieldDefinitionInputMapper
{
    public function mapToFieldValueInputType(ContentType $contentType, FieldDefinition $fieldDefinition): ?string;
}
