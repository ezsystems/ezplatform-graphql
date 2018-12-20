<?php
namespace EzSystems\EzPlatformGraphQL\Schema;

interface GroupProvider
{
    public function getGroups(array $args);
}