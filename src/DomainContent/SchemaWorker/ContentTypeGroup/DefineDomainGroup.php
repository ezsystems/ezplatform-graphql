<?php
namespace EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\ContentTypeGroup;

use EzSystems\EzPlatformGraphQL\DomainContent\NameHelper;
use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\BaseWorker;
use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\SchemaWorker;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;

class DefineDomainGroup extends BaseWorker implements SchemaWorker
{
    public function work(array &$schema, array $args)
    {
        $schema[$this->getGroupName($args['ContentTypeGroup'])] = [
            'type' => 'object',
            'inherits' => ['DomainContentTypeGroup'],
            'config' => [
                'fields' => [
                    '_types' => [
                        'type' => $this->getGroupTypesName($args['ContentTypeGroup']),
                        'resolve' => []
                    ]
                ],
            ]
        ];

        // Type that indexes the types from a group by identifier
        $schema[$this->getGroupTypesName($args['ContentTypeGroup'])] = [
            'type' => 'object',
            'config' => ['fields' => []]
        ];
    }

    public function canWork(array $schema, array $args)
    {
        return
            isset($args['ContentTypeGroup']) && $args['ContentTypeGroup'] instanceof ContentTypeGroup
            && !isset($schema[$this->getGroupName($args['ContentTypeGroup'])]);
    }

    /**
     * @param ContentTypeGroup $contentTypeGroup
     * @return string
     */
    protected function getGroupName(ContentTypeGroup $contentTypeGroup): string
    {
        return $this->getNameHelper()->domainGroupName($contentTypeGroup);
    }

    private function getGroupTypesName($contentTypeGroup): string
    {
        return $this->getNameHelper()->domainGroupName($contentTypeGroup) . 'Types';
    }
}