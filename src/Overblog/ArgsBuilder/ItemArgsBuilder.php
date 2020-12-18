<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Overblog\ArgsBuilder;

use Overblog\GraphQLBundle\Definition\Builder\MappingInterface;

class ItemArgsBuilder implements MappingInterface
{
    /**
     * {@inheritdoc}
     */
    public function toMappingDefinition(array $config): array
    {
        return [
            'contentId' => ['type' => 'Int', 'description' => 'Content ID of the article'],
            'remoteId' => ['type' => 'String', 'description' => 'Content remote ID of the article'],
            'locationId' => ['type' => 'Int', 'description' => 'Location ID of the article'],
            'locationRemoteId' => ['type' => 'String', 'description' => 'Location remote ID of the article'],
            'urlAlias' => ['type' => 'String', 'description' => 'URL alias of the article'],
        ];
    }
}
