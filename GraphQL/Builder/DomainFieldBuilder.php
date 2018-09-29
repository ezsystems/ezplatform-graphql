<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\EzPlatformGraphQLBundle\GraphQL\Builder;

use Overblog\GraphQLBundle\Definition\Builder\MappingInterface;

class DomainFieldBuilder implements MappingInterface
{
    const DOMAIN_SCHEMA_FILE = __DIR__. '/../../../../../src/AppBundle/Resources/config/graphql/Domain.types.yml';

    public function toMappingDefinition(array $config)
    {
        $return = ['description' => 'Repository domain objects'];

        if (file_exists(self::DOMAIN_SCHEMA_FILE)) {
            $return['type'] = 'Domain';
            $return['resolve'] = '[]';
        } else {
            $return['type'] = 'String';
            $return['resolve'] = 'This resource is only available once the domain types have been generated.';
        }

        return $return;
    }
}
