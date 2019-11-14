<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\Schema\DynamicSchema;

use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use EzSystems\EzPlatformGraphQL\Schema\Builder\SchemaBuilder;

/**
 * A version of the graphql schema builder that doesn't care about non-existent types when adding fields.
 * It will add the fields as is, and will be able to tell that they're modified fields.
 */
class SchemaDiffBuilder extends SchemaBuilder
{
    /** @var array */
    private $modifiedTypes = [];

    /** @var array */
    private $addedTypes = [];

    public function addFieldToType($type, Input\Field $fieldInput)
    {
        if (!$this->hasType($type)) {
            if (!isset($this->modifiedTypes[$type])) {
                $this->modifiedTypes[$type] = true;
            }
            parent::addType(new Input\Type($type, 'object'));
        }

        parent::addFieldToType($type, $fieldInput);
    }

    public function addType(Input\Type $typeInput)
    {
        if (!$this->hasType($typeInput->name)) {
            $this->addedTypes[$typeInput->name] = true;
        }

        parent::addType($typeInput);
    }

    public function getModifiedTypes(): array
    {
        return $this->filterSchema($this->modifiedTypes);
    }

    public function getAddedTypes(): array
    {
        return $this->filterSchema($this->addedTypes);
    }

    private function filterSchema(array $types)
    {
        return array_filter(
            $this->getSchema(),
            function ($type) use ($types) {
                return isset($types[$type]);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
