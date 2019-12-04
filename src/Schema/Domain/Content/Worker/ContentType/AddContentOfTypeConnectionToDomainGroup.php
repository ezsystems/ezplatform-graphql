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

class AddContentOfTypeConnectionToDomainGroup extends BaseWorker implements Worker
{
    public function work(Builder $schema, array $args)
    {
        $contentType = $args['ContentType'];
        $descriptions = $contentType->getDescriptions();

        $schema->addFieldToType($this->groupName($args), new Input\Field(
            $this->connectionField($args),
            $this->connectionType($args),
            [
                'description' => isset($descriptions['eng-GB']) ? $descriptions['eng-GB'] : 'No description available',
                'resolve' => sprintf(
                    '@=resolver("SearchContentOfTypeAsConnection", ["%s", args])',
                    $contentType->identifier
                ),
                'argsBuilder' => 'Relay::Connection',
            ]
        ));

        $schema->addArgToField($this->groupName($args), $this->connectionField($args), new Input\Arg(
            'query', 'ContentSearchQuery',
            ['description' => 'A Content query used to filter results']
        ));

        $schema->addArgToField($this->groupName($args), $this->connectionField($args), new Input\Arg(
            'sortBy', '[SortByOptions]',
            ['description' => 'A sort clause, or array of clauses. Add _desc after a clause to reverse it']
        ));
    }

    public function canWork(Builder $schema, array $args)
    {
        return
            isset($args['ContentType'])
            && $args['ContentType'] instanceof ContentType
            && isset($args['ContentTypeGroup'])
            && $args['ContentTypeGroup'] instanceof ContentTypeGroup
            && !$schema->hasTypeWithField($this->groupName($args), $this->connectionField($args));
    }

    protected function groupName(array $args): string
    {
        return $this->getNameHelper()->domainGroupName($args['ContentTypeGroup']);
    }

    protected function connectionField(array $args): string
    {
        return $this->getNameHelper()->domainContentCollectionField($args['ContentType']);
    }

    protected function connectionType(array $args): string
    {
        return $this->getNameHelper()->domainContentConnection($args['ContentType']);
    }

    protected function typeName($args): string
    {
        return $this->getNameHelper()->domainContentName($args['ContentType']);
    }
}
