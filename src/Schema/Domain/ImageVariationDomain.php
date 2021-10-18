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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Adds configured image variations to the ImageVariationIdentifier type.
 */
class ImageVariationDomain implements Domain\Iterator, Schema\Worker, LoggerAwareInterface
{
    use LoggerAwareTrait;

    const TYPE = 'ImageVariationIdentifier';
    const ARG = 'ImageVariation';

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \EzSystems\EzPlatformGraphQL\Schema\Domain\NameValidator */
    private $nameValidator;

    public function __construct(
        ConfigResolverInterface $configResolver,
        NameValidator $nameValidator
    ) {
        $this->configResolver = $configResolver;
        $this->nameValidator = $nameValidator;
        $this->logger = new NullLogger();
    }

    public function iterate(): Generator
    {
        foreach ($this->configResolver->getParameter('image_variations') as $identifier => $variation) {
            if (!$this->nameValidator->isValidName($identifier)) {
                $this->logger->warning("Skipped schema generation for Image Variation with identifier '$identifier'. Please rename given image variation according to GraphQL specification (http://spec.graphql.org/June2018/#sec-Names)");
                continue;
            }

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
