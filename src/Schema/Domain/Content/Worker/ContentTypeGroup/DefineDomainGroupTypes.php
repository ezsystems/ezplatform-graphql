<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentTypeGroup;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\BaseWorker;
use EzSystems\EzPlatformGraphQL\Schema\Worker;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;

/**
 * Defines the type that indexes the types from a group by identifier.
 * Example: 'DomainGroupContentTypes'
 */
class DefineDomainGroupTypes extends BaseWorker implements Worker
{
    public function work(Builder $schema, array $args)
    {
        $schema->addType(new Builder\Input\Type($this->typeName($args), 'object'));
    }

    public function canWork(Builder $schema, array $args)
    {
        return
            isset($args['ContentTypeGroup'])
            && $args['ContentTypeGroup'] instanceof ContentTypeGroup
            && !$schema->hasType($this->typeName($args));
    }

    private function typeName($args): string
    {
        return $this->getNameHelper()->domainGroupTypesName($args['ContentTypeGroup']);
    }
}