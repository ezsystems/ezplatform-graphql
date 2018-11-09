<?php
namespace BD\EzPlatformGraphQLBundle\Schema;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;

/**
 * Generates the ImageVariationIdentifier enum that indexes images variations identifiers.
 */
class ImagesVariationsBuilder implements SchemaBuilder
{
    /**
     * @var ConfigResolver
     */
    private $configResolver;

    public function __construct(ConfigResolver $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function build(array &$schema)
    {
        $schema['ImageVariationIdentifier'] = [
            'type' => 'enum',
            'config' => [
                'values' => []
            ]
        ];

        $values =& $schema['ImageVariationIdentifier']['config']['values'];

        foreach (array_keys($this->configResolver->getParameter('image_variations')) as $variationIdentifier) {
            $values[$variationIdentifier] = [];
        }
    }
}