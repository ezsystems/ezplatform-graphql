<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Relay;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Output\ConnectionBuilder;
use Overblog\GraphQLBundle\Relay\Connection\Output\Edge;
use Overblog\GraphQLBundle\Relay\Connection\Output\PageInfo;

final class PageAwareConnection
{
    /** @var Edge[] */
    public $edges = [];

    /** @var PageInfo */
    public $pageInfo;

    /** @var int */
    public $totalCount;

    /** @var Page[] */
    public $pages;

    public function __construct(array $edges, PageInfo $pageInfo)
    {
        $this->edges = $edges;
        $this->pageInfo = $pageInfo;
    }

    public static function fromConnection(Connection $connection, Argument $args): PageAwareConnection
    {
        $return = new self($connection->edges, $connection->pageInfo);
        $return->totalCount = $connection->totalCount;

        $return->pages = [];

        $perPage = $args['first'] ?? $args['last'] ?? 10;
        $totalPages = ceil($return->totalCount / $perPage);
        for ($pageNumber = 2; $pageNumber <= $totalPages; ++$pageNumber) {
            $offset = ($pageNumber - 1) * $perPage - 1;
            $return->pages[] = new Page($pageNumber, ConnectionBuilder::offsetToCursor($offset));
        }

        return $return;
    }
}
