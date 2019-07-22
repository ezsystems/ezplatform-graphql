<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Mutation\InputHandler\FieldType\RichText;

use DOMDocument;
use EzSystems\EzPlatformRichText\eZ\RichText\Converter as RichTextFieldTypeConverter;

class HtmlRichTextConverter implements RichTextInputConverter
{
    /**
     * @var \EzSystems\EzPlatformRichText\eZ\RichText\Converter
     */
    private $xhtml5Converter;

    public function __construct(RichTextFieldTypeConverter $xhtml5Converter)
    {
        $this->xhtml5Converter = $xhtml5Converter;
    }

    public function convertToXml($text): DOMDocument
    {
        $text = <<<HTML5EDIT
<section xmlns="http://ez.no/namespaces/ezpublish5/xhtml5/edit">$text</section>
HTML5EDIT;

        $dom = new DOMDocument();
        $dom->loadXML($text);

        return $this->xhtml5Converter->convert($dom);
    }
}
