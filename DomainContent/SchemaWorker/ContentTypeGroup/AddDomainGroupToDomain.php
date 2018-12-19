<?php
namespace EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\ContentTypeGroup;

use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\BaseWorker;
use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\SchemaWorker;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use EzSystems\BehatBundle\Context\Object\ContentType;

final class AddDomainGroupToDomain extends BaseWorker implements SchemaWorker
{
    public function work(array &$schema, array $args)
    {
        $contentTypeGroup = $args['ContentTypeGroup'];
        $schema['Domain']['config']['fields'][$this->getGroupField($args['ContentTypeGroup'])] = [
            'type' => $this->getGroupName($args['ContentTypeGroup']),
            'description' => $contentTypeGroup->getDescription('eng-GB'),
            'resolve' => sprintf(
                "@=resolver(\"ContentTypeGroupByIdentifier\", [\"%s\"])",
                $args['ContentTypeGroup']->identifier
            ),
        ];
    }

    public function canWork(array $schema, array $args)
    {
        return isset($args['ContentTypeGroup']) && $args['ContentTypeGroup'] instanceof ContentTypeGroup
            && !isset($schema['Domain']['config']['fields'][$this->getGroupName($args['ContentTypeGroup'])]);
    }

    /**
     * @param ContentTypeGroup $contentTypeGroup
     * @return string
     */
    private function getGroupField(ContentTypeGroup $contentTypeGroup): string
    {
        return $this->getNameHelper()->domainGroupField($contentTypeGroup);
    }

    /**
     * @param ContentTypeGroup $contentTypeGroup
     * @return string
     */
    private function getGroupName(ContentTypeGroup $contentTypeGroup): string
    {
        return $this->getNameHelper()->domainGroupName($contentTypeGroup);
    }
}