<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\GraphQL\Mutation;

use eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\FieldType\ValidationError;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use EzSystems\EzPlatformUser\ConfigResolver\RegistrationContentTypeLoader;
use EzSystems\EzPlatformUser\ConfigResolver\RegistrationGroupLoader;
use Overblog\GraphQLBundle\Error\UserErrors;

class User
{
    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    /** @var \EzSystems\EzPlatformUser\ConfigResolver\RegistrationContentTypeLoader */
    private $userTypeLoader;

    /** @var \EzSystems\EzPlatformUser\ConfigResolver\RegistrationGroupLoader */
    private $registrationGroupLoader;

    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var array */
    private $fieldInputHandlers;

    /** @var \EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper */
    private $nameHelper;

    public function __construct(
        Repository $repository,
        UserService $userService,
        RegistrationContentTypeLoader $userTypeLoader,
        RegistrationGroupLoader $registrationGroupLoader,
        NameHelper $nameHelper,
        array $fieldInputHandlers = []
    )
    {
        $this->userService = $userService;
        $this->userTypeLoader = $userTypeLoader;
        $this->registrationGroupLoader = $registrationGroupLoader;
        $this->repository = $repository;
        $this->fieldInputHandlers = $fieldInputHandlers;
        $this->nameHelper = $nameHelper;
    }

    public function registerAccount($args)
    {
        $profile = $args['input'];
        $contentType = $this->userTypeLoader->loadContentType();

        $userCreateStruct = $this->userService->newUserCreateStruct(
            $profile['username'],
            $profile['email'],
            $profile['password'],
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

            if ($value = $this->getInputFieldValue($profile, $fieldDefinition)) {
                $userCreateStruct->setField($fieldDefinition->identifier, $value);
            }
        }
        $parentGroups = [$this->registrationGroupLoader->loadGroup()];

        try {
            $return = $this->repository->sudo(function (Repository $repository) use ($userCreateStruct, $parentGroups) {
                return $repository->getUserService()->createUser($userCreateStruct, $parentGroups);
            });

            return $return;
        } catch (ContentFieldValidationException $e) {
            $this->throwContentFieldValidationError($e);
        }
    }

    private function getInputFieldValue(array $profile, FieldDefinition $fieldDefinition)
    {
        if (!isset($this->fieldInputHandlers[$fieldDefinition->fieldTypeIdentifier])) {
            return null;
        }

        $inputKey = $this->nameHelper->fieldDefinitionField($fieldDefinition);
        if (!isset($profile[$inputKey])) {
            return null;
        }

        $fieldInput = $profile[$inputKey];

        $format = null;
        if (isset($fieldInput['input'])) {
            $input = $fieldInput['input'];
            $format = $fieldInput['format'] ?? null;
        } else {
            $input = $fieldInput;
        }

        return $this->fieldInputHandlers[$fieldDefinition->fieldTypeIdentifier]->toFieldValue($input, $format);
    }

    public function requestPasswordReset($args)
    {

    }

    public function resetPassword($args)
    {

    }

    /**
     * @todo centralize handling of that exception so that it can be used for content mutations as well.
     */
    private function throwContentFieldValidationError(ContentFieldValidationException $e)
    {
        $errors = [];
        foreach ($e->getFieldErrors() as $errorsPerLanguage) {
            if (isset($errorsPerLanguage['eng-GB'])) {
                if (is_array($errorsPerLanguage['eng-GB'])) {
                    $errors = array_merge(
                        $errors,
                        array_map(
                            function(ValidationError $validationError) {
                                return (string)$validationError->getTranslatableMessage();
                            },
                            $errorsPerLanguage['eng-GB']
                        )
                    );
                } else {
                    $errors[] = (string)$errorsPerLanguage['eng-GB']->getTranslatableMessage();
                }
            }
        }

        throw new UserErrors($errors);
    }
}
