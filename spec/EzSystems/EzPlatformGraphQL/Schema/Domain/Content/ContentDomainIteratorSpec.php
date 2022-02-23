<?php
namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content;

use Ibexa\GraphQL\Schema\Domain\NameValidator;
use spec\EzSystems\EzPlatformGraphQL\Tools\TypeArgument;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use EzSystems\EzPlatformGraphQL\Schema\Domain;
use EzSystems\EzPlatformGraphQL\Schema\Builder;

class ContentDomainIteratorSpec extends ObjectBehavior
{
    public function let(
        ContentTypeService $contentTypeService,
        NameValidator $nameValidator
    ) {
        $this->beConstructedWith($contentTypeService, $nameValidator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Domain\Iterator::class);
    }

    function it_initializes_the_schema_with_the_Platform_root_type(Builder $schema)
    {
        $this->init($schema);

        $schema->addType(
            Argument::allOf(
                TypeArgument::isNamed('Domain'),
                TypeArgument::inherits('Platform')
            )
        )->shouldHaveBeenCalled();
    }

    function it_yields_content_type_groups(ContentTypeService $contentTypeService)
    {
        $contentTypeService->loadContentTypeGroups()->willReturn([
            $group1 = new ContentTypeGroup(['identifier' => 'Group 1']),
            $group2 = new ContentTypeGroup(['identifier' => 'Group 2']),
            $group3 = new ContentTypeGroup(['identifier' => 'Group 3']),
        ]);
        $contentTypeService->loadContentTypes(Argument::any())->willReturn([]);

        $this->iterate()->shouldYieldLike(
            new \ArrayIterator([
                ['ContentTypeGroup' => $group1],
                ['ContentTypeGroup' => $group2],
                ['ContentTypeGroup' => $group3],
            ])
        );
    }

    function it_yields_content_types_with_their_group_from_a_content_type_group(
        ContentTypeService $contentTypeService,
        NameValidator $nameValidator
    ) {
        $nameValidator->isValidName(Argument::any())->willReturn(true);

        $contentTypeService->loadContentTypeGroups()->willReturn([
            $group = new ContentTypeGroup(['identifier' => 'Group']),
        ]);
        $contentTypeService->loadContentTypes(Argument::any())->willReturn([
            $type1 = new ContentType(['identifier' => 'type 1']),
            $type2 = new ContentType(['identifier' => 'type 2']),
            $type3 = new ContentType(['identifier' => 'type 3']),
        ]);

        $this->iterate()->shouldYieldLike(
            new \ArrayIterator([
                ['ContentTypeGroup' => $group],
                ['ContentTypeGroup' => $group, 'ContentType' => $type1],
                ['ContentTypeGroup' => $group, 'ContentType' => $type2],
                ['ContentTypeGroup' => $group, 'ContentType' => $type3],
            ])
        );
    }

    function it_yields_fields_definitions_with_their_content_types_and_group_from_a_content_type(
        ContentTypeService $contentTypeService,
        NameValidator $nameValidator
    ) {
        $nameValidator->isValidName(Argument::any())->willReturn(true);

        $contentTypeService->loadContentTypeGroups()->willReturn([
            $group = new ContentTypeGroup(['identifier' => 'Group']),
        ]);
        $contentTypeService->loadContentTypes(Argument::any())->willReturn([
            $type = new ContentType([
                'identifier' => 'type',
                'fieldDefinitions' => [
                    'field1' => $field1 = new FieldDefinition(['identifier' => 'foo']),
                    'field2' => $field2 = new FieldDefinition(['identifier' => 'bar']),
                    'field3' => $field3 = new FieldDefinition(['identifier' => 'faz']),
                ]
            ]),
        ]);

        $this->iterate()->shouldYieldLike(
            new \ArrayIterator([
                ['ContentTypeGroup' => $group],
                ['ContentTypeGroup' => $group, 'ContentType' => $type],
                ['ContentTypeGroup' => $group, 'ContentType' => $type, 'FieldDefinition' => $field1],
                ['ContentTypeGroup' => $group, 'ContentType' => $type, 'FieldDefinition' => $field2],
                ['ContentTypeGroup' => $group, 'ContentType' => $type, 'FieldDefinition' => $field3],
            ])
        );
    }

    function it_only_yields_fields_definitions_from_the_current_content_type(
        ContentTypeService $contentTypeService,
        NameValidator $nameValidator
    ) {
        $nameValidator->isValidName(Argument::any())->willReturn(true);

        $contentTypeService->loadContentTypeGroups()->willReturn([
            $group = new ContentTypeGroup([
                'identifier' => 'group'
            ]),
        ]);

        $contentTypeService->loadContentTypes(Argument::any())->willReturn([
            $type1 = new ContentType([
                'identifier' => 'type1',
                'fieldDefinitions' => [
                    'type1_field1' => ($type1field1 = new FieldDefinition(['identifier' => 'foo'])),
                ]
            ]),
            $type2 = new ContentType([
                'identifier' => 'type2',
                'fieldDefinitions' => [
                    'type2_field1' => ($type2field1 = new FieldDefinition(['identifier' => 'bar'])),
                ]
            ]),
        ]);

        $this->iterate()->shouldYieldLike(
            new \ArrayIterator([
                ['ContentTypeGroup' => $group],
                ['ContentTypeGroup' => $group, 'ContentType' => $type1],
                ['ContentTypeGroup' => $group, 'ContentType' => $type1, 'FieldDefinition' => $type1field1],
                ['ContentTypeGroup' => $group, 'ContentType' => $type2],
                ['ContentTypeGroup' => $group, 'ContentType' => $type2, 'FieldDefinition' => $type2field1],
            ])
        );
    }
}
