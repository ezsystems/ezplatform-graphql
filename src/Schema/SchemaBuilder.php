<?php
namespace EzSystems\EzPlatformGraphQL\Schema;

interface SchemaBuilder
{
    public function build(array &$schema);
}