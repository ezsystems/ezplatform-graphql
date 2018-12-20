<?php
namespace spec\EzSystems\EzPlatformGraphQL\Tools;

use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use Prophecy\Argument\Token\CallbackToken;

class TypeArgument
{
    public static function isNamed($name) {
        return new CallbackToken(
            function (Input\Type $type) use($name) {
                return $type->name === $name;
            }
        );
    }

    public static function inherits($typeName) {
        return new CallbackToken(
            function (Input\type $typeInput) use($typeName) {
                return
                    is_array($typeInput->inherits)
                    ? in_array($typeName, $typeInput->inherits)
                    : $typeInput->inherits === $typeName;
            }
        );
    }

    public static function implements($interfaceName)
    {
        return new CallbackToken(
            function (Input\type $typeInput) use($interfaceName) {
                return
                    is_array($typeInput->interfaces)
                        ? in_array($interfaceName, $typeInput->interfaces)
                        : $typeInput->interfaces === $interfaceName;
            }
        );
    }

    public static function hasType($expectedType)
    {
        return new CallbackToken(
            function (Input\Type $type) use($expectedType) {
                return $type->type === $expectedType;
            }
        );
    }
}