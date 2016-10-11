<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\EzPlatformGraphQLBundle\GraphQL\Resolver;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SectionService;
use Overblog\GraphQLBundle\Resolver\TypeResolver;

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

    public function resolveSectionById($sectionId)
    {
        return $this->sectionService->loadSection($sectionId);
    }
}
