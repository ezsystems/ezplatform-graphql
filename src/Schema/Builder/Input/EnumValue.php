<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Builder\Input;

class EnumValue extends Input
{
    public function __construct($name, array $properties = [])
    {
        parent::__construct($properties);
        $this->name = $name;
    }

    public $name;
    public $value;
    public $description;
}