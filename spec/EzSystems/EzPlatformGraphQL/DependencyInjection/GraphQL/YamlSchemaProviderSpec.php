<?php

namespace spec\EzSystems\EzPlatformGraphQL\DependencyInjection\GraphQL;

use EzSystems\EzPlatformGraphQL\DependencyInjection\GraphQL\YamlSchemaProvider;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PhpSpec\ObjectBehavior;

class YamlSchemaProviderSpec extends ObjectBehavior
{
    /**
     * @vfsStreamDirectory
     */
    private $vfs;

    function let()
    {
        $this->vfs = vfsStream::setUp('config');
        $this->beConstructedWith($this->vfs->url());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(YamlSchemaProvider::class);
    }

    function it_returns_the_app_schema_if_one_exists()
    {
        $this->createFile('Query.types.yml');
        $this->getQuerySchema()->shouldHaveSchemaConfiguration('Query');
    }

    function it_returns_the_Domain_schema_if_no_app_schema_exists_and_the_domain_schema_exists()
    {
        $this->createFile('ezplatform/Domain.types.yml');
        $this->getQuerySchema()->shouldHaveSchemaConfiguration('Domain');
    }

    function it_returns_the_Platform_schema_if_no_app_and_domain_schema_exist()
    {
        $this->createFile('ezplatform/Platform.types.yml');
        $this->getQuerySchema()->shouldHaveSchemaConfiguration('Platform');
    }

    public function getMatchers(): array
    {
        return [
            'haveSchemaConfiguration' => function($value, $expectedSchemaConfiguration) {
                return $value === $expectedSchemaConfiguration;
            }
        ];
    }

    private function createFile($file)
    {
        $this->vfs->addChild(vfsStream::newFile($file));
    }
}