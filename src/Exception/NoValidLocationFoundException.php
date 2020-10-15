<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\Exception;

use eZ\Publish\API\Repository\Values\Content\Content;

class NoValidLocationFoundException extends \Exception
{
    public function __construct(Content $content)
    {
        parent::__construct("No valid location was found for content with id {$content->id}");
    }
}
