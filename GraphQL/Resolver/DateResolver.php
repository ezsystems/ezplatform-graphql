<?php
namespace BD\EzPlatformGraphQLBundle\GraphQL\Resolver;

use DateTime;
use Overblog\GraphQLBundle\Definition\Argument;

class DateResolver
{
    public function resolveDateToFormat(string $format, DateTime $date = null)
    {
        if ($date === null) {
            return $date;
        }
        return $date->format($format);
    }
}
