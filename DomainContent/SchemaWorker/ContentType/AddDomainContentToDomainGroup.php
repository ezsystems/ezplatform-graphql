<?php
namespace EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\ContentType;

use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\BaseWorker;
use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\SchemaWorker;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;

class AddDomainContentToDomainGroup extends BaseWorker implements SchemaWorker
{
    public function work(array &$schema, array $args)
    {
        $contentType = $args['ContentType'];
        $contentTypeGroup = $args['ContentTypeGroup'];
        $descriptions = $contentType->getDescriptions();

        $schema
            [$this->getGroupName($contentTypeGroup)]
            ['config']['fields']
            [$this->getContentCollectionField($contentType)] = [
                'type' => sprintf("[%s]", $this->getContentName($contentType)),
                // @todo Improve description to mention that it is a collection ?
                'description' => isset($descriptions['eng-GB']) ? $descriptions['eng-GB'] : 'No description available',
                'resolve' => sprintf(
                    '@=resolver("DomainContentItemsByTypeIdentifier", ["%s", args])',
                    $contentType->identifier
                ),
                'args' => [
                    'query' => [
                        'type' => "ContentSearchQuery",
                        'description' => "A Content query used to filter results"
                    ],
                    'sortBy' => [
                        'type' => '[SortByOptions]',
                        'description' => "A sort clause, or array of clauses. Add _desc after a clause to reverse it"
                    ],
                ],
            ];

        $schema
            [$this->getGroupName($contentTypeGroup)]
            ['config']['fields']
            [$this->getContentField($contentType)] = [
                'type' => $this->getContentName($contentType),
                'description' => isset($descriptions['eng-GB']) ? $descriptions['eng-GB'] : 'No description available',
                'resolve' => sprintf('@=resolver("DomainContentItem", [args, "%s"])', $contentType->identifier),
                'args' => [
                    // @todo How do we constraint this so that it only takes an id of an item of that type ?
                    // same approach than GlobalId ? (<type>-<id>)
                    'id' => [
                        'type' => 'Int',
                        'description' => sprintf('A %s content id', $contentType->identifier),
                    ],
                    'locationId' => [
                        'type' => 'Int',
                        'description' => sprintf('A %s content location id', $contentType->identifier),
                    ],
                    'remoteId' => [
                        'type' => 'String',
                        'description' => sprintf('A %s content remote id', $contentType->identifier),
                    ]
                ]
            ];

        $schema[$this->getGroupTypesName($contentTypeGroup)]
            ['config']['fields']
            [$this->getNameHelper()->domainContentField($contentType)] = [
                'type' => $this->getNameHelper()->domainContentTypeName($contentType),
                'resolve' => sprintf(
                    '@=resolver("ContentType", [{"identifier": "%s"}])',
                    $contentType->identifier
                ),
            ];
    }

    public function canWork(array $schema, array $args)
    {
        return
            isset($args['ContentType'])
            && $args['ContentType'] instanceof ContentType
            && isset($args['ContentTypeGroup'])
            && $args['ContentTypeGroup'] instanceof ContentTypeGroup
            && !$this->isFieldDefined($args['ContentTypeGroup'], $args['ContentType']);
    }

    /**
     * @param $contentTypeGroup
     * @return string
     */
    protected function getGroupName($contentTypeGroup): string
    {
        return $this->getNameHelper()->domainGroupName($contentTypeGroup);
    }

    /**
     * @param $contentTypeGroup
     * @return string
     */
    protected function getGroupTypesName($contentTypeGroup): string
    {
        return $this->getNameHelper()->domainGroupName($contentTypeGroup) . 'Types';
    }

    /**
     * @param $contentType
     * @return string
     */
    protected function getContentCollectionField($contentType): string
    {
        return $this->getNameHelper()->domainContentCollectionField($contentType);
    }

    /**
     * @param $contentType
     * @return string
     */
    protected function getContentField($contentType): string
    {
        return $this->getNameHelper()->domainContentField($contentType);
    }

    /**
     * @param $contentType
     * @return string
     */
    protected function getContentName($contentType): string
    {
        return $this->getNameHelper()->domainContentName($contentType);
    }

    private function isFieldDefined(ContentTypeGroup $contentTypeGroup, ContentType $contentType)
    {
        return isset(
            $schema
            [$this->getGroupName($contentTypeGroup)]
            ['config']['fields']
            [$this->getContentCollectionField($contentType)]
        );
    }
}