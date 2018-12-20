<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Builder;

use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use EzSystems\EzPlatformGraphQL\Schema\Builder\SchemaBuilder;
use PhpSpec\ObjectBehavior;

class SchemaBuilderSpec extends ObjectBehavior
{
    const TYPE = 'Test';
    const TYPE_TYPE = 'object';

    const FIELD = 'field';
    const FIELD_TYPE = 'string';

    const ARG = 'arg';
    const ARG_TYPE = 'Boolean';

    function it_is_initializable()
    {
        $this->shouldHaveType(SchemaBuilder::class);
    }

    function it_adds_a_type_to_the_schema()
    {
        $this->addType($this->inputType('Parent', 'Interface'));

        $schema = $this->getSchema();

        $schema->shouldHaveGraphQLType();
        $schema->shouldHaveGraphQLTypeThatInherits('Parent');
        $schema->shouldHaveGraphQLTypeThatImplements('Interface');
    }

    function it_adds_a_field_to_an_existing_type()
    {
        $this->addType($this->inputType());
        $this->addFieldToType(self::TYPE,
            $this->inputField('Description', '@=resolver("myresolver")')
        );

        $schema = $this->getSchema();
        $schema->shouldHaveGraphQLType();
        $schema->shouldHaveGraphQLTypeField();
        $schema->shouldHaveGraphQLTypeFieldWithDescription('Description');
        $schema->shouldHaveGraphQLTypeFieldWithResolve('@=resolver("myresolver")');
    }

    function it_adds_an_argument_to_an_existing_type_field()
    {
        $this->addType($this->inputType());
        $this->addFieldToType(self::TYPE, $this->inputField());
        $this->addArgToField(self::TYPE, self::FIELD, $this->inputArg('Description'));

        $schema = $this->getSchema();
        $schema->shouldHaveGraphQLType();
        $schema->shouldHaveGraphQLTypeField();
        $schema->shouldHaveGraphQLTypeFieldArg();
        $schema->shouldHaveGraphQLTypeFieldArgWithDescription('Description');
    }

    public function getMatchers(): array
    {
        return [
            'haveGraphQLType' => function (array $schema) {
                return
                    isset($schema[self::TYPE]['type'])
                    && $schema[self::TYPE]['type'] === self::TYPE_TYPE;
            },
            'haveGraphQLTypeThatInherits' => function (array $schema, $inherits) {
                return
                    isset($schema[self::TYPE]['inherits'])
                    && in_array($inherits, $schema[self::TYPE]['inherits']);
            },
            'haveGraphQLTypeThatImplements' => function (array $schema, $interface) {
                return
                    isset($schema[self::TYPE]['config']['interfaces'])
                    && in_array($interface, $schema[self::TYPE]['config']['interfaces']);
            },
            'haveGraphQLTypeField' => function (array $schema) {
                return
                    isset($schema[self::TYPE]['config']['fields'][self::FIELD]['type'])
                    && $schema[self::TYPE]['config']['fields'][self::FIELD]['type'] === self::FIELD_TYPE;
            },
            'haveGraphQLTypeFieldWithDescription' => function (array $schema, $description) {
                return
                    isset($schema[self::TYPE]['config']['fields'][self::FIELD]['description'])
                    && $schema[self::TYPE]['config']['fields'][self::FIELD]['description'] === $description;
            },
            'haveGraphQLTypeFieldWithResolve' => function (array $schema, $resolve) {
                return
                    isset($schema[self::TYPE]['config']['fields'][self::FIELD]['description'])
                    && $schema[self::TYPE]['config']['fields'][self::FIELD]['resolve'] === $resolve;
            },
            'haveGraphQLTypeFieldArg' => function (array $schema) {
                return
                    isset($schema[self::TYPE]['config']['fields'][self::FIELD]['args'][self::ARG]['type'])
                    && $schema[self::TYPE]['config']['fields'][self::FIELD]['args'][self::ARG]['type'] === self::ARG_TYPE;
            },
            'haveGraphQLTypeFieldArgWithDescription' => function (array $schema, $description) {
                return
                    isset($schema[self::TYPE]['config']['fields'][self::FIELD]['args'][self::ARG]['description'])
                    && $schema[self::TYPE]['config']['fields'][self::FIELD]['args'][self::ARG]['description'] === $description;
            },
        ];
    }

    protected function inputType($inherits = [], $interfaces = []): Input\Type
    {
        return new Input\Type(
            self::TYPE, self::TYPE_TYPE,
            [
                'inherits' => $inherits,
                'interfaces' => $interfaces
            ]
        );
    }

    protected function inputField($description = null, $resolve = null): Input\Field
    {
        $input = new Input\Field(self::FIELD, self::FIELD_TYPE);

        if (isset($description)) {
            $input->description = $description;
        }

        if (isset($resolve)) {
            $input->resolve = $resolve;
        }

        return $input;
    }

    protected function inputArg($description): Input\Arg
    {
        $input = new Input\Arg(self::ARG, self::ARG_TYPE);

        if (isset($description)) {
            $input->description = $description;
        }

        return $input;
    }
}
