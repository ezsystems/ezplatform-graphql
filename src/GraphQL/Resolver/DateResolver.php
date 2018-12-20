<?php
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use DateTime;
use Overblog\GraphQLBundle\Definition\Argument;

class DateResolver
{
    public function resolveDateToFormat($date, $args)
    {
        if (!$date instanceof DateTime) {
            return null;
        }

        if (isset($args['pattern'])) {
            return $date->format($args['pattern']);
        }

        if (isset($args['constant'])) {
            return $date->format($args['constant']);
        }
    }
}
