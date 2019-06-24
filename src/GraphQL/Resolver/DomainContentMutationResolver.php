<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository as API;
use eZ\Publish\API\Repository\Exceptions as RepositoryExceptions;
use eZ\Publish\API\Repository\Values as RepositoryValues;
use EzSystems\EzPlatformGraphQL\Exception\UnsupportedFieldTypeException;
use EzSystems\EzPlatformGraphQL\GraphQL\Mutation\InputHandler\FieldTypeInputHandler;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserErrors;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * @internal
 */
class DomainContentMutationResolver
{
    /**
     * @var API\Repository
     */
    private $repository;

    /**
     * @var FieldTypeInputHandler[]
     */
    private $fieldInputHandlers = [];
    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper
     */
    private $nameHelper;

    public function __construct(API\Repository $repository, array $fieldInputHandlers, NameHelper $nameHelper)
    {
        $this->repository = $repository;
        $this->fieldInputHandlers = $fieldInputHandlers;
        $this->nameHelper = $nameHelper;
    }

    public function updateDomainContent($input, Argument $args, $versionNo, $language): RepositoryValues\Content\Content
    {
        if (isset($args['id'])) {
            $idArray = GlobalId::fromGlobalId($args['id']);
            $contentId = $idArray['id'];
        } elseif (isset($args['contentId'])) {
            $contentId = $args['contentId'];
        } else {
            throw new UserError('One argument out of id or contentId is required');
        }

        try {
            $contentInfo = $this->getContentService()->loadContentInfo($contentId);
        } catch (RepositoryExceptions\NotFoundException $e) {
            throw new UserError("Content with id $contentId could not be loaded");
        } catch (RepositoryExceptions\UnauthorizedException $e) {
            throw new UserError('You are not authorized to load this content');
        }
        try {
            $contentType = $this->getContentTypeService()->loadContentType($contentInfo->contentTypeId);
        } catch (RepositoryExceptions\NotFoundException $e) {
            throw new UserError("Content type with id $contentInfo->contentTypeId could not be loaded");
        }

        $contentUpdateStruct = $this->getContentService()->newContentUpdateStruct();

        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            $inputFieldKey = $this->getInputField($fieldDefinition);
            if (isset($input[$inputFieldKey])) {
                try {
                    $contentUpdateStruct->setField(
                        $fieldDefinition->identifier,
                        $this->getInputFieldValue($input[$inputFieldKey], $fieldDefinition),
                        $language
                    );
                } catch (UnsupportedFieldTypeException $e) {
                    continue;
                }
            }
        }

        if (!isset($versionNo)) {
            try {
                $versionInfo = $this->getContentService()->createContentDraft($contentInfo)->versionInfo;
            } catch (RepositoryExceptions\UnauthorizedException $e) {
                throw new UserError('You are not authorized to create a draft of this content');
            }
        } else {
            try {
                $versionInfo = $this->getContentService()->loadVersionInfo($contentInfo, $versionNo);
            } catch (RepositoryExceptions\NotFoundException $e) {
                throw new UserError("Version $versionNo was not found");
            } catch (RepositoryExceptions\UnauthorizedException $e) {
                throw new UserError('You are not authorized to load this version');
            }
            if ($versionInfo->status !== RepositoryValues\Content\VersionInfo::STATUS_DRAFT) {
                try {
                    $versionInfo = $this->getContentService()->createContentDraft($contentInfo, $versionInfo)->versionInfo;
                } catch (RepositoryExceptions\UnauthorizedException $e) {
                    throw new UserError('You are not authorized to create a draft from this version');
                }
            }
        }

        try {
            $contentDraft = $this->getContentService()->updateContent($versionInfo, $contentUpdateStruct);
        } catch (RepositoryExceptions\ContentFieldValidationException $e) {
            throw new UserErrors($this->renderFieldValidationErrors($e, $contentType));
        } catch (RepositoryExceptions\ContentValidationException $e) {
            throw new UserError('The given input did not validate: ' . $e->getMessage());
        } catch (RepositoryExceptions\UnauthorizedException $e) {
            throw new UserError('You are not authorized to update this version');
        }
        try {
            $this->getContentService()->publishVersion($contentDraft->versionInfo);
        } catch (RepositoryExceptions\BadStateException $e) {
            throw new UserError("The version you're trying to publish is not a draft");
        } catch (RepositoryExceptions\UnauthorizedException $e) {
            throw new UserError('You are not authorized to publish this version');
        }

        return $this->getContentService()->loadContent($contentDraft->id);
    }

    public function createDomainContent($input, $contentTypeIdentifier, $parentLocationId, $language): RepositoryValues\Content\Content
    {
        try {
            $contentType = $this->getContentTypeService()->loadContentTypeByIdentifier($contentTypeIdentifier);
        } catch (API\Exceptions\NotFoundException $e) {
            throw new UserError($e->getMessage(), 0, $e);
        }
        $contentCreateStruct = $this->getContentService()->newContentCreateStruct($contentType, $language);
        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            $inputFieldKey = $this->getInputField($fieldDefinition);
            if (isset($input[$inputFieldKey])) {
                $contentCreateStruct->setField(
                    $fieldDefinition->identifier,
                    $this->getInputFieldValue($input[$inputFieldKey], $fieldDefinition),
                    $language
                );
            }
        }

        try {
            $contentDraft = $this->getContentService()->createContent(
                $contentCreateStruct,
                [$this->getLocationService()->newLocationCreateStruct($parentLocationId)]
            );
        } catch (RepositoryExceptions\ContentFieldValidationException $e) {
            throw new UserErrors($this->renderFieldValidationErrors($e, $contentType));
        } catch (\Exception $e) {
            throw new UserError($e->getMessage(), 0, $e);
        }

        try {
            $content = $this->getContentService()->publishVersion($contentDraft->versionInfo);
        } catch (\Exception $e) {
            throw new UserError($e->getMessage(), 0, $e);
        }

        return $content;
    }

    public function deleteDomainContent(Argument $args)
    {
        $globalId = null;

        if (isset($args['id'])) {
            $globalId = $args['id'];
            $idArray = GlobalId::fromGlobalId($args['id']);
            $contentId = $idArray['id'];
        } elseif (isset($args['contentId'])) {
            $contentId = $args['contentId'];
        } else {
            throw new UserError('One argument out of id or contentId is required');
        }

        try {
            $contentInfo = $this->getContentService()->loadContentInfo($contentId);
        } catch (API\Exceptions\NotFoundException $e) {
            throw new UserError("No content item was found with id $contentId");
        } catch (API\Exceptions\UnauthorizedException $e) {
            throw new UserError("You are not authorized to load the content item with id $contentId");
        }
        if (!isset($globalId)) {
            $globalId = GlobalId::toGlobalId(
                $this->resolveDomainContentType($contentInfo),
                $contentId
            );
        }

        try {
            $this->getContentService()->deleteContent($contentInfo);
        } catch (API\Exceptions\UnauthorizedException $e) {
            throw new UserError("You are not authorized to delete the content item with id $contentInfo->id");
        }

        return [
            'id' => $globalId,
            'contentId' => $contentId,
        ];
    }

    private function getInputFieldValue($fieldInput, RepositoryValues\ContentType\FieldDefinition $fieldDefinition)
    {
        if (isset($this->fieldInputHandlers[$fieldDefinition->fieldTypeIdentifier])) {
            $format = null;
            if (isset($fieldInput['input'])) {
                $input = $fieldInput['input'];
                $format = $fieldInput['format'] ?? null;
            } else {
                $input = $fieldInput;
            }

            return $this->fieldInputHandlers[$fieldDefinition->fieldTypeIdentifier]->toFieldValue($input, $format);
        }
    }

    public function resolveDomainContentType(RepositoryValues\Content\ContentInfo $contentInfo)
    {
        static $contentTypesMap = [], $contentTypesLoadErrors = [];

        if (!isset($contentTypesMap[$contentInfo->contentTypeId])) {
            try {
                $contentTypesMap[$contentInfo->contentTypeId] = $this->getContentTypeService()->loadContentType($contentInfo->contentTypeId);
            } catch (\Exception $e) {
                $contentTypesLoadErrors[$contentInfo->contentTypeId] = $e;
                throw $e;
            }
        }

        return $this->makeDomainContentTypeName($contentTypesMap[$contentInfo->contentTypeId]);
    }

    private function makeDomainContentTypeName(RepositoryValues\ContentType\ContentType $contentType)
    {
        $converter = new CamelCaseToSnakeCaseNameConverter(null, false);

        return $converter->denormalize($contentType->identifier) . 'Content';
    }

    /**
     * @return API\ContentService
     */
    private function getContentService()
    {
        return $this->repository->getContentService();
    }

    /**
     * @return API\ContentTypeService
     */
    private function getContentTypeService()
    {
        return $this->repository->getContentTypeService();
    }

    private function getLocationService()
    {
        return $this->repository->getLocationService();
    }

    private function renderFieldValidationErrors(RepositoryExceptions\ContentFieldValidationException $e, API\Values\ContentType\ContentType $contentType)
    {
        $errors = [];
        foreach ($e->getFieldErrors() as $fieldDefId => $fieldErrors) {
            $fieldDefinition = $contentType->getFieldDefinitionById($fieldDefId);
            foreach ($fieldErrors['eng-GB'] as $fieldError) {
                $translatableMessage = $fieldError->getTranslatableMessage();
                if ($translatableMessage instanceof API\Values\Translation\Plural) {
                    if (count($fieldError['values']) === 1) {
                        $message = $translatableMessage->singular;
                    } else {
                        $message = $translatableMessage->plural;
                    }
                } else {
                    $message = $translatableMessage->message;
                }
                $errors[] = sprintf("Field '%s' failed validation: %s",
                    $fieldDefinition->identifier,
                    $message
                );
            }
        }

        return $errors;
    }

    /**
     * Returns the GraphQL schema input field for a field definition.
     * Example: text_line -> textLine.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     *
     * @return string
     */
    private function getInputField(API\Values\ContentType\FieldDefinition $fieldDefinition)
    {
        return $this->nameHelper->fieldDefinitionField($fieldDefinition);
    }
}
