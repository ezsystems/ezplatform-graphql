<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Mutation\InputHandler\FieldType;

use eZ\Publish\Core\FieldType\BinaryFile as BInaryFileFieldType;
use eZ\Publish\SPI\FieldType\Value;
use EzSystems\EzPlatformGraphQL\GraphQL\Mutation\InputHandler\FieldTypeInputHandler;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BinaryFile implements FieldTypeInputHandler
{
    public function toFieldValue($input, $inputFormat = null): Value
    {
        if (!$input['file'] instanceof UploadedFile) {
            return null;
        }

        $file = $input['file'];

        return new BinaryFileFieldType\Value([
            'fileName' => $file->getClientOriginalName(),
            'inputUri' => $file->getPathname(),
            'fileSize' => $file->getSize(),
        ]);
    }
}
