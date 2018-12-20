<?php
namespace spec\EzSystems\EzPlatformGraphQL\Tools;

use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use Prophecy\Argument\Token\CallbackToken;

class FieldArgument
{
    public static function hasName($name)
    {
        return self::has('name', $name);
    }

    public static function hasType($type)
    {
        return self::has('type', $type);
    }

    public static function hasDescription($description)
    {
        return self::has('description', $description);
    }

    public static function withResolver($resolverFunction)
    {
        return new CallbackToken(
            function(Input\Field $input) use ($resolverFunction) {
                return strpos($input->resolve, $resolverFunction) !== false;
            }
        );
    }

    private static function has($property, $value) {
        return new CallbackToken(
            function(Input\Field $field) use ($property, $value) {
                return $field->$property === $value;
            }
        );
    }
}
