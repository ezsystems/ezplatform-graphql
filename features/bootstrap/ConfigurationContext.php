<?php

use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

class ConfigurationContext implements \Behat\Symfony2Extension\Context\KernelAwareContext
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * Sets Kernel instance.
     *
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given /^the GraphQL extension is configured to use that schema$/
     */
    public function theGraphQLExtensionIsConfiguredToUseThatSchema()
    {
        $container = $this->kernel->getContainer();
        $executor = $container->get('overblog_graphql.request_executor');
        $schema = $executor->getSchema('default');
        Assert::assertEquals('Domain', (string)$schema->getQueryType());
        Assert::assertEquals('DomainContentMutation', (string)$schema->getMutationType());
    }

}