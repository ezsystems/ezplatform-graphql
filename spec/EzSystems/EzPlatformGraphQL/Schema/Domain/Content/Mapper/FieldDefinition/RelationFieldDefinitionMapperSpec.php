<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\RelationFieldDefinitionMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use PhpSpec\ObjectBehavior;

class RelationFieldDefinitionMapperSpec extends ObjectBehavior
{
    const DEF_LIMIT_SINGLE = 1;
    const DEF_LIMIT_MULTI = 5;
    const DEF_LIMIT_NONE = 0;

    function let(NameHelper $nameHelper, ContentTypeService $contentTypeService, FieldDefinitionMapper $innerMapper)
    {
        $this->beConstructedWith($innerMapper, $nameHelper, $contentTypeService);

        $articleContentType = new ContentType(['identifier' => 'article']);
        $folderContentType = new ContentType(['identifier' => 'folder']);
        $contentTypeService->loadContentTypeByIdentifier('article')->willReturn($articleContentType);
        $contentTypeService->loadContentTypeByIdentifier('folder')->willReturn($folderContentType);

        $nameHelper->domainContentName($articleContentType)->willReturn('ArticleContent');
        $nameHelper->domainContentName($folderContentType)->willReturn('FolderContent');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RelationFieldDefinitionMapper::class);
        $this->shouldHaveType(FieldDefinitionMapper::class);
    }

    function it_maps_single_selection_without_type_limitations_to_a_single_generic_content()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_SINGLE, []);
        $this->mapToFieldValueType($fieldDefinition)->shouldReturn('DomainContent');
    }

    function it_maps_single_selection_with_multiple_type_limitations_to_a_single_generic_content()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_SINGLE, ['article', 'blog_post']);
        $this->mapToFieldValueType($fieldDefinition)->shouldReturn('DomainContent');
    }

    function it_maps_single_selection_with_a_unique_type_limitations_to_a_single_item_of_that_type()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_SINGLE, ['article']);
        $this->mapToFieldValueType($fieldDefinition)->shouldReturn('ArticleContent');
    }

    function it_maps_multi_selection_without_type_limitations_to_an_array_of_generic_content()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_MULTI, []);
        $this->mapToFieldValueType($fieldDefinition)->shouldReturn('[DomainContent]');
    }

    function it_maps_multi_selection_with_multiple_type_limitations_to_an_array_of_generic_content()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_NONE, ['article', 'blog_post']);
        $this->mapToFieldValueType($fieldDefinition)->shouldReturn('[DomainContent]');
    }

    function it_maps_multi_selection_with_a_unique_type_limitations_to_an_array_of_that_type()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_MULTI, ['article']);
        $this->mapToFieldValueType($fieldDefinition)->shouldReturn('[ArticleContent]');
    }

    function it_delegates_the_field_definition_type_to_the_inner_mapper(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->createFieldDefinition();
        $innerMapper->mapToFieldDefinitionType($fieldDefinition)->willReturn('SomeFieldDefinition');
        $this->mapToFieldDefinitionType($fieldDefinition)->shouldReturn('SomeFieldDefinition');
    }

    function it_maps_multi_selection_to_resolve_multiple()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_MULTI);
        $this->mapToFieldValueResolver($fieldDefinition)->shouldReturn('@=resolver("DomainRelationFieldValue", [field, true])');
    }

    function it_maps_singl_selection_to_resolve_single()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_SINGLE);
        $this->mapToFieldValueResolver($fieldDefinition)->shouldReturn('@=resolver("DomainRelationFieldValue", [field, false])');
    }

    private function createFieldDefinition($selectionLimit = 0, $selectionContentTypes = [])
    {
        return new FieldDefinition([
            'fieldTypeIdentifier' => 'ezobjectrelationlist',
            'fieldSettings' => [
                'selectionContentTypes' => $selectionContentTypes,
            ],
            'validatorConfiguration' => [
                'RelationListValueValidator' => ['selectionLimit' => $selectionLimit]
            ],
        ]);
    }
}
