<?php
/**
 * Created by PhpStorm.
 * User: bdunogier
 * Date: 23/09/2018
 * Time: 14:06
 */

namespace BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\ContentTypeGroup;

use BD\EzPlatformGraphQLBundle\DomainContent\NameHelper;
use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\BaseWorker;
use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\SchemaWorker;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;

class DefineDomainGroup extends BaseWorker implements SchemaWorker
{
    public function work(array &$schema, array $args)
    {
        $schema[$this->getGroupName($args['ContentTypeGroup'])] = [
            'type' => 'object',
            'inherits' => ['DomainContentTypeGroup'],
            'config' => [
                'fields' => []
            ]
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
}