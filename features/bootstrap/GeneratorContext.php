<?php

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
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

class GeneratorContext implements \Behat\Symfony2Extension\Context\KernelAwareContext
{
    /**
     * @var string
     */
    private $scriptOutput;

    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * @When /^I run the command "([^"]+)"$/
     */
    public function iRunTheCommand($command)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => $command, '--env' => 'behat']);

        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = $output->fetch();
    }

    /**
     * @Then /^the schema files are generated in "([^"]*)"$/
     */
    public function theSchemaFilesAreGeneratedIn($directory)
    {
        $finder = new Finder();
        Assert::assertFileExists('app/config/graphql/ezplatform/Domain.types.yml');
        Assert::assertFileExists('app/config/graphql/ezplatform/DomainContentMutation.types.yml');
    }

    /**
     * @Given /^the schema has not been generated$/
     */
    public function theSchemaHasNotBeenGenerated()
    {
        if (file_exists('app/config/graphql/ezplatform')) {
            $finder = new Finder();
            $fs = new Filesystem();
            $fs->remove($finder->in('app/config/graphql/ezplatform')->files());
        }
    }

    /**
     * Sets Kernel instance.
     *
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
}