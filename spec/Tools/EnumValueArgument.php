<?php
namespace spec\EzSystems\EzPlatformGraphQL\Tools;

use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use Prophecy\Argument\Token\CallbackToken;

class EnumValueArgument
{
    public static function withName($name) {
        return new CallbackToken(
            function (Input\EnumValue $input) use($name) {
                return $input->name === $name;
            }
        );
    }

    public static function withDescription($description) {
        return new CallbackToken(
            function (Input\EnumValue $input) use($description) {
                return $input->description === $description;
            }
        );
    }

    public static function withValue($value) {
        return new CallbackToken(
            function (Input\EnumValue $input) use($value) {
                return $input->value === $value;
            }
        );
    }
}