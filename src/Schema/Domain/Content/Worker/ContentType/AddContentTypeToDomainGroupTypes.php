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

class AddContentTypeToDomainGroupTypes extends BaseWorker implements Worker
{
    public function work(Builder $schema, array $args)
    {
        $resolve = sprintf(
            '@=resolver("ContentType", [{"identifier": "%s"}])',
            $args['ContentType']->identifier
        );

        $schema->addFieldToType(
            $this->groupTypesName($args),
            new Input\Field(
                $this->typeField($args),
                $this->typeName($args),
                ['resolve' => $resolve]
            )
        );
    }

    public function canWork(Builder $schema, array $args)
    {
        return
            isset($args['ContentType'])
            && $args['ContentType'] instanceof ContentType
            && isset($args['ContentTypeGroup'])
            && $args['ContentTypeGroup'] instanceof ContentTypeGroup
            && !$schema->hasTypeWithField($this->groupTypesName($args), $this->typeField($args));
    }

    protected function typeField(array $args): string
    {
        return $this->getNameHelper()->domainContentField($args['ContentType']);
    }

    protected function groupTypesName(array $args): string
    {
        return $this->getNameHelper()->domainGroupTypesName($args['ContentTypeGroup']);
    }

    protected function typeName(array $args): string
    {
        return $this->getNameHelper()->domainContentTypeName($args['ContentType']);
    }
}
