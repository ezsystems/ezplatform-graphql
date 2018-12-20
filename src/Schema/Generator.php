<?php
namespace EzSystems\EzPlatformGraphQL\Schema;

class Generator
{
    /**
     * @var Builder
     */
    private $schema;

    /**
     * Grouping of schema types for writing to disk (group => [types])
     * @var array
     */
    private $groups;

    /**
     * @var Domain\Iterator[]
     */
    private $iterators;

    /**
     * @var Worker[]
     */
    private $workers;

    public function __construct(Builder $schema, array $iterators, array $workers)
    {
        $this->schema = $schema;
        $this->workers = $workers;
        $this->iterators = $iterators;
    }

    /**
     * @return array
     */
    public function generate()
    {
        foreach ($this->workers as $worker) {
            if ($worker instanceof Initializer) {
                $worker->init($this->schema);
            }
        }

        foreach ($this->iterators as $iterator) {
            $iterator->init($this->schema);
            foreach ($iterator->iterate() as $arguments) {
                foreach ($this->workers as $schemaWorker) {
                    if (!$schemaWorker->canWork($this->schema, $arguments)) {
                        continue;
                    }
                    $schemaWorker->work($this->schema, $arguments);
                }
            }
        }
        
        return $this->schema->getSchema();
    }
}