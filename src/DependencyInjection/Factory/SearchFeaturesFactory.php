<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Factory;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;

class SearchFeaturesFactory
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider
     */
    private $configurationProvider;

    /**
     * @var \EzSystems\EzPlatformGraphQL\Search\SearchFeatures[]
     */
    private $searchFeatures = [];

    public function __construct(RepositoryConfigurationProvider $configurationProvider, array $searchFeatures)
    {
        $this->configurationProvider = $configurationProvider;
        $this->searchFeatures = $searchFeatures;
    }

    public function build()
    {
        $searchEngine = $this->configurationProvider->getRepositoryConfig()['search']['engine'];

        if (isset($this->searchFeatures[$searchEngine])) {
            return $this->searchFeatures[$searchEngine];
        } else {
            throw new \InvalidArgumentException('Search engine not found');
        }
    }
}
