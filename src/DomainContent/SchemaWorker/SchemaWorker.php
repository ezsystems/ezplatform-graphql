<?php
namespace EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker;

interface SchemaWorker
{
    /**
     * Does the work on $schema.
     *
     * @param array a reference to the schema
     * @param array args
     *
     * @return void
     */
    public function work(array &$schema, array $args);

    /**
     * Tests the arguments and schema, and says if the worker can work on that state.
     * It includes testing if the worker was already executed.
     *
     * @param array a reference to the schema
     * @param array args
     *
     * @return bool
     */
    public function canWork(array $schema, array $args);
}