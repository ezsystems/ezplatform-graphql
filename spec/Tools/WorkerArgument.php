<?php
namespace spec\EzSystems\EzPlatformGraphQL\Tools;

use PhpSpec\Wrapper\ObjectWrapper;
use Prophecy\Argument;

class WorkerArgument extends Argument
{
    public static function hasContentTypeGroup($contentTypeGroup = null)
    {
        return self::hasArgument('ContentTypeGroup', $contentTypeGroup);
    }

    public static function hasContentType($contentType = null)
    {
        return self::hasArgument('ContentType', $contentType);
    }

    public static function hasFieldDefinition($fieldDefinition = null)
    {
        return self::hasArgument('FieldDefinition', $fieldDefinition);
    }

    public static function hasArgument($argumentName, $value = null)
    {
        return new Argument\Token\CallbackToken(
            function ($args) use ($argumentName, $value) {
                if ($value instanceof ObjectWrapper) {
                    $value = $value->getWrappedObject();
                }
                return isset($args[$argumentName]) && ($value === null || $args[$argumentName] == $value);
            }
        );
    }
}