<?php
namespace EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker;

use EzSystems\EzPlatformGraphQL\DomainContent\NameHelper;

class BaseWorker
{
    /**
     * @var \EzSystems\EzPlatformGraphQL\DomainContent\NameHelper
     */
    private $nameHelper;

    public function setNameHelper(NameHelper $nameHelper)
    {
        $this->nameHelper = $nameHelper;
    }

    protected function getNameHelper()
    {
        return $this->nameHelper;
    }
}