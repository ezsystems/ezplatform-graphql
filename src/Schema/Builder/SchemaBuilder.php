<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Builder;

use EzSystems\EzPlatformGraphQL\Schema\Builder as SchemaBuilderInterface;
use Ibexa\GraphQL\Schema\Domain\NameValidator;

class SchemaBuilder implements SchemaBuilderInterface
{
    private $schema = [];

    /** @var \Ibexa\GraphQL\Schema\Domain\NameValidator */
    private $nameValidator;

    public function __construct(NameValidator $nameValidator)
    {
        $this->nameValidator = $nameValidator;
    }

    public function getSchema(): array
    {
        return $this->schema;
    }

    public function addType(Input\Type $typeInput)
    {
        if (!$this->nameValidator->isValidName($typeInput->name)) {
            $this->nameValidator->generateInvalidNameWarning($typeInput->type, $typeInput->name);

            return;
        }

        if ($this->hasType($typeInput->name)) {
            throw new \Exception("The type $typeInput->name is already defined");
        }

        $type = ['type' => $typeInput->type];
        if (!empty($typeInput->inherits)) {
            $type['inherits'] = is_array($typeInput->inherits)
                ? $typeInput->inherits
                : [$typeInput->inherits];
        }
        if (!empty($typeInput->interfaces)) {
            $type['config']['interfaces'] = is_array($typeInput->interfaces)
                ? $typeInput->interfaces :
                [$typeInput->interfaces];
        }

        if (isset($typeInput->nodeType)) {
            $type['config']['nodeType'] = $typeInput->nodeType;
        }
        $this->schema[$typeInput->name] = $type;
    }

    public function addFieldToType($type, Input\Field $fieldInput)
    {
        if (!$this->nameValidator->isValidName($fieldInput->name)) {
            $this->nameValidator->generateInvalidNameWarning($fieldInput->type, $fieldInput->name);

            return;
        }

        if (!$this->hasType($type)) {
            throw new \Exception("Expected type $type to be defined, but it was not");
        }

        if ($this->hasTypeWithField($type, $fieldInput->name)) {
            throw new \Exception("Type $type already has a field named $fieldInput->name");
        }

        $field = ['type' => $fieldInput->type];
        if (!empty($fieldInput->description)) {
            $field['description'] = $fieldInput->description;
        }
        if (isset($fieldInput->resolve)) {
            $field['resolve'] = $fieldInput->resolve;
        }
        if (isset($fieldInput->argsBuilder)) {
            $field['argsBuilder'] = $fieldInput->argsBuilder;
        }

        $this->schema[$type]['config']['fields'][$fieldInput->name] = $field;
    }

    public function addArgToField($type, $field, Input\Arg $argInput)
    {
        if (!$this->hasType($type)) {
            throw new \Exception("Expected type $type to be defined, but it was not");
        }

        if (!$this->hasTypeWithField($type, $field)) {
            throw new \Exception("Type $type already has a field named $field");
        }

        if ($this->hasTypeFieldWithArg($type, $field, $argInput->name)) {
            throw new \Exception("The field $field from type $type already has an argument $argInput->name");
        }

        $arg = ['type' => $argInput->type];
        if (!empty($argInput->description)) {
            $arg['description'] = $argInput->description;
        }

        if (!empty($argInput->defaultValue)) {
            $arg['defaultValue'] = $argInput->defaultValue;
        }

        $this->schema[$type]['config']['fields'][$field]['args'][$argInput->name] = $arg;
    }

    public function addValueToEnum($enum, Input\EnumValue $valueInput)
    {
        if (!$this->hasType($enum)) {
            throw new \Exception("Expected type $enum to be defined, but it was not");
        }

        if (!empty($valueInput->value)) {
            $enumValue['value'] = $valueInput->value;
        }

        if (!empty($valueInput->description)) {
            $enumValue['description'] = $valueInput->description;
        }

        if (!isset($enumValue)) {
            $enumValue = [];
        }

        $this->schema[$enum]['config']['values'][$valueInput->name] = $enumValue;
    }

    /**
     * @param string $type
     */
    public function hasType($type): bool
    {
        return isset($this->schema[$type]);
    }

    /**
     * @param string $type
     * @param string $field
     */
    public function hasTypeWithField($type, $field): bool
    {
        return
            $this->hasType($type)
            && isset($this->schema[$type]['config']['fields'][$field]);
    }

    /**
     * @param string $type
     * @param string $field
     * @param $arg
     */
    public function hasTypeFieldWithArg($type, $field, $arg): bool
    {
        return
            $this->hasTypeWithField($type, $field)
            && isset($this->schema[$type]['config']['fields'][$field]['args'][$arg]);
    }

    /**
     * @param string $enum
     */
    public function hasEnum($enum): bool
    {
        return $this->hasType($enum);
    }
}
