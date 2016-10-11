<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\EzPlatformGraphQLBundle\GraphQL\Value;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * @property-read int $contentId
 */
class ContentFieldValue extends ValueObject
{
    protected $fieldDefIdentifier;

    protected $contentId;

    /**
     * @var \eZ\Publish\Core\FieldType\Value
     */
    protected $value;
}
