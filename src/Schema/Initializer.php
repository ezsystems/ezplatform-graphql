<?php
namespace EzSystems\EzPlatformGraphQL\Schema;

interface Initializer
{
    public function init(Builder $schema);
}