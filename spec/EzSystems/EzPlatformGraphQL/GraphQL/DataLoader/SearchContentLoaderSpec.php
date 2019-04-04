<?php

namespace spec\EzSystems\EzPlatformGraphQL\GraphQL\DataLoader;

use eZ\Publish\Core\Repository\ContentService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\SearchContentLoader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SearchContentLoaderSpec extends ObjectBehavior
{
    const CONTENT_ID = 1;
    const CONTENT_ID_ARRAY = [1, 2, 3];

    function let(SearchService $searchService, ContentService $contentService)
    {
        $this->beConstructedWith($searchService, $contentService);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SearchContentLoader::class);
    }

    function it_finds_with_the_Content_Service_given_a_content_id(SearchService $searchService, ContentService $contentService)
    {
        $content = $this->createOneContentItem();

        $searchService->findContent(Argument::any())->shouldNotBeCalled();
        $contentService->loadContent(self::CONTENT_ID)->willReturn($content);

        $this->find($this->contentIdQuery(self::CONTENT_ID))->shouldReturn([$content]);
    }

    function it_finds_with_the_Content_Service_multi_load_feature_if_available_given_a_ContentId_Criterion_with_several_id(SearchService $searchService, ContentService $contentService)
    {
        $contentInfo1 = new ContentInfo(['id' => self::CONTENT_ID_ARRAY[0]]);
        $content1 = new Content(['versionInfo'=> new VersionInfo(['contentInfo' => $contentInfo1])]);
        $contentInfo2 = new ContentInfo(['id' => self::CONTENT_ID_ARRAY[1]]);
        $content2 = new Content(['versionInfo'=> new VersionInfo(['contentInfo' => $contentInfo2])]);
        $contentInfo3 = new ContentInfo(['id' => self::CONTENT_ID_ARRAY[2]]);
        $content3 = new Content(['versionInfo'=> new VersionInfo(['contentInfo' => $contentInfo3])]);
        $contentInfoArray = [$contentInfo1, $contentInfo2, $contentInfo3];
        $contentArray = [$content1, $content2, $content3];

        $searchService->findContent(Argument::any())->shouldNotBeCalled();
        $contentService->loadContent(Argument::any())->shouldNotBeCalled();
        $contentService->loadContentInfoList(self::CONTENT_ID_ARRAY)->willReturn($contentInfoArray);
        $contentService->loadContentListByContentInfo($contentInfoArray)->willReturn($contentArray);

        $this->find($this->contentIdQuery(self::CONTENT_ID))->shouldReturn($contentArray);
    }

    function it_finds_content_items_given_a_criterion()
    {

    }

    function it_finds_a_single_content_item_using_the_Content_Service_given_a_ContentId_criterion_with_one_id()
    {

    }

    function it_finds_a_single_content_item_using_the_Content_Service_given_a_ContentId_criterion_with_array_array_with_one_id()
    {

    }

    function it_finds_a_single_content_item_using_the_Content_Service_given_a_ContentId_Criterion_with_several_id()
    {

    }

    function it_finds_a_single_content_item_given_a_filter_using_the_Search_Service()
    {

    }

    private function contentIdQuery($contentId)
    {
        return new Query(['filter' => new Query\Criterion\ContentId($contentId)]);
    }

    /**
     * @return Content
     */
    private function createOneContentItem(): Content
    {
        return new Content(['versionInfo' => ['contentInfo' => ['id' => self::CONTENT_ID]]]);
    }
}
