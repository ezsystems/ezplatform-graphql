<?php
namespace BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\ContentType;

use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\BaseWorker;
use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\SchemaWorker;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;

class DefineDomainContent extends BaseWorker implements SchemaWorker
{
    public function work(array &$schema, array $args)
    {
        $contentType = $args['ContentType'];
        $schema[$this->getDomainContentName($contentType)] = [
            'type' => 'object',
            'inherits' => ['AbstractDomainContent'],
            'config' => [
                'fields' => [],
                'interfaces' => ['DomainContent'],
            ]
        ];

        $schema[$this->getNameHelper()->domainContentTypeName($contentType)] = [
            'type' => 'object',
            'inherits' => ['BaseDomainContentType'],
            'config' => [
                'interfaces' => ['DomainContentType'],
                'fields' => []
            ],
        ];
    }

    public function canWork(array $schema, array $args)
    {
        return
            isset($args['ContentType']) && $args['ContentType'] instanceof ContentType
            && !isset($schema[$this->getDomainContentName($args['ContentType'])]);
    }

    /**
     * @param $contentType
     * @return string
     */
    protected function getDomainContentName(ContentType $contentType): string
    {
        return $this->getNameHelper()->domainContentName($contentType);
    }
}