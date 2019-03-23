<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Mutation\InputHandler\FieldType;

use eZ\Publish\Core\FieldType\Media as MediaFieldType;
use eZ\Publish\SPI\FieldType\Value;
use EzSystems\EzPlatformGraphQL\GraphQL\Mutation\InputHandler\FieldTypeInputHandler;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Media implements FieldTypeInputHandler
{
    public function toFieldValue($input, $inputFormat = null): Value
    {
        if (!$input['file'] instanceof UploadedFile) {
            return null;
        }

        $file = $input['file'];

        $value = new MediaFieldType\Value([
            'fileName' => $input['fileName'] ?? $file->getClientOriginalName(),
            'inputUri' => $file->getPathname(),
            'fileSize' => $file->getSize(),
        ]);

        foreach (['hasController', 'loop', 'autoplay', 'width', 'height'] as $property) {
            if (isset($input[$property])) {
                $value->$property = $input[$property];
            }
        }

        return $value;
    }
}
