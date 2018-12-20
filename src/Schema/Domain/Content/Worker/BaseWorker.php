<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;

class BaseWorker
{
    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper
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