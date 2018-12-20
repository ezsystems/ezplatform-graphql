<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\FieldValueBuilder;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\FieldValueBuilder\BaseFieldValueBuilder;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use PhpSpec\ObjectBehavior;

class BaseFieldValueBuilderSpec extends ObjectBehavior
{
    const FIELD_IDENTIFIER = 'test';

    function it_is_initializable()
    {
        $this->shouldHaveType(BaseFieldValueBuilder::class);
    }

    function it_builder_ezauthor()
    {
        $fieldDefinition = $this->createFieldDefinition('ezauthor');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('[AuthorFieldValue]');
        $this->buildDefinition($fieldDefinition)->shouldBeResolvedWithFieldValueProperty('authors');
    }

    function it_builds_ezbinaryfile()
    {
        $fieldDefinition = $this->createFieldDefinition('ezbinaryfile');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('BinaryFileFieldValue');
        $this->buildDefinition($fieldDefinition)->shouldBeResolvedWithDefaultResolver();
    }

    function it_builds_ezboolean()
    {
        $fieldDefinition = $this->createFieldDefinition('ezboolean');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('Boolean');
        $this->buildDefinition($fieldDefinition)->shouldBeResolvedWithFieldValueProperty('bool');
    }

    function it_builds_ezcountry()
    {
        $fieldDefinition = $this->createFieldDefinition('ezcountry');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('String');
        $this->buildDefinition($fieldDefinition)->shouldBeResolvedWithDefaultResolver();
    }

    function it_builds_ezdate()
    {
        $fieldDefinition = $this->createFieldDefinition('ezdate');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('DateTime');
        $this->buildDefinition($fieldDefinition)->shouldBeResolvedWithFieldValueProperty('value');
    }

    function it_builds_ezdatetime()
    {
        $fieldDefinition = $this->createFieldDefinition('ezdatetime');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('DateTime');
        $this->buildDefinition($fieldDefinition)->shouldBeResolvedWithFieldValueProperty('value');
    }

    function it_builds_ezemail()
    {
        $fieldDefinition = $this->createFieldDefinition('ezemail');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('String');
        $this->buildDefinition($fieldDefinition)->shouldBeResolvedWithDefaultResolver();
    }

    function it_builds_ezfloat()
    {
        $fieldDefinition = $this->createFieldDefinition('ezfloat');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('Float');
        $this->buildDefinition($fieldDefinition)->shouldBeResolvedWithFieldValueProperty('value');
    }

    function it_builds_ezgmaplocation()
    {
        $fieldDefinition = $this->createFieldDefinition('ezgmaplocation');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('MapLocationFieldValue');
    }

    function it_builds_ezimage()
    {
        $fieldDefinition = $this->createFieldDefinition('ezimage');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('ImageFieldValue');
    }

    function it_builds_ezimageasset()
    {
        $fieldDefinition = $this->createFieldDefinition('ezimageasset');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('ImageFieldValue');
        $this->buildDefinition($fieldDefinition)->shouldBeResolvedWithResolver('DomainImageAssetFieldValue');
    }

    function it_builds_ezinteger()
    {
        $fieldDefinition = $this->createFieldDefinition('ezinteger');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('Int');
        $this->buildDefinition($fieldDefinition)->shouldBeResolvedWithFieldValueProperty('value');
    }

    function it_builds_ezkeyword()
    {
        $fieldDefinition = $this->createFieldDefinition('ezkeyword');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('[String]');
        $this->buildDefinition($fieldDefinition)->shouldBeResolvedWithFieldValueProperty('values');
    }

    function it_builds_ezmedia()
    {
        $fieldDefinition = $this->createFieldDefinition('ezmedia');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('MediaFieldValue');
    }

    function it_builds_ezobjectrelation()
    {
        $fieldDefinition = $this->createFieldDefinition('ezobjectrelation');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('RelationFieldValue');
    }

    /**
     * @todo it isn't used, as there is a field value builder for it
     */
    function it_builds_ezobjectrelationlist()
    {
        $fieldDefinition = $this->createFieldDefinition('ezobjectrelationlist');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('RelationListFieldValue');
    }

    function it_builds_ezrichtext()
    {
        $fieldDefinition = $this->createFieldDefinition('ezrichtext');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('RichTextFieldValue');
    }

    function it_builds_ezstring()
    {
        $fieldDefinition = $this->createFieldDefinition('ezstring');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('String');
    }

    function it_builds_eztext()
    {
        $fieldDefinition = $this->createFieldDefinition('eztext');

        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('String');
    }

    public function getMatchers(): array
    {
        return [
            'haveGraphQLType' => function(array $definition, $type) {
                return $definition['type'] === $type;
            },
            'beResolvedWithFieldValueProperty' => function(array $definition, $property) {
                return $definition['resolve'] === sprintf(
                    '@=resolver("DomainFieldValue", [value, "%s"]).%s',
                    self::FIELD_IDENTIFIER,
                    $property
                );
            },
            'beResolvedWithDefaultResolver' => function(array $definition) {
                return $definition['resolve'] === sprintf(
                    '@=resolver("DomainFieldValue", [value, "%s"])',
                    self::FIELD_IDENTIFIER
                );
            },
            'beResolvedWithResolver' => function(array $definition, $resolver) {
                return 0 === strpos(
                    $definition['resolve'],
                    sprintf('@=resolver("%s"', $resolver)
                );
            },
        ];
    }

    /**
     * @return FieldDefinition
     */
    protected function createFieldDefinition($fieldTypeIdentifier): FieldDefinition
    {
        return new FieldDefinition([
            'identifier' => self::FIELD_IDENTIFIER,
            'fieldTypeIdentifier' => $fieldTypeIdentifier,
        ]);
    }
}
