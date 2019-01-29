<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Builder\Input;

class Field extends Input
{
    public function __construct($name, $type, array $properties = [])
    {
        parent::__construct($properties);
        $this->name = $name;
        $this->type = $type;
    }

    public $name;
    public $description;
    public $type;
    public $resolve;
    public $argsBuilder;
}