<?php
namespace spec\EzSystems\EzPlatformGraphQL\Tools;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use Prophecy\Argument\Token\CallbackToken;

class ContentTypeArgument
{
    /**
     * @return ContentType|CallbackToken
     */
    public static function withIdentifier($identifier)
    {
        return new CallbackToken(
            function ($argument) use ($identifier) {
                return
                    $argument instanceof ContentType
                    && $argument->identifier === $identifier;
            }
        );
    }
}