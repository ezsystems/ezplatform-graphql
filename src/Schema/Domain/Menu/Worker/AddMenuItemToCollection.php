<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */


namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Menu\Worker;


use EzSystems\EzPlatformGraphQL\Menu\Menu;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Worker;

class AddMenuItemToCollection implements Worker
{
    public function work(Builder $schema, array $args)
    {
        $schema->addFieldToType(
            'MenuCollection',
            new Builder\Input\Field($args['Menu']->identifier, 'Menu', [
                "resolve" => sprintf(
                    '@=resolver("MenuByIdentifier", ["%s"])',
                    $args['Menu']->identifier
                ),
            ])
        );
    }

    public function canWork(Builder $schema, array $args)
    {
        return isset($args['Menu']) && $args['Menu'] instanceof Menu;
    }
}
