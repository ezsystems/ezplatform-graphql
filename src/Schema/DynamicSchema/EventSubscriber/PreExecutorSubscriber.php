<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */


namespace EzSystems\EzPlatformGraphQL\Schema\DynamicSchema\EventSubscriber;


use Overblog\GraphQLBundle\__DEFINITIONS__\HotContentType;
use Overblog\GraphQLBundle\Definition\ConfigProcessor;
use Overblog\GraphQLBundle\Definition\GlobalVariables;
use Overblog\GraphQLBundle\Event\Events;
use Overblog\GraphQLBundle\Event\ExecutorArgumentsEvent;
use Overblog\GraphQLBundle\Resolver\FluentResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PreExecutorSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Overblog\GraphQLBundle\Resolver\FluentResolverInterface
     */
    private $typeResolver;
    /**
     * @var \Overblog\GraphQLBundle\Definition\ConfigProcessor
     */
    private $configProcessor;
    /**
     * @var \Overblog\GraphQLBundle\Definition\GlobalVariables
     */
    private $globalVariables;

    public function __construct(FluentResolverInterface $typeResolver, ConfigProcessor $configProcessor, GlobalVariables $globalVariables)
    {
        $this->typeResolver = $typeResolver;
        $this->configProcessor = $configProcessor;
        $this->globalVariables = $globalVariables;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [Events::PRE_EXECUTOR => 'updateSchemaFromRepository'];
    }

    public function updateSchemaFromRepository(ExecutorArgumentsEvent $event)
    {
        $this->typeResolver->addSolution(
            'Overblog\\GraphQLBundle\\__DEFINITIONS__\\HotContentType',
            [
                [$this, 'build'],
                ['Overblog\\GraphQLBundle\\__DEFINITIONS__\\HotContentType']
            ],
            ['HotContent'],
            [
                'id' => 'Overblog\\GraphQLBundle\\__DEFINITIONS__\\HotContentType',
                'aliases' => ['HotContent'],
                'alias' => 'HotContent',
                'generated' => true
            ]
        );

        /**
         * Could the type class have been written earlier (when the type got modified) ?
         */
    }

    public function build($id)
    {
        return new HotContentType($this->configProcessor, $this->globalVariables);
    }
}
