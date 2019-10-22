<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use DOMDocument;
use eZ\Publish\Core\FieldType\XmlText\Converter\Html5 as Html5Converter;

/**
 * @internal
 */
class XmlTextResolver
{
    /**
     * @var Html5Converter
     */
    private $xmlTextConverter;

    public function __construct(Html5Converter $xmlTextConverter)
    {
        $this->xmlTextConverter = $xmlTextConverter;
    }

    public function xmlTextToHtml5(DOMDocument $document)
    {
        return $this->xmlTextConverter->convert($document);
    }
}
