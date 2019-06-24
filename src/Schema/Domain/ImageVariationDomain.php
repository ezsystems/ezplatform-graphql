<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzPlatformGraphQL\Schema;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Domain;
use Generator;

/**
 * Adds configured image variations to the ImageVariationIdentifier type.
 */
class ImageVariationDomain implements Domain\Iterator, Schema\Worker
{
    const TYPE = 'ImageVariationIdentifier';
    const ARG = 'ImageVariation';

    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function iterate(): Generator
    {
        foreach ($this->configResolver->getParameter('image_variations') as $identifier => $variation) {
            yield [self::ARG => ['identifier' => $identifier, 'variation' => $variation]];
        }
    }

    public function init(Builder $schema)
    {
        $schema->addType(new Builder\Input\Type(self::TYPE, 'enum'));
    }

    public function work(Builder $schema, array $args)
    {
        $schema->addValueToEnum(self::TYPE,
            new Builder\Input\EnumValue($args[self::ARG]['identifier'])
        );
    }

    public function canWork(Builder $schema, array $args)
    {
        return isset($args[self::ARG]['identifier']);
    }
}
