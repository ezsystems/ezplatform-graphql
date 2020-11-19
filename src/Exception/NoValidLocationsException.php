<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\Exception;

use Exception;
use eZ\Publish\API\Repository\Values\Content\Content;

class NoValidLocationsException extends Exception
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content|\eZ\Publish\API\Repository\Values\Content\Content[]
     */
    private $content;

    /**
     * NoValidLocationsException constructor.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    public function __construct(Content $content)
    {
        parent::__construct("No valid location could be determined for content #{$content->id}");
        $this->content = $content;
    }

    public function getContent(): Content
    {
        return $this->content;
    }
}
