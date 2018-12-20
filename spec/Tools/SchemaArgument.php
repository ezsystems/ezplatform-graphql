<?php
namespace spec\EzSystems\EzPlatformGraphQL\Tools;

use PhpSpec\Wrapper\ObjectWrapper;
use Prophecy\Argument;

class SchemaArgument extends Argument
{
    public static function isSchema()
    {
        return new Argument\Token\CallbackToken(
            function ($schema) {
                return is_array($schema);
            }
        );
    }
}