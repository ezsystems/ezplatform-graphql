<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\FieldType;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\ItemFactory;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use GraphQL\Error\UserError;

final class RelationFieldResolver
{
    /** @var ContentLoader */
    private $contentLoader;

    /** @var \EzSystems\EzPlatformGraphQL\GraphQL\ItemFactory */
    private $itemFactory;

    public function __construct(ContentLoader $contentLoader, ItemFactory $itemFactory)
    {
        $this->contentLoader = $contentLoader;
        $this->itemFactory = $itemFactory;
    }

    public function resolveRelationFieldValue(Field $field, $multiple = false)
    {
        $destinationContentIds = $this->getContentIds($field);

        if (empty($destinationContentIds) || array_key_exists(0, $destinationContentIds) && null === $destinationContentIds[0]) {
            return $multiple ? [] : null;
        }

        // @todo do we want to restrict results to the current siteaccess (tree root) ?
        //       What if the user has access to locations from other siteaccesses ?
        $contentItems = $this->contentLoader->find(new Query(
            ['filter' => new Query\Criterion\ContentId($destinationContentIds)]
        ));

        if ($multiple) {
            return array_map(
                function ($contentId) use ($contentItems) {
                    return $this->itemFactory->fromContent(
                        $contentItems[array_search($contentId, array_column($contentItems, 'id'))]
                    );
                },
                $destinationContentIds
            );
        }

        return $contentItems[0] ? $this->itemFactory->fromContent($contentItems[0]) : null;
    }

    /**
     * @return array
     *
     * @throws UserError if the field isn't a Relation or RelationList value
     */
    private function getContentIds(Field $field): array
    {
        if ($field->value instanceof FieldType\RelationList\Value) {
            return $field->value->destinationContentIds;
        } elseif ($field->value instanceof FieldType\Relation\Value) {
            return [$field->value->destinationContentId];
        } else {
            throw new UserError('\$field does not contain a RelationList or Relation Field value');
        }
    }
}
