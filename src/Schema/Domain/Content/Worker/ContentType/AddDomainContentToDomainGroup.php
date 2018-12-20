<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\BaseWorker;
use EzSystems\EzPlatformGraphQL\Schema\Worker;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;

class AddDomainContentToDomainGroup extends BaseWorker implements Worker
{
    public function work(Builder $schema, array $args)
    {
        $contentType = $args['ContentType'];
        $descriptions = $contentType->getDescriptions();

        $schema->addFieldToType($this->groupName($args), new Input\Field(
            $this->typeField($args), $this->typeName($args),
            [
                'description' => isset($descriptions['eng-GB']) ? $descriptions['eng-GB'] : 'No description available',
                'resolve' => sprintf('@=resolver("DomainContentItem", [args, "%s"])', $contentType->identifier)
            ]
        ));

        $schema->addArgToField($this->groupName($args), $this->typeField($args), new Input\Arg(
            'id', 'Int',
            ['description' => sprintf('A %s content id', $contentType->identifier)]
        ));

        $schema->addArgToField($this->groupName($args), $this->typeField($args), new Input\Arg(
            'remoteId', 'String',
            ['description' => sprintf('A %s content remote id', $contentType->identifier)]
        ));

        $schema->addArgToField($this->groupName($args), $this->typeField($args), new Input\Arg(
            'locationId', 'Int',
            ['description' => sprintf('A %s content location id', $contentType->identifier)]
        ));
    }

    public function canWork(Builder $schema, array $args)
    {
        return
            isset($args['ContentType'])
            && $args['ContentType'] instanceof ContentType
            && isset($args['ContentTypeGroup'])
            && $args['ContentTypeGroup'] instanceof ContentTypeGroup
            && !$schema->hasTypeWithField($this->groupName($args), $this->typeField($args));
    }

    protected function groupName(array $args): string
    {
        return $this->getNameHelper()->domainGroupName($args['ContentTypeGroup']);
    }

    protected function typeField($args): string
    {
        return $this->getNameHelper()->domainContentField($args['ContentType']);
    }

    protected function typeName($args): string
    {
        return $this->getNameHelper()->domainContentName($args['ContentType']);
    }
}