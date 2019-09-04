<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\BaseWorker;
use EzSystems\EzPlatformGraphQL\Schema\Worker;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;

class DefineDomainContentFilter extends BaseWorker implements Worker
{
    public function work(Builder $schema, array $args)
    {
        $schema->addType(new Input\Type($this->filterType($args), 'input-object'));
        $schema->addArgToField(
            $this->groupType($args),
            $this->connectionField($args),
            new Input\Arg('filter', $this->filterType($args))
        );
    }

    public function canWork(Builder $schema, array $args)
    {
        return isset($args['ContentTypeGroup']) && $args['ContentTypeGroup'] instanceof ContentTypeGroup
               && isset($args['ContentType']) && $args['ContentType'] instanceof ContentType
               && !$schema->hasType($this->filterType($args));
    }

    protected function filterType(array $args): string
    {
        return $this->getNameHelper()->filterType($args['ContentType']);
    }

    protected function groupType(array $args): string
    {
        return $this->getNameHelper()->domainGroupName($args['ContentTypeGroup']);
    }

    protected function connectionField(array $args): string
    {
        return $this->getNameHelper()->domainContentCollectionField($args['ContentType']);
    }
}
