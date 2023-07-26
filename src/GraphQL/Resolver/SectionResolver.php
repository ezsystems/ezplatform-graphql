<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\SectionService;

/**
 * @internal
 */
class SectionResolver
{
    /**
     * @var \eZ\Publish\API\Repository\SectionService
     */
    private $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    public function resolveSectionById($sectionId): ?Section
    {
        try {
            return $this->sectionService->loadSection($sectionId);
        } catch (UnauthorizedException $ex) {
            return null;
        }
    }
}
