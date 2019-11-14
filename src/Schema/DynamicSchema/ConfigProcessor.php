<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */


namespace EzSystems\EzPlatformGraphQL\Schema\DynamicSchema;

use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\ConfigProcessor\ConfigProcessorInterface;
use Overblog\GraphQLBundle\Definition\GlobalVariables;
use Overblog\GraphQLBundle\Definition\LazyConfig;
use Overblog\GraphQLBundle\Resolver\FluentResolverInterface;

final class ConfigProcessor implements ConfigProcessorInterface
{
    /** @var \Overblog\GraphQLBundle\Resolver\FluentResolverInterface */
    private $typeResolver;

    /** @var \Overblog\GraphQLBundle\Definition\GlobalVariables */
    private $globalVariables;

    public function __construct(FluentResolverInterface $typeResolver, GlobalVariables $globalVariables)
    {
        $this->typeResolver = $typeResolver;
        $this->globalVariables = $globalVariables;
    }

    private function addField(array $fields)
    {
        $resolverArguments = new Argument(['id' => 56]);
        $globalVariables = $this->globalVariables;
        $fields['_hot'] = [
            'type' => $this->typeResolver->getSolution('HotContent'),
            'args' => [],
            'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariables, $resolverArguments) {
                return $globalVariables->get('resolverResolver')->resolve(["DomainContentItem", [0 => $resolverArguments, 1 => "hot"]]);
            },
            'description' => '',
            'deprecationReason' => null
        ];

        return $fields;
    }

    /**
     * @inheritDoc
     */
    public function process(LazyConfig $lazyConfig)
    {
        $lazyConfig->addPostLoader(function ($config) {
            if ($config['name'] === 'DomainGroupContent') {
                if (isset($config['fields']) && \is_callable($config['fields'])) {
                    $config['fields'] = function () use ($config) {
                        $fields = $config['fields']();

                        return static::addField($fields);
                    };
                }
            }
            return $config;
        });

        return $lazyConfig;
    }
}
