<?php
namespace BD\EzPlatformGraphQLBundle\Schema;

interface SchemaBuilder
{
    public function build(array &$schema);
}