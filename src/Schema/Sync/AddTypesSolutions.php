<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Sync;

use Overblog\GraphQLBundle\Definition\ConfigProcessor;
use Overblog\GraphQLBundle\Definition\GlobalVariables;
use Overblog\GraphQLBundle\Event\Events;
use Overblog\GraphQLBundle\Event\ExecutorArgumentsEvent;
use Overblog\GraphQLBundle\Resolver\TypeResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddTypesSolutions implements EventSubscriberInterface
{
    /**
     * @var \Overblog\GraphQLBundle\Resolver\TypeResolver
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

    /**
     * @var string
     */
    private $definitionsDirectory;

    /**
     * @var \Psr\Log\LoggerInterface|null
     */
    private $logger;

    public function __construct(TypeResolver $typeResolver, ConfigProcessor $configProcessor, GlobalVariables $globalVariables, string $definitionsDirectory, ?LoggerInterface $logger)
    {
        $this->typeResolver = $typeResolver;
        $this->configProcessor = $configProcessor;
        $this->globalVariables = $globalVariables;
        $this->definitionsDirectory = $definitionsDirectory;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [Events::PRE_EXECUTOR => 'registerTypes'];
    }

    public function registerTypes(ExecutorArgumentsEvent $event)
    {
        $classMapFile = "$this->definitionsDirectory/__classes.map";
        $map = include($classMapFile);
        ksort($map);
        foreach ($map as $class => $file) {
            $typeName = str_replace('Overblog\\GraphQLBundle\\__DEFINITIONS__\\', '', $class);
            $typeName = substr($typeName, 0, -4);
            if ($this->typeResolver->hasSolution($class)) {
                continue;
            }
            $this->typeResolver->addSolution(
                $class,
                [
                    [$this, 'build'],
                    [$class]
                ],
                [$typeName],
                [
                    'id' => $class,
                    'aliases' => [$typeName],
                    'alias' => $typeName,
                    'generated' => true
                ]
            );
        }
    }

    public function build($id)
    {
        return new $id($this->configProcessor, $this->globalVariables);
    }
}
