<?php
/**
 * Created by PhpStorm.
 * User: bdunogier
 * Date: 23/09/2018
 * Time: 23:21
 */

namespace BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\ContentType;

use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\BaseWorker;
use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\SchemaWorker;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;

class DefineDomainContent extends BaseWorker implements SchemaWorker
{
    public function work(array &$schema, array $args)
    {
        $schema[$this->getDomainContentName($args['ContentType'])] = [
            'type' => 'object',
            'inherits' => ['AbstractDomainContent'],
            'config' => [
                'fields' => [],
                'interfaces' => ['DomainContent'],
            ]
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