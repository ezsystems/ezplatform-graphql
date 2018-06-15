<?php
namespace BD\EzPlatformGraphQLBundle\GraphQL\Resolver;

use DateTime;
use Overblog\GraphQLBundle\Definition\Argument;

class DateResolver
{
    public function resolveDateToFormat(DateTime $date, Argument $args)
    {
        if (isset($args['constant'])) {
            $format = $args['constant'];
        } else if (isset($args['format'])) {
            $format = $args['format'];
        } else {
            $format = DateTime::RFC850;
        }

        return $date->format($format);
    }
}
