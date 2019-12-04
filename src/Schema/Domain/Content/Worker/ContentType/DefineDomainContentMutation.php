<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\BaseWorker;
use EzSystems\EzPlatformGraphQL\Schema\Initializer;
use EzSystems\EzPlatformGraphQL\Schema\Worker;

class DefineDomainContentMutation extends BaseWorker implements Worker, Initializer
{
    const MUTATION_TYPE = 'DomainContentMutation';

    public function init(Builder $schema)
    {
        $schema->addType(new Builder\Input\Type(
            self::MUTATION_TYPE,
            'object',
            ['inherits' => ['PlatformMutation']]
        ));
    }

    public function work(Builder $schema, array $args)
    {
        $contentType = $args['ContentType'];

        // ex: createArticle
        $schema->addFieldToType(self::MUTATION_TYPE,
            new Builder\Input\Field(
                $this->getCreateField($contentType),
                $this->getNameHelper()->domainContentName($contentType) . '!',
                [
                    'resolve' => sprintf(
                        '@=mutation("CreateDomainContent", [args["input"], "%s", args["parentLocationId"], args["language"]])',
                        $contentType->identifier
                ), ]
            )
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getCreateField($contentType),
            new Builder\Input\Arg('input', $this->getCreateInputName($contentType) . '!')
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getCreateField($contentType),
            $this->buildLanguageFieldInput()
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getCreateField($contentType),
            new Builder\Input\Arg('parentLocationId', 'Int!')
        );

        $schema->addType(new Builder\Input\Type($this->getCreateInputName($contentType), 'input-object'));

        // Update mutation field
        $schema->addFieldToType(
            self::MUTATION_TYPE,
            new Builder\Input\Field(
                $this->getUpdateField($contentType),
                $this->getNameHelper()->domainContentName($contentType) . '!',
                ['resolve' => '@=mutation("UpdateDomainContent", [args["input"], args, args["versionNo"], args["language"]])']
            )
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getUpdateField($contentType),
            new Builder\Input\Arg('input', $this->getUpdateInputName($contentType) . '!')
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getUpdateField($contentType),
            $this->buildLanguageFieldInput()
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getUpdateField($contentType),
            new Builder\Input\Arg('id', 'ID', ['description' => 'ID of the content item to update'])
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getUpdateField($contentType),
            new Builder\Input\Arg('contentId', 'Int', ['description' => 'Repository content ID of the content item to update'])
        );

        $schema->addArgToField(
            self::MUTATION_TYPE,
            $this->getUpdateField($contentType),
            new Builder\Input\Arg('versionNo', 'Int', ['description' => 'Optional version number to update. If it is a draft, it is saved, not published. If it is archived, it is used as the source version for the update, to complete missing fields.'])
        );

        $schema->addType(new Builder\Input\Type($this->getUpdateInputName($contentType), 'input-object'));
    }

    public function canWork(Builder $schema, array $args)
    {
        return isset($args['ContentType'])
               && $args['ContentType'] instanceof ContentType
               && !isset($args['FieldDefinition'])
               && !$schema->hasType($this->getCreateInputName($args['ContentType']));
    }

    /**
     * @param $contentType
     */
    protected function getCreateInputName($contentType): string
    {
        return $this->getNameHelper()->domainContentCreateInputName($contentType);
    }

    /**
     * @param $contentType
     */
    protected function getUpdateInputName($contentType): string
    {
        return $this->getNameHelper()->domainContentUpdateInputName($contentType);
    }

    /**
     * @param $contentType
     */
    protected function getCreateField($contentType): string
    {
        return $this->getNameHelper()->domainMutationCreateContentField($contentType);
    }

    /**
     * @param $contentType
     */
    protected function getUpdateField($contentType): string
    {
        return $this->getNameHelper()->domainMutationUpdateContentField($contentType);
    }

    private function buildLanguageFieldInput(): Builder\Input\Arg
    {
        return new Builder\Input\Arg(
            'language',
            'RepositoryLanguage!',
            ['description' => 'The language the content should be created/updated in.']
        );
    }
}
