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

class CacheContext implements \Behat\Symfony2Extension\Context\KernelAwareContext
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
     * @Given /^I clear the cache$/
     */
    public function iClearTheCache()
    {
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => 'cache:clear', '--env' => 'behat']);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        Assert::assertEquals(0, $application->run($input, $output));
        $content = $output->fetch();
        $this->kernel->shutdown();
        $this->kernel->boot();
    }
}