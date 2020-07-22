<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\GraphQL\Mutation;

use eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException;
use eZ\Publish\API\Repository\Exceptions\ContentValidationException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Repository\Values\User\UserCreateStruct;
use EzSystems\EzPlatformUser\ConfigResolver\RegistrationContentTypeLoader;
use EzSystems\EzPlatformUser\ConfigResolver\RegistrationGroupLoader;
use eZ\Publish\Core\FieldType\User as UserFieldType;
use GraphQL\Error\UserError;

class User
{
    /**
     * @var \eZ\Publish\API\Repository\UserService
     */
    private $userService;

    /** @var \EzSystems\EzPlatformUser\ConfigResolver\RegistrationContentTypeLoader */
    private $userTypeLoader;

    /** @var \EzSystems\EzPlatformUser\ConfigResolver\RegistrationGroupLoader */
    private $registrationGroupLoader;

    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var array */
    private $fieldInputHandlers;

    public function __construct(
        Repository $repository,
        UserService $userService,
        RegistrationContentTypeLoader $userTypeLoader,
        RegistrationGroupLoader $registrationGroupLoader,
        array $fieldInputHandlers = []
    )
    {
        $this->userService = $userService;
        $this->userTypeLoader = $userTypeLoader;
        $this->registrationGroupLoader = $registrationGroupLoader;
        $this->repository = $repository;
        $this->fieldInputHandlers = $fieldInputHandlers;
    }

    public function registerAccount($args)
    {
        $profile = $args['input'];
        $contentType = $this->userTypeLoader->loadContentType();

        $userCreateStruct = $this->userService->newUserCreateStruct(
            $profile['account']['username'],
            $profile['account']['email'],
            $profile['account']['password'],
            'eng-GB'
        );

        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->fieldTypeIdentifier === 'ezuser') {
                /*$userCreateStruct->setField($fieldDefinition->identifier, [
                    'login' => $profile['account']['username'],
                    'password' => $profile['account']['password'],
                    'email' => $profile['account']['email'],
                    'enabled' => false,
                ]);*/
            }

            if (isset($profile[$fieldDefinition->identifier])) {
                $userCreateStruct->setField(
                    $fieldDefinition->identifier,
                    $this->getInputFieldValue($profile[$fieldDefinition->identifier], $fieldDefinition)
                );
            }
        }
        $parentGroups = [$this->registrationGroupLoader->loadGroup()];

        try {
            return $this->repository->sudo(function (Repository $repository) use ($userCreateStruct, $parentGroups) {
                $repository->getUserService()->createUser($userCreateStruct, $parentGroups);
            });
        } catch (ContentFieldValidationException $e) {
            $this->throwContentFieldValidationError($e);
        }
    }

    private function getInputFieldValue($fieldInput, FieldDefinition $fieldDefinition)
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

    public function requestPasswordReset($args)
    {

    }

    public function resetPassword($args)
    {

    }

    private function throwContentFieldValidationError(ContentFieldValidationException $e)
    {
        $errors = [];
        foreach ($e->getFieldErrors() as $fieldId => $errorsForField) {
            if (isset($errorsForField['eng-GB'])) {
                $errors[] = $errorsForField['eng-GB']->getTranslatableMessage();
            }
        }

        throw new UserError(implode(', ', $errors));
    }
}
