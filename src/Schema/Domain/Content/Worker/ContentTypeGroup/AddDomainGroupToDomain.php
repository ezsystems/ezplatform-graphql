<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentTypeGroup;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\BaseWorker;
use EzSystems\EzPlatformGraphQL\Schema\Worker;
use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;

final class AddDomainGroupToDomain extends BaseWorker implements Worker
{
    public function work(Builder $schema, array $args)
    {
        $contentTypeGroup = $args['ContentTypeGroup'];
        $schema->addFieldToType('Domain', new Input\Field(
            $this->fieldName($args), 
            $this->typeGroupName($args),
            [
                'description' => $contentTypeGroup->getDescription('eng-GB'),
                'resolve' => sprintf(
                    '@=resolver("ContentTypeGroupByIdentifier", ["%s"])',
                    $contentTypeGroup->identifier
                )
            ]
        ));
    }

    public function canWork(Builder $schema, array $args)
    {
        return
            isset($args['ContentTypeGroup'])
            && $args['ContentTypeGroup'] instanceof ContentTypeGroup
            && !$schema->hasTypeWithField('Domain', $this->fieldName($args));
    }

    private function fieldName($args): string
    {
        return $this->getNameHelper()->domainGroupField($args['ContentTypeGroup']);
    }

    private function typeGroupName($args): string
    {
        return $this->getNameHelper()->domainGroupName($args['ContentTypeGroup']);
    }
}