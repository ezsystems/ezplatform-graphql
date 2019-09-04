<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Search;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

class SolrSearchFeatures implements SearchFeatures
{
    public function supportsFieldCriterion(FieldDefinition $fieldDefinition)
    {
        return true;
    }
}
