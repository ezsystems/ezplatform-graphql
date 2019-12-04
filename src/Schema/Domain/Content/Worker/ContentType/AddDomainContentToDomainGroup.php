<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\BaseWorker;
use EzSystems\EzPlatformGraphQL\Schema\Worker;

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
                'resolve' => sprintf('@=resolver("DomainContentItem", [args, "%s"])', $contentType->identifier),
            ]
        ));

        $schema->addArgToField($this->groupName($args), $this->typeField($args), new Input\Arg(
            'id', 'Int',
            ['description' => sprintf('Content ID of the %s', $contentType->identifier)]
        ));

        $schema->addArgToField($this->groupName($args), $this->typeField($args), new Input\Arg(
            'remoteId', 'String',
            ['description' => sprintf('Content remote ID of the %s', $contentType->identifier)]
        ));

        $schema->addArgToField($this->groupName($args), $this->typeField($args), new Input\Arg(
            'locationId', 'Int',
            ['description' => sprintf('Location ID of the %s', $contentType->identifier)]
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
