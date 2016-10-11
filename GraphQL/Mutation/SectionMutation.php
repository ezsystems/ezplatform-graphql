<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\EzPlatformGraphQLBundle\GraphQL\Mutation;

use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;

class SectionMutation
{
    /**
     * @var SectionService
     */
    private $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    public function createSection($value)
    {
        $sectionCreateStruct = new SectionCreateStruct(
            [
                'identifier' => $value['identifier'],
                'name' => $value['name'],
            ]
        );

        $section = $this->sectionService->createSection($sectionCreateStruct);

        return $this->mapSectionToPayLoad($value, $section);
    }

    public function deleteSection($value)
    {
        if (isset($value['id'])) {
            $section = $this->sectionService->loadSection($value['id']);
        }

        if (isset($value['identifier'])) {
            $section = $this->sectionService->loadSectionByIdentifier($value['identifier']);
        }

        if (isset($section)) {
            $this->sectionService->deleteSection($section);

            return $this->mapSectionToPayLoad($value, $section);
        }

        return null;
    }

    /**
     * @param $value
     * @param $section
     * @return array
     */
    private function mapSectionToPayLoad($value, $section)
    {
        return [
            'clientMutationId' => $value['clientMutationId'],
            'id' => $section->id,
            'identifier' => $section->identifier,
            'name' => $section->name,
        ];
    }
}
