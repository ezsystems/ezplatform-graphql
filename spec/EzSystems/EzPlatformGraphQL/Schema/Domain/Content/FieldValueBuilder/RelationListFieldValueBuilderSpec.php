<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\FieldValueBuilder;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\FieldValueBuilder\RelationListFieldValueBuilder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RelationListFieldValueBuilderSpec extends ObjectBehavior
{
    const DEF_LIMIT_SINGLE = 1;
    const DEF_LIMIT_MULTI = 5;
    const DEF_LIMIT_NONE = 0;

    function let(NameHelper $nameHelper, ContentTypeService $contentTypeService)
    {
        $this->beConstructedWith($nameHelper, $contentTypeService);

        $articleContentType = new ContentType(['identifier' => 'article']);
        $folderContentType = new ContentType(['identifier' => 'folder']);
        $contentTypeService->loadContentTypeByIdentifier('article')->willReturn($articleContentType);
        $contentTypeService->loadContentTypeByIdentifier('folder')->willReturn($folderContentType);

        $nameHelper->domainContentName($articleContentType)->willReturn('ArticleContent');
        $nameHelper->domainContentName($folderContentType)->willReturn('FolderContent');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RelationListFieldValueBuilder::class);
    }

    function it_maps_single_selection_without_type_limitations_to_a_single_generic_content()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_SINGLE, []);
        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('DomainContent');
    }

    function it_maps_single_selection_with_multiple_type_limitations_to_a_single_generic_content()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_SINGLE, ['article', 'blog_post']);
        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('DomainContent');
    }

    function it_maps_single_selection_with_a_unique_type_limitations_to_a_single_item_of_that_type()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_SINGLE, ['article']);
        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('ArticleContent');
    }

    function it_maps_multi_selection_without_type_limitations_to_an_array_of_generic_content()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_MULTI, []);
        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('[DomainContent]');
    }

    function it_maps_multi_selection_with_multiple_type_limitations_to_an_array_of_generic_content()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_NONE, ['article', 'blog_post']);
        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('[DomainContent]');
    }

    function it_maps_multi_selection_with_a_unique_type_limitations_to_an_array_of_that_type()
    {
        $fieldDefinition = $this->createFieldDefinition(self::DEF_LIMIT_MULTI, ['article']);
        $this->buildDefinition($fieldDefinition)->shouldHaveGraphQLType('[ArticleContent]');
    }

    public function getMatchers(): array
    {
        return [
            'haveGraphQLType' => function(array $definition, $type) {
                return $definition['type'] === $type;
            },
        ];
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
